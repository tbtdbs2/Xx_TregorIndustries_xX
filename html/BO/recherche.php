<?php
// 1. SÉCURISATION ET INITIALISATION
$pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
require_once __DIR__ . '/../../includes/db.php';

// 2. LOGIQUE PHP
$all_categories = [];
try {
    $category_stmt = $pdo->query('SELECT id, type FROM categories ORDER BY type');
    $all_categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de BDD (catégories): " . $e->getMessage());
}

$searchTerm = $_GET['q'] ?? '';
$category_id = $_GET['category'] ?? '';
$destination = $_GET['destination'] ?? '';
$priceMin = isset($_GET['price_min_input']) && $_GET['price_min_input'] !== '' ? (float)$_GET['price_min_input'] : null;
$priceMax = isset($_GET['price_max_input']) && $_GET['price_max_input'] !== '' ? (float)$_GET['price_max_input'] : null;
$sort = $_GET['sort'] ?? 'note';

// NOUVEAUX FILTRES
$selectedDate = $_GET['date'] ?? null; // Pour le filtre date
$minRating = isset($_GET['min_rating']) && $_GET['min_rating'] !== '' ? (float)$_GET['min_rating'] : null; // Pour le filtre de note
$statusFilter = $_GET['status'] ?? null; // Pour le filtre de statut

$sql = 'SELECT offres.*, categories.type as category_type,
        (SELECT s.status FROM statuts s WHERE s.offre_id = offres.id ORDER BY s.changed_at DESC LIMIT 1) as current_status,
        COALESCE(offres.rating, 0) as offer_rating'; // Ajout de offer_rating et COALESCE pour gérer les NULL

$sql .= ' FROM offres ';
$sql .= ' JOIN adresses ON offres.adresse_id = adresses.id ';
$sql .= ' JOIN categories ON offres.categorie_id = categories.id ';

// Jointure LEFT JOIN avec statuts pour récupérer le statut le plus récent de l'offre
$sql .= ' LEFT JOIN (
            SELECT offre_id, status
            FROM statuts
            WHERE (offre_id, changed_at) IN (
                SELECT offre_id, MAX(changed_at)
                FROM statuts
                GROUP BY offre_id
            )
        ) s ON offres.id = s.offre_id';

$conditions = [];
$params = [];

$conditions[] = 'offres.pro_id = :pro_id';
$params[':pro_id'] = $pro_id;

if (!empty($searchTerm)) {
    $conditions[] = '(offres.title LIKE :keyword OR offres.summary LIKE :keyword)';
    $params[':keyword'] = '%' . $searchTerm . '%';
}

if (!empty($category_id)) {
    $conditions[] = 'offres.categorie_id = :category_id';
    $params[':category_id'] = $category_id;
}

if (!empty($destination)) {
    $conditions[] = 'adresses.city LIKE :destination';
    $params[':destination'] = '%' . $destination . '%';
}

if ($priceMin !== null) {
    $conditions[] = 'offres.price >= :price_min';
    $params[':price_min'] = $priceMin;
}
if ($priceMax !== null) {
    $conditions[] = 'offres.price <= :price_max';
    $params[':price_max'] = $priceMax;
}

// FILTRE DE NOTE
if ($minRating !== null) {
    $conditions[] = 'COALESCE(offres.rating, 0) >= :min_rating'; // Utilise COALESCE pour les ratings NULL
    $params[':min_rating'] = $minRating;
}

// FILTRE DE STATUT
if ($statusFilter !== null) {
    if ($statusFilter === 'open') {
        $conditions[] = 'COALESCE(s.status, 0) = 1'; // Statut 1 pour "ouvert"
    } elseif ($statusFilter === 'closed') {
        $conditions[] = 'COALESCE(s.status, 0) = 0'; // Statut 0 pour "fermé" (ou non trouvé dans la table statuts)
    }
}

// FILTRE DE DATE
if ($selectedDate) {
    $dateConditions = [];
    $dateParams = [];

    // Tente de créer un objet DateTime pour extraire le jour de la semaine
    try {
        $selectedDateTime = new DateTime($selectedDate);
        $selectedDayOfWeek = strtolower($selectedDateTime->format('l')); // 'monday', 'tuesday', etc.
    } catch (Exception $e) {
        error_log("Erreur de parsing de date: " . $e->getMessage());
        $selectedDate = null; // Invalide la date si erreur
    }
    
    if ($selectedDate) {
        // Horaires Activités (activites -> horaires_activites)
        $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_activites ha 
                                    WHERE ha.activite_id = offres.categorie_id 
                                    AND offres.category_type = \'activite\' 
                                    AND ha.date = :selected_date_ha)';
        $dateParams[':selected_date_ha'] = $selectedDate;

        // Spectacles (spectacles)
        $dateConditions[] = 'EXISTS (SELECT 1 FROM spectacles s_cat 
                                    WHERE s_cat.categorie_id = offres.categorie_id 
                                    AND offres.category_type = \'spectacle\' 
                                    AND s_cat.date = :selected_date_s)';
        $dateParams[':selected_date_s'] = $selectedDate;
        
        // Visites (visites)
        $dateConditions[] = 'EXISTS (SELECT 1 FROM visites v_cat 
                                    WHERE v_cat.categorie_id = offres.categorie_id 
                                    AND offres.category_type = \'visite\' 
                                    AND v_cat.date = :selected_date_v)';
        $dateParams[':selected_date_v'] = $selectedDate;

        // Parcs Attractions (parcs_attractions -> attractions -> horaires_attractions)
        $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_attractions hat
                                    JOIN attractions attr ON hat.attraction_id = attr.id
                                    WHERE attr.parc_attractions_id = offres.categorie_id
                                    AND offres.category_type = \'parc_attraction\'
                                    AND hat.day = :selected_day_pa)';
        $dateParams[':selected_day_pa'] = $selectedDayOfWeek;

        // Restaurations (restaurations -> horaires_restaurants)
        $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_restaurants hres
                                    WHERE hres.restauration_id = offres.categorie_id
                                    AND offres.category_type = \'restauration\'
                                    AND hres.day = :selected_day_res)';
        $dateParams[':selected_day_res'] = $selectedDayOfWeek;
        
        // Si des conditions de date sont définies, nous devons les encapsuler correctement.
        // Il faut que l'offre corresponde à AU MOINS UNE des conditions de date.
        if (!empty($dateConditions)) {
            $conditions[] = '(' . implode(' OR ', $dateConditions) . ')';
            $params = array_merge($params, $dateParams); // Fusionne les paramètres
        }
    }
}


if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

switch ($sort) {
    case 'price_asc':
        $sql .= ' ORDER BY offres.price ASC';
        break;
    case 'price_desc':
        $sql .= ' ORDER BY offres.price DESC';
        break;
    case 'note':
    default:
        $sql .= ' ORDER BY offer_rating DESC, offres.created_at DESC'; // Tri par note (la plus haute en premier), puis par date de création
        break;
}

$offers = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de BDD (recherche offres): " . $e->getMessage());
}

function getSortLink($sortValue, $currentSort, $filters) {
    $filters['sort'] = $sortValue;
    $queryString = http_build_query($filters);
    $activeClass = ($sortValue == $currentSort) ? 'active' : '';
    $labels = ['note' => 'Plus récents', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant'];
    return '<a href="?' . $queryString . '" class="sort-button ' . $activeClass . '">' . $labels[$sortValue] . '</a>';
}

function renderStars($rating) {
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $output .= '<i class="fas fa-star"></i>';
        } else {
            $output .= '<i class="far fa-star"></i>';
        }
    }
    return $output;
}

$current_filters = $_GET;
unset($current_filters['sort']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Offres - PACT Pro</title>
    <link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .search-page-container { display: flex; gap: 25px; padding-top: 20px; padding-bottom: 20px; }
        .filters-sidebar { width: 280px; flex-shrink: 0; background-color: var(--couleur-blanche); padding: 20px; height: fit-content; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filter-group { margin-bottom: 20px; }
        .filter-group h3 { font-size: 1em; font-weight: var(--font-weight-semibold); color: var(--couleur-texte); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid var(--couleur-bordure); }
        .filter-group label, .filter-group .label { display: block; margin-bottom: 6px; font-size: 0.9em; color: var(--couleur-texte-footer); }
        .filter-group input[type="text"], .filter-group input[type="date"], .filter-group select, .filter-group input[type="number"] { width: 100%; padding: 10px; border: 1px solid var(--couleur-bordure); border-radius: 6px; font-size: 0.9em; box-sizing: border-box; }
        .price-input-container { display: flex; align-items: flex-end; gap: 8px; margin-top: 5px; }
        .price-input-field { display: flex; flex-direction: column; flex-grow: 1; }
        .price-input-field label { font-size: 0.8em; margin-bottom: 4px; color: var(--couleur-texte-footer); }
        .results-area { flex-grow: 1; }
        .search-and-sort-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .search-bar-results { display: flex; align-items: center; background-color: var(--couleur-blanche); border: 1px solid var(--couleur-bordure); border-radius: 25px; padding: 0 10px 0 15px; height: 45px; flex-grow: 1; min-width: 200px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .search-bar-results input[type="text"] { flex-grow: 1; padding: 10px; border: none; outline: none; font-size: 0.95em; background-color: transparent; }
        .search-bar-results button { background-color: transparent; color: var(--couleur-principale); border: none; padding: 10px; cursor: pointer; font-size: 1.1em; }
        .sort-options { display: flex; gap: 8px; }
        .sort-options .sort-button { text-decoration: none; background-color: var(--couleur-blanche); color: var(--couleur-texte); border: 1px solid var(--couleur-bordure); padding: 8px 18px; border-radius: 20px; cursor: pointer; font-size: 0.9em; font-weight: var(--font-weight-medium); transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
        .sort-options .sort-button:hover { border-color: var(--couleur-principale); color: var(--couleur-principale); }
        .sort-options .sort-button.active { background-color: var(--couleur-principale); color: var(--couleur-blanche); border-color: var(--couleur-principale); }
        .reset-filters-btn { width:100%; background-color: transparent; color: var(--couleur-principale); border: 1px solid var(--couleur-principale); padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 1em; font-weight: var(--font-weight-medium); transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; margin-top: 10px; }
        .reset-filters-btn:hover { background-color: var(--couleur-secondaire-hover-bg); }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .card-bo { background-color: var(--couleur-blanche); border: 1px solid var(--couleur-bordure); border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; overflow: hidden; }
        .card-bo .card-image-wrapper { position: relative; width: 100%; height: 180px; background-color: #f0f0f0; }
        .card-bo .card-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .card-bo .card-content { display: flex; flex-direction: column; padding: 15px; flex-grow: 1; }
        .card-bo .card-title { font-size: 1.2em; font-weight: var(--font-weight-semibold); color: var(--couleur-texte); margin-bottom: 8px; }
        .card-bo .card-category { font-size: 0.9em; color: var(--couleur-texte-footer); margin-bottom: 12px; }
        .card-bo .card-actions-bo { display: flex; gap: 10px; margin-top: auto; padding-top: 15px; border-top: 1px solid var(--couleur-bordure); }
        .card-bo .btn-action { text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 0.85em; font-weight: 500; text-align: center; flex-grow: 1; transition: opacity 0.2s; }
        .card-bo .btn-action i { margin-right: 6px; }
        .card-bo .btn-view { background-color: #e9ecef; color: #495057; }
        .card-bo .btn-edit { background-color: var(--couleur-secondaire); color: var(--couleur-principale); }
        .card-bo .btn-delete { background-color: #f8d7da; color: #721c24; }
        .card-bo .btn-action:hover { opacity: 0.8; }
        .no-results { grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d; }

        @media (max-width: 992px) { .search-page-container { flex-direction: column; } .filters-sidebar { width: 100%; } }
        @media (max-width: 768px) { .search-and-sort-controls { flex-direction: column; align-items: stretch; } .sort-options { justify-content: space-around; } }

    .main-nav ul li.nav-item-with-notification {
        position: relative; /* Contexte pour le positionnement absolu de la bulle */
    }

    .profile-link-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .notification-bubble {
        position: absolute;
        top: -16px;
        right: 80px;
        width: 20px;
        height: 20px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8em;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .header-right .profile-link-container + .btn-primary {
        margin-left: 1rem; 
    }

    .nav-item-with-notification .notification-bubble {
        position: absolute;
        top: -15px; /* Ajustez pour la position verticale */
        right: 80px; /* Ajustez pour la position horizontale */
        width: 20px;
        height: 20px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75em; /* Police un peu plus petite pour la nav */
        font-weight: bold;
        border: 2px solid white;
    }
    /* Styles pour le filtre de note */
    .star-rating-filter {
        display: flex;
        gap: 5px;
        margin-top: 5px;
    }
    .star-rating-filter .star-option {
        cursor: pointer;
        color: #e0e0e0; /* Couleur des étoiles non sélectionnées */
        font-size: 1.2em;
    }
    .star-rating-filter .star-option.active {
        color: var(--couleur-principale); /* Couleur des étoiles sélectionnées */
    }
    /* Styles pour le filtre de statut */
    .status-filter label {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        cursor: pointer;
    }
    .status-filter input[type="radio"] {
        margin-right: 5px;
    }
    </style>
</head>
<body>
    <header>
    <div class="container header-container">
        <div class="header-left">
            <a href="index.php"><img src="images/Logowithoutbgorange.png" alt="Logo" class="logo"></a>
            <span class="pro-text">Professionnel</span>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php" >Accueil</a></li>
                <li class="nav-item-with-notification">
                    <a href="recherche.php" class="active">Mes Offres</a>
                    <?php if (isset($unanswered_reviews_count) && $unanswered_reviews_count > 0): ?>
                        <span class="notification-bubble"><?php echo $unanswered_reviews_count; ?></span>
                    <?php endif; ?>
                </li>
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <div class="profile-link-container">
                <a href="profil.php" class="btn btn-secondary">Mon profil</a>
            </div>
            <a href="../deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>
    
    <main>
        <div class="container content-area search-page-container">
            <aside class="filters-sidebar">
                <form method="GET" action="recherche.php" id="filters-form">
                    <h3>Filtres</h3>
                    <div class="filter-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="">Toutes</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if ($category_id == $cat['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars(ucfirst($cat['type'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" placeholder="Lannion, Paris..." value="<?php echo htmlspecialchars($destination); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Prix</label>
                        <div class="price-input-container">
                            <div class="price-input-field">
                                <input type="number" id="price-min-input" name="price_min_input" min="0" step="1" placeholder="Min €" value="<?php echo htmlspecialchars((string)$priceMin); ?>">
                            </div>
                            <span class="price-input-separator">-</span>
                            <div class="price-input-field">
                                <input type="number" id="price-max-input" name="price_max_input" min="0" step="1" placeholder="Max €" value="<?php echo htmlspecialchars((string)$priceMax); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="filter-group">
                    <h3>Date</h3>
                        <div class="input-with-icon">
                            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selectedDate ?? ''); ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Notes</h3>
                        <div class="label">Minimale</div>
                        <div class="star-rating-filter" id="min-rating-filter">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star-option <?php if ($minRating >= $i) echo 'active'; ?>" data-rating="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="min_rating" id="min-rating-input" value="<?php echo htmlspecialchars((string)$minRating); ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Statut</h3>
                        <div class="status-filter">
                        <label><input type="radio" name="status" value="open" <?php if ($statusFilter === 'open') echo 'checked'; ?>> Ouvert</label>
                        <label><input type="radio" name="status" value="closed" <?php if ($statusFilter === 'closed') echo 'checked'; ?>> Fermé</label>
                        </div>
                    </div>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="button" id="reset-filters-btn" class="reset-filters-btn">Réinitialiser</button>
                </form>
            </aside>

            <section class="results-area">
                <div class="search-and-sort-controls">
                     <form method="GET" action="recherche.php" class="search-bar-results">
                        <input type="text" name="q" placeholder="Rechercher dans mes offres..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                        <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                        <input type="hidden" name="price_min_input" value="<?php echo htmlspecialchars((string)$priceMin); ?>">
                        <input type="hidden" name="price_max_input" value="<?php echo htmlspecialchars((string)$priceMax); ?>">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($selectedDate ?? ''); ?>">
                        <input type="hidden" name="min_rating" value="<?php echo htmlspecialchars((string)$minRating); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter ?? ''); ?>">
                        <button type="submit" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="sort-options">
                        <?php
                            echo getSortLink('note', $sort, $current_filters);
                            echo getSortLink('price_asc', $sort, $current_filters);
                            echo getSortLink('price_desc', $sort, $current_filters);
                        ?>
                    </div>
                </div>

                <div class="results-grid">
                    <?php if (empty($offers)): ?>
                        <p class="no-results">Aucune de vos offres ne correspond à ces critères.</p>
                    <?php else: ?>
                        <?php foreach ($offers as $offer): ?>
                            <div class="card-bo">
                                <div class="card-image-wrapper">
                                    <img src="../<?php echo htmlspecialchars($offer['main_photo'] ?? 'BO/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>">
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                    <p class="card-category"><?php echo htmlspecialchars(ucfirst($offer['category_type'])); ?></p>
                                    <div class="star-rating">
                                        <?php echo renderStars($offer['offer_rating']); ?>
                                    </div>
                                    <div class="card-actions-bo">
                                        <a href="offre.php?id=<?= $offer['id'] ?>" class="btn-action btn-view"><i class="fas fa-eye"></i> Voir</a>
                                        <a href="modifier-offre.php?id=<?= $offer['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                                        <?php if ($offer['current_status'] == 1): // Si le statut est 1 (Actif) ?>
                                                <a href="../composants/changer-status-offre.php?id=<?= $offer['id'] ?>&status=0" class="btn-action btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette offre ?');">
                                                    <i class="fas fa-toggle-off"></i> Rendre Inactif
                                                </a>
                                            <?php else: // Si le statut est 0 (Inactif) ?>
                                                <a href="../composants/changer-status-offre.php?id=<?= $offer['id'] ?>&status=1" class="btn-action btn-view" style="background-color: #d4edda; color: #155724;" onclick="return confirm('Êtes-vous sûr de vouloir réactiver cette offre ?');">
                                                    <i class="fas fa-toggle-on"></i> Rendre Actif
                                                </a>
                                            <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Visiteur</h3>
                <ul>
                    <li><a href="../index.html">Accueil</a></li>
                    <li><a href="../FO/recherche.php">Recherche d'offres</a></li>
                    <li><a href="../FO/connexion-compte.php">Je me connecte en tant que membre</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Découvrir</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="publier-une-offre.php">Publier une offre</a></li>
                    <li><a href="profil.php">Profil</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Ressources</h3>
                <ul>
                    <li><a href="conditions-generales-d-utilisation.php">Conditions générales d'utilisation</a></li>
                    <li><a href="contact-du-responsable-du-site.php">Contact du responsable du site</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PACT. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtersForm = document.getElementById('filters-form');
        const resetFiltersBtn = document.getElementById('reset-filters-btn');

        // Soumission du formulaire lors du changement des SELECT et des INPUT date/radio
        filtersForm.addEventListener('change', function(event) {
            if (event.target.tagName === 'SELECT' || event.target.type === 'date' || event.target.type === 'radio') {
                filtersForm.submit();
            }
        });
        
        // Soumission pour les champs texte/nombre lors de l'appui sur "Entrée"
        filtersForm.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && (event.target.type === 'text' || event.target.type === 'number')) {
                event.preventDefault(); // Empêche le comportement par défaut (qui peut être différent)
                filtersForm.submit();
            }
        });

        // Gestion du filtre de note par étoiles
        const minRatingFilter = document.getElementById('min-rating-filter');
        const minRatingInput = document.getElementById('min-rating-input');
        const couleurPrincipale = getComputedStyle(document.documentElement).getPropertyValue('--couleur-principale').trim();

        if (minRatingFilter && minRatingInput) {
            const stars = Array.from(minRatingFilter.querySelectorAll('.fa-star'));

            const updateStarVisuals = (selectedRating) => {
                stars.forEach(s => {
                    const starValue = parseInt(s.dataset.rating, 10);
                    if (starValue <= selectedRating) {
                        s.classList.add('active');
                        s.style.color = couleurPrincipale;
                    } else {
                        s.classList.remove('active');
                        s.style.color = '#e0e0e0';
                    }
                });
            };

            // Initialiser l'affichage des étoiles au chargement de la page
            updateStarVisuals(minRatingInput.value);

            minRatingFilter.addEventListener('click', function(e) {
                if (e.target.classList.contains('star-option')) {
                    const rating = e.target.dataset.rating;
                    if (minRatingInput.value === rating) { 
                        minRatingInput.value = ""; // Désélectionne si on clique sur l'étoile déjà sélectionnée
                    } else {
                        minRatingInput.value = rating;
                    }
                    filtersForm.submit(); // Soumet le formulaire après la sélection
                }
            });

            minRatingFilter.addEventListener('mouseover', function(e) {
                if (e.target.classList.contains('star-option')) {
                    const hoverRating = parseInt(e.target.dataset.rating, 10);
                    stars.forEach(s => {
                        const starValue = parseInt(s.dataset.rating, 10);
                        if (starValue <= hoverRating) {
                            s.style.color = couleurPrincipale;
                        } else {
                            s.style.color = '#e0e0e0';
                        }
                    });
                }
            });

            minRatingFilter.addEventListener('mouseout', function() {
                updateStarVisuals(minRatingInput.value); // Revenir à l'état sélectionné
            });
        }


        resetFiltersBtn.addEventListener('click', function() {
            const url = new URL(window.location);
            window.location.href = url.pathname; 
        });
    });
    </script>
</body>
</html>