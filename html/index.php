<?php
// INCLUSION DE LA CONNEXION À LA BDD ET AUTRES NÉCESSITÉS
require_once __DIR__ . '/../includes/db.php';

// FONCTION POUR GÉNÉRER LES ÉTOILES DE NOTATION
function generateStarRating($rating)
{
    if ($rating === null) {
        $rating = 0;
    }
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;

    $starsHtml = '';
    $starsHtml .= str_repeat('<i class="fas fa-star"></i> ', $fullStars);
    if ($halfStar) {
        $starsHtml .= '<i class="fas fa-star-half-alt"></i> ';
    }
    $starsHtml .= str_repeat('<i class="far fa-star"></i> ', $emptyStars);

    echo trim($starsHtml);
}

// RÉCUPÉRATION DES OFFRES "A LA UNE"
// Hypothèse : une offre est "à la une" si elle a une souscription active.
// La durée dans la table `souscriptions` est en mois.
try {
    $stmt_alaune = $pdo->query("
        SELECT o.id, o.title, o.summary, o.main_photo, o.rating 
        FROM offres AS o
        JOIN souscriptions AS s ON o.id = s.offre_id
        WHERE s.launch_date <= CURDATE() 
          AND DATE_ADD(s.launch_date, INTERVAL s.duration MONTH) >= CURDATE()
        ORDER BY o.rating DESC
        LIMIT 6
    ");
    $alaune_offres = $stmt_alaune->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $alaune_offres = [];
    // error_log("Erreur de requête pour les offres A la une : " . $e->getMessage());
}


// RÉCUPÉRATION DES OFFRES "NOUVEAUTÉS"
try {
    $stmt_nouveautes = $pdo->query("SELECT id, title, summary, main_photo, rating FROM offres ORDER BY created_at DESC LIMIT 6");
    $nouveautes = $stmt_nouveautes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $nouveautes = [];
    // error_log("Erreur de requête pour les nouveautés : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Accueil</title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="FO/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles spécifiques à la page d'accueil */
        .hero-section {
            background-size: cover;
            background-position: center;
            padding: 160px var(--espacement-double);
            text-align: center;
            color: var(--couleur-blanche);
            position: relative;
            overflow: hidden;
        }

        #hero-video-background {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }


        .hero-section h1 {
            font-size: 3em;
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--espacement-triple);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .search-bar-container {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 700px;
            margin: 0 auto;
            background-color: var(--couleur-blanche);
            height: 50px;
            border-radius: 25px;
            padding: 0 var(--espacement-standard);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .search-bar-container input[type="text"] {
            flex-grow: 1;
            padding: var(--espacement-standard) var(--espacement-moyen);
            border: none;
            outline: none;
            font-size: 1.1em;
            background-color: transparent;
            color: var(--couleur-texte);
            height: 100%;
            box-sizing: border-box;
        }

        .search-bar-container button {
            background-color: transparent;
            color: var(--couleur-principale);
            border: none;
            padding: var(--espacement-standard);
            cursor: pointer;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            box-sizing: border-box;
        }

        .search-bar-container button:hover {
            color: var(--couleur-principale-hover);
        }

        .news-section {
            padding: var(--espacement-triple) var(--espacement-double);
            background-color: var(--couleur-fond-body);
        }

        .news-section h2 {
            font-size: 1.8em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: var(--espacement-double);
            text-align: left;
        }

        .cards-container-wrapper {
            position: relative;
        }

        .cards-container {
            display: flex;
            gap: var(--espacement-double);
            overflow-x: auto;
            padding-bottom: var(--espacement-standard);
            padding-top: var(--espacement-standard);
            align-items: stretch;
        }

        /* Masquer la scrollbar pour Chrome, Safari et Opera */
        .cards-container::-webkit-scrollbar {
            display: none;
        }

        /* Masquer la scrollbar pour IE, Edge et Firefox */
        .cards-container {
            -ms-overflow-style: none;
            /* IE et Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .card {
            background-color: var(--couleur-blanche);
            border: var(--bordure-standard-interface);
            border-radius: 12px;
            padding: var(--espacement-moyen);
            min-width: 280px;
            max-width: 300px;
            flex: 0 0 auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Updated card image structure styles */
        .card .card-image-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            margin-bottom: var(--espacement-moyen);
        }

        .card .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            display: block;
        }

        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            /* Changed from left to right */
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--couleur-texte);
            /* Default color for empty heart */
            font-size: 1rem;
            /* Adjust icon size */
            z-index: 5;
            padding: 0;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .favorite-btn:hover {
            background-color: var(--couleur-blanche);
            color: var(--couleur-principale);
            transform: scale(1.1);
        }

        .favorite-btn .fas.fa-heart {
            /* Filled heart icon */
            display: none;
            /* Hidden by default */
        }

        .favorite-btn.active .far.fa-heart {
            /* Empty heart icon */
            display: none;
            /* Hide empty heart when active */
        }

        .favorite-btn.active .fas.fa-heart {
            /* Filled heart icon */
            display: inline-block;
            /* Show filled heart when active */
            color: var(--couleur-principale);
            /* Or red e.g. #E53935 */
        }

        /* End of new favorite button styles */


        .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: calc(var(--espacement-standard) / 2);
        }

        .star-rating {
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-principale);
            /* Couleur par défaut pour étoiles pleines */
        }

        .star-rating .fas.fa-star {
            /* La couleur est héritée de .star-rating ou peut être surchargée ici si besoin */
        }

        .star-rating .fas.fa-star-half-alt {
            /* La couleur est héritée de .star-rating */
        }

        .star-rating .far.fa-star {
            /* Étoiles vides */
            color: var(--couleur-bordure);
        }

        .card-description {
            font-size: var(--font-size-corps-petit);
            color: var(--couleur-texte-footer);
            line-height: 1.5;
            margin-bottom: var(--espacement-moyen);
            flex-grow: 1;
            /* Pour que la description prenne l'espace disponible */
        }

        .card-more {
            font-size: var(--font-size-corps-petit);
            color: var(--couleur-principale);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            align-self: flex-start;
            /* Aligner en bas à gauche de la card-content */
            margin-top: auto;
            /* Push to the bottom */
        }

        .card-more:hover {
            text-decoration: underline;
            color: var(--couleur-principale-hover);
        }

        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            border: var(--bordure-standard-interface);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--couleur-principale);
            font-size: 1.5em;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .carousel-arrow:hover {
            background-color: var(--couleur-blanche);
            color: var(--couleur-principale-hover);
        }

        .prev-arrow {
            left: -20px;
            /* Ajuster pour qu'elle soit visible et cliquable */
        }

        .next-arrow {
            right: -20px;
            /* Ajuster */
        }

        /* Styling for "À la une" cards */
        .card.card-a-la-une {
            border: 1px solid #ffe390;
            /* Bordure jaune sur tous les côtés */
            position: relative;
            /* Nécessaire pour le positionnement absolu de l'étoile */
        }

        .card-a-la-une .highlight-star-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            /* Positionné à droite */
            color: #FFC107;
            font-size: 1em;
            z-index: 5;
        }

        /* Styling for Category and Destination cards */
        .category-card,
        .destination-card {
            background-color: var(--couleur-blanche);
            border: var(--bordure-standard-interface);
            border-radius: 12px;
            min-width: 250px;
            /* Augmenté */
            max-width: 260px;
            /* Augmenté */
            flex: 0 0 auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            text-align: center;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .category-card:hover,
        .destination-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .category-card img,
        .destination-card img {
            width: 100%;
            height: 150px;
            /* Augmenté */
            object-fit: cover;
        }

        .category-card h3,
        .destination-card h3 {
            font-size: 1em;
            font-weight: var(--font-weight-medium);
            color: var(--couleur-texte);
            margin: 0;
            padding: var(--espacement-moyen) var(--espacement-standard);
            background-color: var(--couleur-blanche);
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .clickable-card {
            cursor: pointer;
        }

        /* Media Queries pour la responsivité de la page d'accueil */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.2em;
            }

            .hero-section {
                padding: 60px var(--espacement-standard);
            }

            .news-section {
                padding: var(--espacement-double) var(--espacement-standard);
            }

            .search-bar-container {
                height: 45px;
                border-radius: 22.5px;
            }

            .search-bar-container input[type="text"] {
                font-size: 1em;
            }

            .search-bar-container button {
                font-size: 1.2em;
            }

            .card {
                /* S'applique aussi aux .card-a-la-une */
                min-width: 250px;
            }

            .card .card-image-wrapper {
                /* Ensure image height is responsive if needed */
                /* height: 160px; /* Example adjustment for smaller cards */
            }

            .category-card,
            .destination-card {
                min-width: 220px;
                /* Ajustement pour mobile aussi */
                max-width: 230px;
            }

            .category-card img,
            .destination-card img {
                height: 130px;
                /* Peut-être réduire un peu la hauteur sur mobile */
            }

            .carousel-arrow {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-section h1 {
                font-size: 1.8em;
            }

            .search-bar-container {
                height: 40px;
                border-radius: 20px;
            }

            .search-bar-container input[type="text"] {
                font-size: 0.9em;
            }

            .search-bar-container button {
                font-size: 1.1em;
            }

            .card .card-image-wrapper {
                /* Example for very small screens */
                /* height: 140px; */
            }

            .category-card,
            .destination-card {
                min-width: 180px;
                /* Ajustement plus petit pour très petits écrans */
                max-width: 190px;
            }

            .category-card img,
            .destination-card img {
                height: 110px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'FO/header.php'; ?>
    <main style="padding: 0px;">
        <section class="hero-section">
            <video autoplay muted loop playsinline id="hero-video-background">
                <source src="FO/images/Paca.mp4" type="video/mp4">
                Votre navigateur ne supporte pas les vidéos HTML5.
            </video>
            <div class="hero-content">
                <h1>Découvrez la région PACA</h1>
                <form action="FO/recherche.php" method="GET" class="search-bar-container">
                    <input type="text" name="q" placeholder="Rechercher une activité, une destination..." required>
                    <button type="submit" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </section>

        <section class="news-section container">
            <h2>A la une</h2>
            <div class="cards-container-wrapper" id="alaUneCarouselWrapper">
                <div class="cards-container">
                    <?php if (empty($alaune_offres)): ?>
                        <p>Aucune offre à la une pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($alaune_offres as $offre): ?>
                            <div class="card card-a-la-une clickable-card" data-href="FO/offre.php?id=<?= htmlspecialchars($offre['id']) ?>">
                                <div class="card-image-wrapper">
                                    <img src="<?= htmlspecialchars($offre['main_photo']) ?>" alt="<?= htmlspecialchars($offre['title']) ?>">
                                    <button class="favorite-btn" aria-label="Ajouter aux favoris">
                                        <i class="far fa-heart"></i><i class="fas fa-heart"></i>
                                    </button>
                                </div>
                                <span class="highlight-star-icon"><i class="fas fa-star"></i></span>
                                <div class="card-content">
                                    <h3 class="card-title"><?= htmlspecialchars($offre['title']) ?></h3>
                                    <div class="star-rating">
                                        <?php generateStarRating($offre['rating']); ?>
                                    </div>
                                    <p class="card-description"><?= htmlspecialchars($offre['summary']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="carousel-arrow prev-arrow" onclick="scrollSpecificCarousel('alaUneCarouselWrapper', -1)"><i class="fas fa-chevron-left"></i></div>
                <div class="carousel-arrow next-arrow" onclick="scrollSpecificCarousel('alaUneCarouselWrapper', 1)"><i class="fas fa-chevron-right"></i></div>
            </div>
        </section>

        <section class="news-section container">
            <h2>Nouveautés</h2>
            <div class="cards-container-wrapper" id="nouveautesCarouselWrapper">
                <div class="cards-container">
                    <?php if (empty($nouveautes)): ?>
                        <p>Aucune nouveauté à afficher pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($nouveautes as $offre): ?>
                            <div class="card clickable-card" data-href="FO/offre.php?id=<?= htmlspecialchars($offre['id']) ?>">
                                <div class="card-image-wrapper">
                                    <img src="<?= htmlspecialchars($offre['main_photo']) ?>" alt="<?= htmlspecialchars($offre['title']) ?>">
                                    <button class="favorite-btn" aria-label="Ajouter aux favoris">
                                        <i class="far fa-heart"></i><i class="fas fa-heart"></i>
                                    </button>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?= htmlspecialchars($offre['title']) ?></h3>
                                    <div class="star-rating">
                                        <?php generateStarRating($offre['rating']); ?>
                                    </div>
                                    <p class="card-description"><?= htmlspecialchars($offre['summary']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="carousel-arrow prev-arrow" onclick="scrollSpecificCarousel('nouveautesCarouselWrapper', -1)"><i class="fas fa-chevron-left"></i></div>
                <div class="carousel-arrow next-arrow" onclick="scrollSpecificCarousel('nouveautesCarouselWrapper', 1)"><i class="fas fa-chevron-right"></i></div>
            </div>
        </section>

        <section class="news-section container">
            <h2>Catégories</h2>
            <div class="cards-container-wrapper" id="categoriesCarouselWrapper">
                <div class="cards-container">
                    <div class="category-card clickable-card" data-category-type="activite">
                        <img src="FO/images/activite.jpg" alt="Activités">
                        <h3>Activités</h3>
                    </div>
                    <div class="category-card clickable-card" data-category-type="visite">
                        <img src="FO/images/visite.jpg" alt="Visites">
                        <h3>Visites</h3>
                    </div>
                    <div class="category-card clickable-card" data-category-type="spectacle">
                        <img src="FO/images/spectacle.jpg" alt="Spectacles">
                        <h3>Spectacles</h3>
                    </div>
                    <div class="category-card clickable-card" data-category-type="parc_attractions">
                        <img src="FO/images/parcs-d-attraction.jpg" alt="Parcs d'attractions">
                        <h3>Parcs d'attractions</h3>
                    </div>
                    <div class="category-card clickable-card" data-category-type="restauration">
                        <img src="FO/images/restaurant.jpg" alt="Restauration">
                        <h3>Restauration</h3>
                    </div>
                </div>
                <div class="carousel-arrow prev-arrow" onclick="scrollSpecificCarousel('categoriesCarouselWrapper', -1)"><i class="fas fa-chevron-left"></i></div>
                <div class="carousel-arrow next-arrow" onclick="scrollSpecificCarousel('categoriesCarouselWrapper', 1)"><i class="fas fa-chevron-right"></i></div>
            </div>
        </section>

        <section class="news-section container">
            <h2>Destinations</h2>
            <div class="cards-container-wrapper" id="destinationsCarouselWrapper">
                <div class="cards-container">
                    <div class="destination-card clickable-card" data-destination="Avignon">
                        <img src="FO/images/avignon.png" alt="Avignon">
                        <h3>Avignon</h3>
                    </div>
                    <div class="destination-card clickable-card" data-destination="Marseille">
                        <img src="FO/images/marseille.jpg" alt="Marseille">
                        <h3>Marseille</h3>
                    </div>
                    <div class="destination-card clickable-card" data-destination="Nice">
                        <img src="FO/images/nice.jpg" alt="Nice">
                        <h3>Nice</h3>
                    </div>
                    <div class="destination-card clickable-card" data-destination="Cannes">
                        <img src="FO/images/cannes.jpg" alt="Cannes">
                        <h3>Cannes</h3>
                    </div>
                    <div class="destination-card clickable-card" data-destination="Toulon">
                        <img src="FO/images/toulon.jpg" alt="Toulon">
                        <h3>Toulon</h3>
                    </div>
                </div>
                <div class="carousel-arrow prev-arrow" onclick="scrollSpecificCarousel('destinationsCarouselWrapper', -1)"><i class="fas fa-chevron-left"></i></div>
                <div class="carousel-arrow next-arrow" onclick="scrollSpecificCarousel('destinationsCarouselWrapper', 1)"><i class="fas fa-chevron-right"></i></div>
            </div>
        </section>

    </main>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <a href="index.php"><img src="FO/images/Logowithoutbg.png" alt="Logo PACT" class="footer-logo"></a>
                <div class="social-icons">
                    <a href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Professionnel</h3>
                <ul>
                    <li><a href="BO/index.php">Comment poster une annonce</a></li>
                    <li><a href="BO/creation-compte.php">Je crée mon compte pro</a></li>
                    <li><a href="BO/connexion-compte.php">Je me connecte en tant que pro</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Découvrir</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="FO/recherche.php">Recherche</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Ressources</h3>
                <ul>
                    <li><a href="FO/conditions-generales-d-utilisation.php">Conditions générales d'utilisation</a></li>
                    <li><a href="FO/contact-du-responsable-du-site.php">Contact du responsable du site</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PACT. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="FO/script.js" defer></script>
    <script>
        function scrollSpecificCarousel(carouselWrapperId, direction) {
            const wrapper = document.getElementById(carouselWrapperId);
            if (!wrapper) {
                console.error('Carousel wrapper not found:', carouselWrapperId);
                return;
            }
            const container = wrapper.querySelector('.cards-container');
            if (!container) {
                console.error('Cards container not found in wrapper:', carouselWrapperId);
                return;
            }

            let card = container.querySelector('.card');
            if (!card) {
                card = container.querySelector('.category-card, .destination-card');
            }

            if (!card) {
                console.warn('No cards found in container for width calculation:', container);
                return;
            }

            const cardWidth = card.offsetWidth;
            const gapStyle = getComputedStyle(container).gap;
            const gap = (gapStyle && gapStyle !== 'normal' && !isNaN(parseFloat(gapStyle))) ? parseFloat(gapStyle) : 20;
            const scrollAmount = cardWidth + gap;

            container.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });

            const prevArrow = wrapper.querySelector('.prev-arrow');
            const nextArrow = wrapper.querySelector('.next-arrow');

            setTimeout(() => {
                if (prevArrow) {
                    prevArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
                }
                if (nextArrow) {
                    const isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - (gap / 2) - 2;
                    nextArrow.style.display = isAtEnd ? 'none' : 'flex';

                    if (container.scrollWidth <= container.clientWidth) {
                        nextArrow.style.display = 'none';
                        if (prevArrow) prevArrow.style.display = 'none';
                    }
                }
            }, 350);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const carouselWrappers = document.querySelectorAll('.cards-container-wrapper');
            const isMobile = window.matchMedia("(max-width: 768px)").matches;

            carouselWrappers.forEach(wrapper => {
                const container = wrapper.querySelector('.cards-container');
                if (!container) return;

                const prevArrow = wrapper.querySelector('.prev-arrow');
                const nextArrow = wrapper.querySelector('.next-arrow');

                const updateArrowVisibility = () => {
                    if (isMobile) {
                        if (prevArrow) prevArrow.style.display = 'none';
                        if (nextArrow) nextArrow.style.display = 'none';
                        return;
                    }

                    if (container.scrollWidth > container.clientWidth) {
                        if (prevArrow) {
                            prevArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
                        }
                        if (nextArrow) {
                            const isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 2;
                            nextArrow.style.display = isAtEnd ? 'none' : 'flex';
                        }
                    } else {
                        if (prevArrow) prevArrow.style.display = 'none';
                        if (nextArrow) nextArrow.style.display = 'none';
                    }
                };
                updateArrowVisibility();
            });

            if (!isMobile) {
                window.addEventListener('resize', () => {
                    carouselWrappers.forEach(wrapper => {
                        const container = wrapper.querySelector('.cards-container');
                        if (!container) return;
                        const prevArrow = wrapper.querySelector('.prev-arrow');
                        const nextArrow = wrapper.querySelector('.next-arrow');
                        if (container.scrollWidth > container.clientWidth) {
                            if (prevArrow) prevArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
                            if (nextArrow) nextArrow.style.display = (container.scrollLeft + container.clientWidth >= container.scrollWidth - 2) ? 'none' : 'flex';
                        } else {
                            if (prevArrow) prevArrow.style.display = 'none';
                            if (nextArrow) nextArrow.style.display = 'none';
                        }
                    });
                });
            }

            // Add click listener for favorite buttons (basic toggle example)
            const favoriteButtons = document.querySelectorAll('.favorite-btn');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    button.classList.toggle('active');
                    // Here you would typically also send a request to a server
                    // to save the favorite state for the user.
                });
            });

            // MODIFIED SCRIPT FOR REDIRECTION on all clickable cards
            document.querySelectorAll('.clickable-card').forEach(card => {
                card.addEventListener('click', function(event) {
                    // Prevent redirection if the favorite button (or any button/link inside the card) was clicked
                    if (event.target.closest('.favorite-btn')) {
                        return;
                    }

                    const href = this.dataset.href;
                    const categoryType = this.dataset.categoryType;
                    const destination = this.dataset.destination;

                    let redirectUrl;

                    if (href) {
                        redirectUrl = href;
                    } else if (categoryType) {
                        redirectUrl = 'FO/recherche.php?category_type=' + encodeURIComponent(categoryType);
                    } else if (destination) {
                        redirectUrl = 'FO/recherche.php?destination=' + encodeURIComponent(destination);
                    }

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            });
        });
    </script>

</body>

</html>