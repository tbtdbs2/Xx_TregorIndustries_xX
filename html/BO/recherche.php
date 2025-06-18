<?php
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
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
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa; 
        }

        .mobile-icons,
        .mobile-nav-links {
            display: none;
        }

        /* Afficher uniquement sur mobile (<=768px) */
        @media (max-width: 768px) {
            .mobile-icons,
            .mobile-nav-links {
                display: block; /* ou flex selon le layout que tu veux */
            }
        }

        .search-page-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 20px 40px;
            gap: 20px;
            background-color: #f9f9f9;
        }
        .filters-sidebar {
            width: 260px;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group h3 {
            font-size: 1rem;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 6px; 
            font-size: 0.9em; 
            color: var(--couleur-texte-footer);
        }

        .filter-group input[type="text"],
        .filter-group input[type="date"],
        .filter-group select {
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            font-size: 14px;
            transition: border 0.3s;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #ff6600;
            background-color: #fff;
        }
        .filter-group input[type="date"],
        .filter-group select {
            width: 100%;
            padding: 10px; 
            border: 1px solid var(--couleur-bordure);
            border-radius: 6px; 
            font-size: 0.9em;
            box-sizing: border-box;
        }
        
        .input-with-icon {
            position: relative;
        }

        .input-with-icon input[type="text"] {
            padding-right: 30px;
        }

        .input-with-icon .fa-map-marker-alt,
        .input-with-icon .fa-calendar-alt {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: var(--couleur-texte-footer);
            pointer-events: none;
        }
        
        .price-input-container {
            display: flex;
            align-items: flex-end; 
            gap: 8px; 
            margin-top: 5px;
        }
        .price-input-field {
            display: flex;
            flex-direction: column; 
            flex-grow: 1;
        }
        .price-input-field label {
            font-size: 0.8em; 
            margin-bottom: 4px;
            color: var(--couleur-texte-footer);
        }
        .price-input-container input[type="number"] {
            width: 100%; 
            padding: 10px; 
            border: 1px solid var(--couleur-bordure);
            border-radius: 6px;
            font-size: 0.9em;
            box-sizing: border-box;
            -moz-appearance: textfield; 
        }
        .price-input-container input[type="number"]::-webkit-outer-spin-button,
        .price-input-container input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none; 
            margin: 0;
        }
        .price-input-separator {
            font-size: 1em;
            color: var(--couleur-texte-footer);
            padding-bottom: 10px; 
        }

        .star-rating-filter { display: flex; justify-content: flex-start; gap: 5px; font-size: 1.3em; cursor: pointer; } 
        .star-rating-filter .fa-star { color: #e0e0e0; }
        .star-rating-filter .fa-star.selected,
        .star-rating-filter .fa-star:hover { color: var(--couleur-principale); } 
        .star-rating-filter .fa-star:hover ~ .fa-star:not(.selected){ color: #e0e0e0; }
        .star-rating-filter:hover .fa-star.selected ~ .fa-star:not(.selected){ color: #e0e0e0; }

        .status-filter label { display: inline-flex; align-items: center; margin-right: 15px; font-size: 0.9em; color: var(--couleur-texte); } 
        .status-filter input[type="radio"] { margin-right: 5px; accent-color: var(--couleur-principale); }

        .results-area { flex-grow: 1; }

        .search-and-sort-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        
        .search-bar-results {
            display: flex;
            align-items: center;
            background-color: var(--couleur-blanche);
            border: 1px solid var(--couleur-bordure);
            border-radius: 25px; 
            padding: 0 10px 0 15px; 
            height: 45px; 
            flex-grow: 1;
            min-width: 250px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .search-bar-results input[type="text"] {
            flex-grow: 1;
            padding: 10px; 
            border: none;
            outline: none;
            font-size: 0.95em;
            background-color: transparent;
        }
        .search-bar-results button {
            background-color: transparent;
            color: var(--couleur-principale);
            border: none;
            padding: 10px; 
            cursor: pointer;
            font-size: 1.1em;
        }

        .sort-options button {
            background-color: var(--couleur-blanche); 
            color: var(--couleur-texte);
            border: 1px solid var(--couleur-bordure);
            padding: 8px 18px; 
            border-radius: 20px; 
            cursor: pointer;
            font-size: 0.9em; 
            font-weight: var(--font-weight-medium);
            margin-left: 8px; 
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .sort-options button:hover {
            border-color: var(--couleur-principale);
            color: var(--couleur-principale);
        }
        .sort-options button.active {
            background-color: var(--couleur-principale);
            color: var(--couleur-blanche);
            border-color: var(--couleur-principale);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
            gap: 25px; 
        }

        .card {
            background-color: var(--couleur-blanche);
            border: 1px solid var(--couleur-bordure);
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            overflow: hidden; 
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .card .card-image-wrapper {
            position: relative;
            width: 100%;
            height: 160px; 
        }
        .card .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .card .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 15px; 
        }
        .card .card-title {
            font-size: 1.1em; 
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 8px; 
        }
        .card .star-rating {
            margin-bottom: 10px; 
            color: var(--couleur-principale); 
            font-size: 0.9em;
        }
        .card .star-rating .far.fa-star { color: var(--couleur-bordure); }
        
        .card .card-description {
            font-size: 0.85em; 
            color: #555; 
            line-height: 1.5; 
            margin-bottom: 12px; 
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3; 
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: calc(0.85em * 1.5 * 3); 
        }
        .card .card-more {
            font-size: 0.9em; 
            color: var(--couleur-principale);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            align-self: flex-start;
        }
        .card .card-more:hover {
            text-decoration: underline;
            color: var(--couleur-principale-hover);
        }

        @media (max-width: 992px) { 
            .search-page-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 20px 40px;
            gap: 20px;
            background-color: #f9f9f9;
        }
        .filters-sidebar {
            width: 260px;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .results-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
        }
        @media (max-width: 768px) { 
            .search-and-sort-controls { flex-direction: column; align-items: stretch; }
            .search-bar-results { width: 100%; margin-bottom: 10px; }
            .sort-options { display: flex; justify-content: space-between; width: 100%; gap: 5px; }
            .sort-options button { margin-left: 0; flex-grow: 1; padding: 8px 10px; }
            .results-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
            .filters-sidebar {
                width: 260px;
                background-color: white;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            .price-input-container { flex-direction: column; align-items: stretch; gap: 10px; }
            .price-input-separator { display: none; }
            .price-input-field label { font-size: 0.9em; }
        }
        @media (max-width: 520px) { 
            .results-grid { grid-template-columns: 1fr; }
            .card .card-image-wrapper { height: 180px; } 
            .card .card-title { font-size: 1.05em; }
            .card .card-description { -webkit-line-clamp: 3; }
            .sort-options button { font-size: 0.8em; }
        }
       /* --- STYLES POUR LA NOTIFICATION PROFIL --- */

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

    .header-right .profile-link-container + .btn-primary {
        margin-left: 1rem; 
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
                <li><a href="index.php" class="active">Accueil</a></li>
                <li class="nav-item-with-notification">
                    <a href="recherche.php">Mes Offres</a>
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

                <div class="filter-group">
                    <h3>Catégorie</h3>
                    <select id="category" name="category">
                        <option value="">Toutes</option>
                        <option value="activites">Activités</option>
                        <option value="visites">Visites</option>
                        <option value="spectacles">Spectacles</option>
                        <option value="parcs">Parcs d'attractions</option>
                        <option value="restauration">Restauration</option>
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Destination</h3>
                    <div class="input-with-icon">
                        <input type="text" id="destination" name="destination" placeholder="Localisation">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Prix</h3>
                    <div class="price-input-container">
                        <div class="price-input-field">
                            <label for="price-min-input">Min €</label>
                            <input type="number" id="price-min-input" name="price_min_input" min="0" step="1" placeholder="0">
                        </div>
                        <span class="price-input-separator">-</span>
                        <div class="price-input-field">
                            <label for="price-max-input">Max €</label>
                            <input type="number" id="price-max-input" name="price_max_input" min="0" step="1" placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Date</h3>
                     <div class="input-with-icon">
                        <input type="date" id="date" name="date" placeholder="jj/mm/aaaa">
                        </div>
                </div>

                <div class="filter-group">
                    <h3>Notes Minimale</h3>
                    <div class="star-rating-filter" id="min-rating">
                        <i class="fas fa-star" data-value="1" title="1 étoile et plus"></i>
                        <i class="fas fa-star" data-value="2" title="2 étoiles et plus"></i>
                        <i class="fas fa-star" data-value="3" title="3 étoiles et plus"></i>
                        <i class="fas fa-star" data-value="4" title="4 étoiles et plus"></i>
                        <i class="fas fa-star" data-value="5" title="5 étoiles"></i>
                    </div>
                    <input type="hidden" id="min-rating-value" name="min_rating_value">
                </div>

                <div class="filter-group"> <h3>Notes Maximale</h3>
                    <div class="star-rating-filter" id="max-rating">
                        <i class="fas fa-star" data-value="1" title="1 étoile maximum"></i>
                        <i class="fas fa-star" data-value="2" title="2 étoiles maximum"></i>
                        <i class="fas fa-star" data-value="3" title="3 étoiles maximum"></i>
                        <i class="fas fa-star" data-value="4" title="4 étoiles maximum"></i>
                        <i class="fas fa-star" data-value="5" title="5 étoiles maximum"></i>
                    </div>
                    <input type="hidden" id="max-rating-value" name="max_rating_value">
                </div>


                <div class="filter-group">
                    <h3>Statut de l'offre</h3>
                    <div class="status-filter">
                        <label for="status-open">
                            <input type="radio" id="status-open" name="status" value="open" checked> Ouvert
                        </label>
                        <label for="status-closed">
                            <input type="radio" id="status-closed" name="status" value="closed"> Fermé
                        </label>
                    </div>
                </div>
            </aside>

            <section class="results-area">
                <div class="search-and-sort-controls">
                    <div class="search-bar-results">
                        <input type="text" placeholder="Rechercher">
                        <button type="submit" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="sort-options">
                        <button type="button" class="active" data-sort="note">Note</button>
                        <button type="button" data-sort="price_asc">Prix croissant</button>
                        <button type="button" data-sort="price_desc">Prix décroissant</button>
                    </div>
                </div>

                <div class="results-grid">
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/kayak.jpg" alt="Archipel de Bréhat en kayak">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Archipel de Bréhat en kayak</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a ...</p>
                            <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/louer_velo_famille.jpg" alt="Balade familiale à vélo">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Balade familiale à vélo</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a very short story.</p>
                            <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/centre-ville.jpg" alt="Découverte du centre-ville historique de Lannion">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Découverte du centre-ville historique de Lannion</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a ...</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                     <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/randonnee.jpg" alt="Randonnée en montagne">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Découverte du centre-ville historique de Lannion</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a ...</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                     <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/kayak.jpg" alt="Archipel de Bréhat en kayak">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Archipel de Bréhat en kayak</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a very short story. This text might be longer.</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                     <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/louer_velo_famille.jpg" alt="Balade familiale à vélo">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Balade familiale à vélo</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a ...</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/centre-ville.jpg" alt="Découverte du centre-ville historique de Lannion">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Balade familiale à vélo</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a very short story.</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                     <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/randonnee.jpg" alt="Randonnée en montagne">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Découverte du centre-ville historique de Lannion</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a ...</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
                     <div class="card">
                        <div class="card-image-wrapper">
                            <img src="images/kayak.jpg" alt="Archipel de Bréhat en kayak">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Archipel de Bréhat en kayak</h3>
                            <div class="star-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></div>
                            <p class="card-description">Body text for whatever you'd like to say. Add main takeaway points, quotes, anecdotes, or even a very short story.</p>
                             <a href="offre.php" class="card-more">Voir plus</a>
                        </div>
                    </div>
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
    <script src="script.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceMinNumberInput = document.getElementById('price-min-input');
            const priceMaxNumberInput = document.getElementById('price-max-input');

            if (priceMinNumberInput && priceMaxNumberInput) {
                const validateAndHandlePriceInputs = () => {
                    let minVal = parseFloat(priceMinNumberInput.value);
                    let maxVal = parseFloat(priceMaxNumberInput.value);

                    if (!isNaN(minVal) && !isNaN(maxVal) && minVal > maxVal) {
                        console.warn("Le prix minimum (" + minVal + ") ne peut pas être supérieur au prix maximum (" + maxVal + ").");
                    }
                };

                priceMinNumberInput.addEventListener('input', validateAndHandlePriceInputs);
                priceMaxNumberInput.addEventListener('input', validateAndHandlePriceInputs);
            }
            
            const couleurPrincipale = getComputedStyle(document.documentElement).getPropertyValue('--couleur-principale').trim();

            const setupStarRatingFilter = (filterId, valueInputId, isMaxRating = false) => {
                const ratingValueInput = document.getElementById(valueInputId); // Renommé pour clarté
                const starRatingFilter = document.getElementById(filterId);
                
                if (starRatingFilter && ratingValueInput) {
                    const stars = Array.from(starRatingFilter.querySelectorAll('.fa-star'));
                    
                    const setStarsVisual = (currentRating) => {
                        stars.forEach(s => {
                            const starValue = parseInt(s.dataset.value, 10);
                            let isSelected = false;
                            
                            // La logique de sélection visuelle est la même : on colore jusqu'à l'étoile cliquée.
                            // L'interprétation (min/max) se fait au moment du filtrage des données.
                            if (currentRating > 0) {
                                isSelected = starValue <= currentRating;
                            }

                            s.classList.toggle('selected', isSelected);
                            s.style.color = isSelected ? couleurPrincipale : '#e0e0e0'; 
                        });
                    };

                    starRatingFilter.addEventListener('click', function(e) {
                        if (e.target.classList.contains('fa-star') && e.target.dataset.value) {
                            const rating = e.target.dataset.value;
                            if (ratingValueInput.value === rating) { 
                                ratingValueInput.value = ""; 
                                setStarsVisual(0);
                            } else {
                                ratingValueInput.value = rating;
                                setStarsVisual(rating);
                            }
                        }
                    });

                    starRatingFilter.addEventListener('mouseover', function(e) {
                        if (e.target.classList.contains('fa-star') && e.target.dataset.value) {
                            const ratingHover = parseInt(e.target.dataset.value, 10);
                            stars.forEach(s => {
                                const starValue = parseInt(s.dataset.value, 10);
                                // La logique de survol colore aussi jusqu'à l'étoile survolée.
                                if (starValue <= ratingHover) {
                                    s.style.color = couleurPrincipale;
                                } else {
                                     s.style.color = '#e0e0e0';
                                }
                            });
                        }
                    });

                    starRatingFilter.addEventListener('mouseout', function() {
                        setStarsVisual(ratingValueInput.value || 0); 
                    });
                    setStarsVisual(ratingValueInput.value || 0); 
                }
            };

            setupStarRatingFilter('min-rating', 'min-rating-value', false); // false pour Note Minimale
            setupStarRatingFilter('max-rating', 'max-rating-value', true);  // true pour Note Maximale (même si la logique visuelle JS est la même ici)


            const sortButtons = document.querySelectorAll('.sort-options button');
            sortButtons.forEach(button => {
                button.addEventListener('click', function() {
                    sortButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>