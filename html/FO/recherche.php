<?php
// On se connecte à la base de données
require_once(__DIR__ . '/../../includes/db.php');

// --- RÉCUPÉRATION DES CATÉGORIES POUR LE FILTRE (Requête Corrigée) ---
$category_stmt = $pdo->query('SELECT id, type FROM categories ORDER BY type');
$all_categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- RÉCUPÉRATION DES FILTRES ET DU TRI ---
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$priceMin = isset($_GET['price_min_input']) && $_GET['price_min_input'] !== '' ? (float)$_GET['price_min_input'] : null;
$priceMax = isset($_GET['price_max_input']) && $_GET['price_max_input'] !== '' ? (float)$_GET['price_max_input'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'note'; // Tri par défaut

// --- CONSTRUCTION DE LA REQUÊTE SQL ---
$sql = 'SELECT offres.*, categories.type as category_type FROM offres 
        JOIN adresses ON offres.adresse_id = adresses.id
        JOIN categories ON offres.categorie_id = categories.id';
$conditions = [];
$params = [];

// Filtre par mot-clé
if (!empty($searchTerm)) {
    $conditions[] = '(offres.title LIKE ? OR offres.summary LIKE ?)';
    $likeTerm = '%' . $searchTerm . '%';
    $params[] = $likeTerm;
    $params[] = $likeTerm;
}

// Filtre par catégorie (maintenant fonctionnel)
if ($category_id !== null) {
    $conditions[] = 'offres.categorie_id = ?';
    $params[] = $category_id;
}

// Filtre par destination
if (!empty($destination)) {
    $conditions[] = 'adresses.city LIKE ?';
    $params[] = '%' . $destination . '%';
}

// Filtre par prix
if ($priceMin !== null) {
    $conditions[] = 'offres.price >= ?';
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $conditions[] = 'offres.price <= ?';
    $params[] = $priceMax;
}

// Ajout des conditions
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

// --- AJOUT DU TRI ---
switch ($sort) {
    case 'price_asc':
        $sql .= ' ORDER BY offres.price ASC';
        break;
    case 'price_desc':
        $sql .= ' ORDER BY offres.price DESC';
        break;
    case 'note':
    default:
        $sql .= ' ORDER BY offres.id DESC';
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
        .apply-filters-btn { width:100%; background-color: var(--couleur-principale); color: var(--couleur-blanche); border: 1px solid var(--couleur-principale); padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 1em; font-weight: var(--font-weight-medium); transition: background-color 0.2s ease; }
        .apply-filters-btn:hover { background-color: var(--couleur-principale-hover); }
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
        @media (max-width: 992px) { .search-page-container { flex-direction: column; } .filters-sidebar { width: 100%; margin-bottom: 20px; } .results-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); } }
        @media (max-width: 768px) { .search-and-sort-controls { flex-direction: column; align-items: stretch; } .search-bar-results { width: 100%; } .sort-options { display: flex; justify-content: space-between; width: 100%; gap: 5px; } .sort-options .sort-button { margin-left: 0; flex-grow: 1; padding: 8px 10px; } .results-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); } .filters-sidebar { padding: 15px;} .price-input-container { flex-direction: column; align-items: stretch; gap: 10px; } .price-input-separator { display: none; } }
        @media (max-width: 520px) { .results-grid { grid-template-columns: 1fr; } .card .card-image-wrapper { height: 180px; } }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="header-left">
                <a href="../index.html"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="logo"></a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.html">Accueil</a></li>
                        <li><a href="recherche.php" class="active">Recherche</a></li>
                    </ul>
                </nav>
            </div>
            <div class="header-right">
                <a href="../BO/index.php" class="pro-link desktop-only">Je suis professionnel</a>
                <a href="creation-compte.php" class="btn btn-secondary desktop-only">S'enregistrer</a>
                <a href="connexion-compte.php" class="btn btn-primary desktop-only">Se connecter</a>
                <div class="mobile-icons">
                    <a href="index.php" class="mobile-icon" aria-label="Accueil"><i class="fas fa-home"></i></a>
                    <a href="profil.php" class="mobile-icon" aria-label="Profil"><i class="fas fa-user"></i></a>
                    <button class="mobile-icon hamburger-menu" aria-label="Menu" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        <nav class="mobile-nav-links">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="recherche.php" class="active">Recherche</a></li>
                <li><a href="../BO/index.php">Je suis professionnel</a></li>
                <li><a href="creation-compte.php">S'enregistrer</a></li>
                <li><a href="connexion-compte.php">Se connecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area search-page-container">
            <form method="GET" action="recherche.php" class="filters-sidebar">
                <div class="filter-group">
                    <h3>Catégorie</h3>
                    <select id="category" name="category">
                        <option value="">Toutes</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if ($category_id == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars(ucfirst($cat['type'])); // On affiche la colonne 'type' ?>
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
                <div class="filter-group" title="Ce filtre n'est pas encore actif.">
                    <h3 style="color:#999;">Date (Inactif)</h3>
                    <div class="input-with-icon"> <input type="date" id="date" name="date" disabled> </div>
                </div>
                <div class="filter-group" title="Ce filtre n'est pas encore actif.">
                    <h3 style="color:#999;">Notes (Inactif)</h3>
                    <div class="label">Minimale</div>
                    <div class="star-rating-filter"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                </div>
                <div class="filter-group" title="Ce filtre n'est pas encore actif.">
                    <h3 style="color:#999;">Statut (Inactif)</h3>
                    <div class="status-filter">
                       <label><input type="radio" name="status" value="open" disabled> Ouvert</label>
                       <label><input type="radio" name="status" value="closed" disabled> Fermé</label>
                    </div>
                </div>
                <button type="submit" class="apply-filters-btn">Appliquer les filtres</button>
            </form>

            <section class="results-area">
                <div class="search-and-sort-controls">
                     <form method="GET" action="recherche.php" class="search-bar-results">
                        <input type="text" name="q" placeholder="Rechercher une offre..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars((string)$category_id); ?>">
                        <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                        <input type="hidden" name="price_min_input" value="<?php echo htmlspecialchars((string)$priceMin); ?>">
                        <input type="hidden" name="price_max_input" value="<?php echo htmlspecialchars((string)$priceMax); ?>">
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
                            <div class="card">
                                <div class="card-image-wrapper">
                                    <img src="../../<?php echo htmlspecialchars($offer['main_photo']); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>">
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                    <div class="star-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                    </div>
                                    <p class="card-description"><?php echo htmlspecialchars($offer['summary']); ?></p>
                                    <a href="offre.php?id=<?php echo htmlspecialchars($offer['id']); ?>" class="card-more">Voir plus</a>
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
    });
    </script>
</body>
</html>