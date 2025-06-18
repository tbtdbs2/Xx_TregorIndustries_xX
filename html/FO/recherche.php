<?php
// On se connecte à la base de données
require_once(__DIR__ . '/../../includes/db.php');

// --- RÉCUPÉRATION DES CATÉGORIES POUR LE FILTRE ---
$category_stmt = $pdo->query('SELECT id, type FROM categories ORDER BY type');
$all_categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- RÉCUPÉRATION DES FILTRES ET DU TRI ---
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) ? $_GET['category'] : ''; 
$category_type = isset($_GET['category_type']) ? trim($_GET['category_type']) : '';
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$priceMin = isset($_GET['price_min_input']) && $_GET['price_min_input'] !== '' ? (float)$_GET['price_min_input'] : null;
$priceMax = isset($_GET['price_max_input']) && $_GET['price_max_input'] !== '' ? (float)$_GET['price_max_input'] : null;

// NOUVEAU : Récupération du filtre de note
$minRating = isset($_GET['min_rating']) && $_GET['min_rating'] !== '' ? (float)$_GET['min_rating'] : null;

// NOUVEAU : Récupération du filtre de date
$selectedDate = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : null;

// NOUVEAU : Récupération du filtre de statut
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null; // 'open', 'closed' ou null


$sort = isset($_GET['sort']) ? $_GET['sort'] : 'note';

// --- CORRECTION : LOGIQUE POUR PRÉ-REMPLIR LE FILTRE CATÉGORIE ---
// Si on reçoit un category_type (depuis la page d'accueil) et que category_id n'est pas déjà défini
if (!empty($category_type) && empty($category_id)) {
    // On parcourt toutes les catégories pour trouver l'ID correspondant au type
    foreach ($all_categories as $cat) {
        if ($cat['type'] === $category_type) {
            $category_id = $cat['id']; // On définit le category_id
            break; // On a trouvé, on arrête la boucle
        }
    }
}
// --- FIN DE LA CORRECTION ---


// --- CONSTRUCTION DE LA REQUÊTE SQL ---
$sql = 'SELECT o.*, c.type as category_type, 
               COALESCE(s.status, 0) as current_status, 
               COALESCE(o.rating, 0) as offer_rating
        FROM offres o
        JOIN adresses a ON o.adresse_id = a.id
        JOIN categories c ON o.categorie_id = c.id';
// NOUVEAU : Jointure pour le statut. On utilise LEFT JOIN pour inclure les offres sans statut explicite (considérées comme fermées par défaut ou en attente)
// et on prend le statut le plus récent.
$sql .= ' LEFT JOIN (
            SELECT offre_id, status
            FROM statuts
            WHERE (offre_id, changed_at) IN (
                SELECT offre_id, MAX(changed_at)
                FROM statuts
                GROUP BY offre_id
            )
        ) s ON o.id = s.offre_id';

$conditions = [];
$params = [];

if (!empty($searchTerm)) {
    $conditions[] = '(o.title LIKE ? OR o.summary LIKE ?)';
    $likeTerm = '%' . $searchTerm . '%';
    $params[] = $likeTerm;
    $params[] = $likeTerm;
}

if (!empty($category_id)) {
    $conditions[] = 'o.categorie_id = ?';
    $params[] = $category_id;
} elseif (!empty($category_type)) { 
    $conditions[] = 'c.type = ?';
    $params[] = $category_type;
}

if (!empty($destination)) {
    $conditions[] = 'a.city LIKE ?';
    $params[] = '%' . $destination . '%';
}

if ($priceMin !== null) {
    $conditions[] = 'o.price >= ?';
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $conditions[] = 'o.price <= ?';
    $params[] = $priceMax;
}

// NOUVEAU : Condition pour le filtre de note
if ($minRating !== null) {
    $conditions[] = 'COALESCE(o.rating, 0) >= ?'; // Utilise COALESCE pour gérer les ratings NULL (les considérer comme 0)
    $params[] = $minRating;
}

// NOUVEAU : Condition pour le filtre de statut
if ($statusFilter !== null) {
    if ($statusFilter === 'open') {
        $conditions[] = 'COALESCE(s.status, 0) = 1'; // Statut 1 pour "ouvert"
    } elseif ($statusFilter === 'closed') {
        $conditions[] = 'COALESCE(s.status, 0) = 0'; // Statut 0 pour "fermé" (ou non trouvé dans la table statuts)
    }
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

// NOUVEAU : Gestion complexe du filtre de date
if ($selectedDate) {
    $selectedDateTime = new DateTime($selectedDate);
    $selectedDayOfWeek = strtolower($selectedDateTime->format('l')); // 'monday', 'tuesday', etc.
    
    // On doit filtrer les résultats déjà obtenus par la première requête
    // Pour cela, on va récupérer les IDs des offres qui correspondent à la date
    // Et ensuite les utiliser dans une clause IN ou similaire.
    // C'est plus simple de faire un deuxième passe ou de construire la requête de manière plus complexe.
    // Pour la simplicité et la lisibilité, nous allons faire un post-filtrage ici
    // ou une sous-requête complexe si nécessaire.
    // Étant donné la complexité de la jointure des horaires, on va d'abord récupérer toutes les offres
    // qui correspondent aux autres filtres, puis les filtrer par PHP.
    // Ou, mieux, adapter la requête SQL pour inclure la logique de date.

    // Pour l'intégration SQL, cela nécessiterait des jointures conditionnelles ou des UNION.
    // Pour simplifier et éviter une requête trop lourde, nous allons ajouter une logique après la récupération.
    // Cependant, pour que le filtre soit efficace côté BDD, il faut l'intégrer.

    // La stratégie la plus robuste est de faire un LEFT JOIN conditionnel ou d'utiliser EXISTS
    // car une offre peut avoir plusieurs types d'horaires.

    $dateConditions = [];
    $dateParams = [];

    // Sub-query pour activités
    $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_activites ha 
                                JOIN activites a_cat ON ha.activite_id = a_cat.categorie_id 
                                WHERE o.categorie_id = a_cat.categorie_id AND ha.date = ?)';
    $dateParams[] = $selectedDate;

    // Sub-query pour spectacles
    $dateConditions[] = 'EXISTS (SELECT 1 FROM spectacles s_cat 
                                WHERE o.categorie_id = s_cat.categorie_id AND s_cat.date = ?)';
    $dateParams[] = $selectedDate;
    
    // Sub-query pour visites
    $dateConditions[] = 'EXISTS (SELECT 1 FROM visites v_cat 
                                WHERE o.categorie_id = v_cat.categorie_id AND v_cat.date = ?)';
    $dateParams[] = $selectedDate;

    // Sub-query pour parcs_attractions et restaurations (basé sur horaires_attractions / horaires_restaurants)
    // Ici, le filtre est basé sur le jour de la semaine
    $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_attractions hat
                                JOIN attractions attr ON hat.attraction_id = attr.id
                                JOIN parcs_attractions pa_cat ON attr.parc_attractions_id = pa_cat.categorie_id
                                WHERE o.categorie_id = pa_cat.categorie_id AND hat.day = ?)';
    $dateParams[] = $selectedDayOfWeek;

    $dateConditions[] = 'EXISTS (SELECT 1 FROM horaires_restaurants hres
                                JOIN restaurations r_cat ON hres.restauration_id = r_cat.categorie_id
                                WHERE o.categorie_id = r_cat.categorie_id AND hres.day = ?)';
    $dateParams[] = $selectedDayOfWeek;
    
    // Si des conditions de date sont définies, nous devons les encapsuler correctement.
    // Il faut que l'offre corresponde à AU MOINS UNE des conditions de date.
    if (!empty($dateConditions)) {
        // Ajout d'une condition OR englobant toutes les conditions de date
        if (!empty($conditions)) {
            $sql .= ' AND (' . implode(' OR ', $dateConditions) . ')';
        } else {
            $sql .= ' WHERE (' . implode(' OR ', $dateConditions) . ')';
        }
        $params = array_merge($params, $dateParams);
    }
}


// --- AJOUT DU TRI ---
switch ($sort) {
    case 'price_asc':
        $sql .= ' ORDER BY o.price ASC';
        break;
    case 'price_desc':
        $sql .= ' ORDER BY o.price DESC';
        break;
    case 'note':
        $sql .= ' ORDER BY offer_rating DESC, o.id DESC'; // Tri par note (la plus haute en premier), puis par ID si notes égales
        break;
    default:
        $sql .= ' ORDER BY o.id DESC';
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper pour les liens de tri
function getSortLink($sortValue, $currentSort, $filters) {
    $filters['sort'] = $sortValue;
    $queryString = http_build_query($filters);
    $activeClass = ($sortValue == $currentSort) ? 'active' : '';
    $labels = ['note' => 'Note', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant'];
    return '<a href="?' . $queryString . '" class="sort-button ' . $activeClass . '">' . $labels[$sortValue] . '</a>';
}

$current_filters = $_GET;
unset($current_filters['sort']);

// Helper pour le rendu des étoiles
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Recherche</title><link rel="icon" href="images/Logo2withoutbg.png">
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
        .input-with-icon { position: relative; }
        .input-with-icon input[type="text"] { padding-right: 30px; }
        .input-with-icon .fa-map-marker-alt, .input-with-icon .fa-calendar-alt { position: absolute; top: 50%; right: 10px; transform: translateY(-50%); color: var(--couleur-texte-footer); pointer-events: none; }
        .price-input-container { display: flex; align-items: flex-end; gap: 8px; margin-top: 5px; }
        .price-input-field { display: flex; flex-direction: column; flex-grow: 1; }
        .price-input-field label { font-size: 0.8em; margin-bottom: 4px; color: var(--couleur-texte-footer); }
        .price-input-container input[type="number"] { -moz-appearance: textfield; }
        .price-input-container input[type="number"]::-webkit-outer-spin-button, .price-input-container input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .price-input-separator { font-size: 1em; color: var(--couleur-texte-footer); padding-bottom: 10px; }
        .results-area { flex-grow: 1; }
        .search-and-sort-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .search-bar-results { display: flex; align-items: center; background-color: var(--couleur-blanche); border: 1px solid var(--couleur-bordure); border-radius: 25px; padding: 0 10px 0 15px; height: 45px; flex-grow: 1; min-width: 200px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .search-bar-results input[type="text"] { flex-grow: 1; padding: 10px; border: none; outline: none; font-size: 0.95em; background-color: transparent; }
        .search-bar-results button { background-color: transparent; color: var(--couleur-principale); border: none; padding: 10px; cursor: pointer; font-size: 1.1em; }
        .sort-options { display: flex; gap: 8px; }
        .sort-options .sort-button { text-decoration: none; background-color: var(--couleur-blanche); color: var(--couleur-texte); border: 1px solid var(--couleur-bordure); padding: 8px 18px; border-radius: 20px; cursor: pointer; font-size: 0.9em; font-weight: var(--font-weight-medium); transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
        .sort-options .sort-button:hover { border-color: var(--couleur-principale); color: var(--couleur-principale); }
        .sort-options .sort-button.active { background-color: var(--couleur-principale); color: var(--couleur-blanche); border-color: var(--couleur-principale); }
        .reset-filters-btn { width:100%; background-color: transparent; color: var(--couleur-principale); border: 1px solid var(--couleur-principale); padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 1em; font-weight: var(--font-weight-medium); transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
        .reset-filters-btn:hover { background-color: var(--couleur-secondaire-hover-bg); color: var(--couleur-principale-hover); border-color: var(--couleur-secondaire-hover-border); }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
        .card { background-color: var(--couleur-blanche); border: 1px solid var(--couleur-bordure); border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; overflow: hidden; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .card .card-image-wrapper { position: relative; width: 100%; height: 160px; }
        .card .card-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .card .card-content { flex-grow: 1; display: flex; flex-direction: column; padding: 15px; }
        .card .card-title { font-size: 1.1em; font-weight: var(--font-weight-semibold); color: var(--couleur-texte); margin-bottom: 8px; }
        .card .star-rating { margin-bottom: 10px; color: var(--couleur-principale); font-size: 0.9em; }
        .card .star-rating .far.fa-star { color: var(--couleur-bordure); }
        .card .card-description { font-size: 0.85em; color: #555; line-height: 1.5; margin-bottom: 12px; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: calc(0.85em * 1.5 * 3); }
        .card .card-more { font-size: 0.9em; color: var(--couleur-principale); text-decoration: none; font-weight: var(--font-weight-medium); align-self: flex-start; }
        .card .card-more:hover { text-decoration: underline; color: var(--couleur-principale-hover); }
        .card-link {text-decoration: none; color: inherit; display: block;}
        @media (max-width: 992px) { .search-page-container { flex-direction: column; } .filters-sidebar { width: 100%; margin-bottom: 20px; } .results-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); } }
        @media (max-width: 768px) { .search-and-sort-controls { flex-direction: column; align-items: stretch; } .search-bar-results { width: 100%; } .sort-options { display: flex; justify-content: space-between; width: 100%; gap: 5px; } .sort-options .sort-button { margin-left: 0; flex-grow: 1; padding: 8px 10px; } .results-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); } .filters-sidebar { padding: 15px;} .price-input-container { flex-direction: column; align-items: stretch; gap: 10px; } .price-input-separator { display: none; } }
        @media (max-width: 520px) { .results-grid { grid-template-columns: 1fr; } .card .card-image-wrapper { height: 180px; } }

        /* Styles pour le filtre de note */
        .star-rating-filter {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        .star-rating-filter .star-option {
            cursor: pointer;
            color: var(--couleur-bordure); /* Couleur des étoiles non sélectionnées */
            font-size: 1.2em;
        }
        .star-rating-filter .star-option.selected,
        .star-rating-filter .star-option:hover {
            color: var(--couleur-principale); /* Couleur des étoiles sélectionnées ou survolées */
        }
        .star-rating-filter .star-option.active {
             color: var(--couleur-principale);
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
    <?php require_once 'header.php'; ?>
    <main>
        <div class="container content-area search-page-container">
            <form method="GET" action="recherche.php" class="filters-sidebar" id="filters-form">
                <div class="filter-group">
                    <h3>Catégorie</h3>
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
                    <h3>Destination</h3>
                    <div class="input-with-icon">
                        <input type="text" id="destination" name="destination" placeholder="Lannion, Paris..." value="<?php echo htmlspecialchars($destination); ?>">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>
                <div class="filter-group">
                    <h3>Prix</h3>
                    <div class="price-input-container">
                        <div class="price-input-field">
                            <label for="price-min-input">Min €</label>
                            <input type="number" id="price-min-input" name="price_min_input" min="0" step="1" placeholder="0" value="<?php echo htmlspecialchars((string)$priceMin); ?>">
                        </div>
                        <span class="price-input-separator">-</span>
                        <div class="price-input-field">
                            <label for="price-max-input">Max €</label>
                            <input type="number" id="price-max-input" name="price_max_input" min="0" step="1" placeholder="Max" value="<?php echo htmlspecialchars((string)$priceMax); ?>">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>">
                
                <div class="filter-group">
                    <h3>Date</h3>
                    <div class="input-with-icon">
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                        <i class="fas fa-calendar-alt"></i>
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

                <button type="button" id="reset-filters-btn" class="reset-filters-btn">Réinitialiser les filtres</button>
            </form>

            <section class="results-area">
                <div class="search-and-sort-controls">
                     <form method="GET" action="recherche.php" class="search-bar-results">
                        <input type="text" name="q" placeholder="Rechercher une offre..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                        <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                        <input type="hidden" name="price_min_input" value="<?php echo htmlspecialchars((string)$priceMin); ?>">
                        <input type="hidden" name="price_max_input" value="<?php echo htmlspecialchars((string)$priceMax); ?>">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                        <input type="hidden" name="min_rating" value="<?php echo htmlspecialchars((string)$minRating); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
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
                        <p>Aucune offre ne correspond à vos critères de recherche.</p>
                    <?php else: ?>
                        <?php foreach ($offers as $offer): ?>
                            <a href="offre.php?id=<?php echo htmlspecialchars($offer['id']); ?>" class="card-link">
                                <div class="card">
                                    <div class="card-image-wrapper">
                                        <img src="../../<?php echo htmlspecialchars($offer['main_photo']); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>">
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                        <div class="star-rating">
                                            <?php echo renderStars($offer['offer_rating']); ?>
                                        </div>
                                        <p class="card-description"><?php echo htmlspecialchars($offer['summary']); ?></p>
                                        <span class="card-more">Voir plus</span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <a href="../index.html"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="footer-logo"></a>
                <div class="social-icons">
                    <a href="#" aria-label="Twitter PACT"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram PACT"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube PACT"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn PACT"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Professionnel</h3>
                <ul>
                    <li><a href="../BO/index.php">Comment poster une annonce</a></li>
                    <li><a href="../BO/creation-compte.php">Je crée mon compte pro</a></li>
                    <li><a href="../BO/connexion-compte.php">Je me connecte en tant que pro</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Découvrir</h3>
                <ul>
                    <li><a href="../index.html">Accueil</a></li>
                    <li><a href="recherche.php">Recherche</a></li>
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
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const mobileNavLinks = document.querySelector('.mobile-nav-links');
        if (hamburgerMenu && mobileNavLinks) {
            hamburgerMenu.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                mobileNavLinks.classList.toggle('active');
            });
        }

        const filtersForm = document.getElementById('filters-form');
        const resetFiltersBtn = document.getElementById('reset-filters-btn');
        const minRatingFilter = document.getElementById('min-rating-filter');
        const minRatingInput = document.getElementById('min-rating-input');

        // Soumission du formulaire lors du changement des filtres (sauf input type number)
        filtersForm.addEventListener('change', function(e) {
            if(e.target.type !== 'number' && e.target.id !== 'min-rating-input') { // Exclure l'input caché de la note
                filtersForm.submit();
            }
        });
        
        // Soumission du formulaire pour les champs prix lors de la touche "Entrée"
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    filtersForm.submit();
                }
            });
        });

        // Gestion du filtre de note
        if (minRatingFilter) {
            minRatingFilter.addEventListener('click', function(e) {
                if (e.target.classList.contains('star-option')) {
                    const rating = e.target.dataset.rating;
                    minRatingInput.value = rating;
                    filtersForm.submit();
                }
            });
        }

        // Réinitialisation des filtres
        resetFiltersBtn.addEventListener('click', function() {
            const url = new URL(window.location);
            url.search = ''; // Vide tous les paramètres de l'URL
            window.location.href = url.href;
        });
    });
    </script>
</body>
</html>