<?php
// Démarrer la session et inclure la connexion à la BDD
session_start();
require_once __DIR__ . '/../../includes/db.php';

// 1. Récupérer l'ID de l'offre depuis l'URL
$offer_id = $_GET['id'] ?? null;

if (!$offer_id) {
    header("Location: recherche.php");
    exit;
}

// 2. Récupérer les détails de l'offre, l'entreprise, et l'adresse
try {
    $sql = "
        SELECT
            o.id, o.title, o.summary, o.description, o.price, o.main_photo, o.website,
            o.accessibility, o.phone, o.rating,
            cp.company_name as pro_company_name,
            a.street, a.postal_code, a.city
        FROM offres o
        JOIN comptes_pro cp ON o.pro_id = cp.id
        JOIN adresses a ON o.adresse_id = a.id
        WHERE o.id = :offer_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['offer_id' => $offer_id]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si aucune offre n'est trouvée, on redirige avant de continuer
    if (!$offer) {
        header("Location: recherche.php?error=notfound");
        exit;
    }

    // 3. Récupérer les photos supplémentaires
    $photo_stmt = $pdo->prepare("SELECT url FROM photos_offres WHERE offre_id = :offer_id AND url != :main_photo");
    $photo_stmt->execute(['offer_id' => $offer_id, 'main_photo' => $offer['main_photo']]);
    $photos = $photo_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Récupérer les avis pour l'offre (REQUÊTE CORRIGÉE)
    $reviews_sql = "
        SELECT
            r.title, r.comment, r.rating, r.published_at,
            m.alias as user_alias, m.firstname
        FROM avis r
        JOIN comptes_membre m ON r.membre_id = m.id
        WHERE r.offre_id = :offer_id
        ORDER BY r.published_at DESC
    ";
    $reviews_stmt = $pdo->prepare($reviews_sql);
    $reviews_stmt->execute(['offer_id' => $offer_id]);
    $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Fonction pour afficher les étoiles de notation
function display_stars($rating) {
    $html = '';
    $full_stars = floor($rating);
    $half_star = ceil($rating) - $full_stars;
    $empty_stars = 5 - $full_stars - $half_star;

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($half_star) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - <?php echo htmlspecialchars($offer['title']); ?></title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles spécifiques à la page offre */
        body {
            background-color: var(--couleur-blanche);
        }

        .main-content-offre {
            padding: 20px 0;
        }

        .breadcrumb-bar {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            margin-top: 5px;
            padding-left: 15px;
        }

        .breadcrumb-bar a {
            color: var(--couleur-texte);
            text-decoration: none;
            font-size: 1.2em;
            display: flex;
            align-items: center;
        }
        .breadcrumb-bar a:hover {
            color: var(--couleur-principale);
        }

        .offre-container {
            background-color: var(--couleur-blanche);
            padding: 0 15px;
            border-radius: 8px;
        }

        .offre-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .offre-purchase-details .title {
            font-size: 1.6em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 2px;
        }

        .offre-purchase-details .provider {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            margin-bottom: 8px;
        }

        .offre-purchase-details .tags span {
            background-color: #eef8f8;
            color: var(--couleur-principale);
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 0.75em;
            font-weight: var(--font-weight-medium);
            margin-right: 8px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .offre-favorite-btn {
            position: absolute; /* MODIFIED */
            top: 20px;          /* ADDED */
            right: 20px;         /* ADDED */
            background: none;
            border: none;
            color: var(--couleur-principale);
            font-size: 2em;
            cursor: pointer;
            padding: 0;
            z-index: 5; /* Optional: Added to ensure visibility */
        }
         .offre-favorite-btn:hover {
            color: var(--couleur-principale-hover);
        }
        .offre-favorite-btn .fas.fa-heart {
            display: none;
        }
        .offre-favorite-btn.active .far.fa-heart {
            display: none;
        }
        .offre-favorite-btn.active .fas.fa-heart {
            display: inline-block;
        }

        .offre-gallery-and-purchase {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .offre-gallery {
            flex: 0 0 60%;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }

        .gallery-image-container { 
            display: flex;
            height: 450px;
        }

        .gallery-image-container img { 
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .offre-purchase-details {
            position: relative; /* ADDED */
            flex: 0 0 calc(40% - 30px);
            background-color: var(--couleur-blanche);
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--couleur-bordure);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .offre-purchase-details .price {
            font-size: 2em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 5px;
        }

        .offre-purchase-details .summary-title {
            font-size: 1em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 8px;
        }
        .offre-purchase-details .summary-text {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .btn-acces-site {
            background-color: var(--couleur-principale);
            color: var(--couleur-blanche);
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: var(--font-weight-medium);
            transition: background-color 0.3s ease;
            display: block;
            margin-top: auto;
        }
        .btn-acces-site:hover {
            background-color: var(--couleur-principale-hover);
        }

        .offre-detailed-info {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .offre-description-text {
            flex: 0 0 60%;
        }
         .offre-description-text h2 {
            font-size: 1.3em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 10px;
        }
        .offre-description-text p {
            font-size: 0.95em;
            color: var(--couleur-texte-footer);
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .offre-additional-details {
            flex: 0 0 calc(40% - 30px);
            background-color: var(--couleur-blanche);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--couleur-bordure);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
         .offre-additional-details h3 {
            font-size: 1em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 12px;
         }
        .detail-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
        }
        .detail-item i {
            color: var(--couleur-texte);
            margin-right: 10px;
            font-size: 1.1em;
            width: 20px;
            text-align: center;
            margin-top: 1px;
        }
        .map-placeholder {
            width: 100%;
            height: 200px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            margin-top: 15px;
            overflow: hidden;
        }
         .map-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .offre-avis-section {
            margin-top: 40px;
            border-top: 1px solid var(--couleur-bordure);
            padding-top: 30px;
        }
        .offre-avis-section > h2 {
            font-size: 1.3em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 20px;
        }

        .avis-card {
            background-color: var(--couleur-blanche);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--couleur-bordure);
            display: flex;
            gap: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .avis-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 3px;
        }
        .avis-content { flex-grow: 1; }

        .avis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .avis-user-info .name {
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            font-size: 1em;
        }
        .avis-user-info .username-date {
             font-size: 0.85em;
             color: #6c757d;
             margin-top: 3px;
        }

        .avis-rating {
            display: flex;
            align-items: center;
            font-size: 0.9em;
        }
        .avis-rating .fas.fa-star,
        .avis-rating .fas.fa-star-half-alt {
            color: var(--couleur-principale);
            margin-left: 2px;
        }
        .avis-rating .far.fa-star {
            color: var(--couleur-bordure);
            margin-left: 2px;
        }

        .avis-comment p {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .avis-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        .btn-laisser-avis {
            background-color: var(--couleur-principale);
            color: var(--couleur-blanche);
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: var(--font-weight-medium);
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
            margin-bottom: 25px;
            border: none;
        }
        .btn-laisser-avis:hover {
            background-color: var(--couleur-principale-hover);
        }

        .avis-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100px;
            margin: 0 auto;
        }
        .avis-navigation button {
            background: none;
            border: none;
            color: var(--couleur-texte);
            font-size: 1.2em;
            cursor: pointer;
            padding: 5px;
        }
        .avis-navigation button:disabled { color: #ccc; cursor: not-allowed; }
        
        .gallery-image-container.cards-container {
            display: flex;
            height: 450px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            border-radius: inherit;
            scroll-snap-type: x mandatory;
        }
        .gallery-image-container.cards-container::-webkit-scrollbar {
            display: none;
        }
        .gallery-image-container.cards-container img {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            scroll-snap-align: start;
        }
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--couleur-bordure);
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s ease, color 0.2s ease, opacity 0.3s ease, visibility 0.3s ease;
        }
        .carousel-arrow.prev-arrow { left: 15px; }
        .carousel-arrow.next-arrow { right: 15px; }
        
        @media (max-width: 992px) {
            .offre-gallery-and-purchase, .offre-detailed-info { flex-direction: column; }
            .offre-gallery, .offre-purchase-details,
            .offre-description-text, .offre-additional-details {
                flex-basis: auto;
            }
        }
        @media (max-width: 768px) {
            .avis-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <main class="main-content-offre">
        <div class="container">
            <div class="breadcrumb-bar">
                <a href="recherche.php"><i class="fas fa-arrow-left"></i>Retour à la recherche</a>
            </div>

            <div class="offre-container">
                <div class="offre-gallery-and-purchase">
                    <div class="offre-gallery cards-container-wrapper" id="offreImageCarouselWrapper">
                        <div class="gallery-image-container cards-container">
                            <img src="../../<?php echo htmlspecialchars($offer['main_photo']); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>">
                            <?php foreach ($photos as $photo): ?>
                                <img src="../../<?php echo htmlspecialchars($photo['url']); ?>" alt="Photo de <?php echo htmlspecialchars($offer['title']); ?>">
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-arrow prev-arrow" onclick="scrollOffreCarousel('offreImageCarouselWrapper', -1)" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
                        <button class="carousel-arrow next-arrow" onclick="scrollOffreCarousel('offreImageCarouselWrapper', 1)" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="offre-purchase-details">
                        <button class="offre-favorite-btn" aria-label="Ajouter aux favoris"><i class="far fa-heart"></i><i class="fas fa-heart"></i></button>
                        <h1 class="title"><?php echo htmlspecialchars($offer['title']); ?></h1>
                        <div class="avis-rating">
                            <?php echo display_stars($offer['rating']); ?>
                        </div>
                        <p class="provider">Proposé par <?php echo htmlspecialchars($offer['pro_company_name']); ?></p>
                        <p class="price"><?php echo htmlspecialchars(number_format($offer['price'], 2, ',', ' ')); ?>€</p>
                        <h3 class="summary-title">Résumé</h3>
                        <p class="summary-text"><?php echo htmlspecialchars($offer['summary']); ?></p>
                        <a href="<?php echo htmlspecialchars($offer['website']); ?>" class="btn-acces-site" target="_blank">Accéder au site web</a>
                    </div>
                </div>

                <div class="offre-detailed-info">
                    <div class="offre-description-text">
                        <h2>Description</h2>
                        <p><?php echo nl2br(htmlspecialchars($offer['description'])); ?></p>
                    </div>
                    <div class="offre-additional-details">
                        <h3>Conditions d'accessibilité</h3>
                        <p><?php echo htmlspecialchars($offer['accessibility']); ?></p>
                        <h3>Adresse</h3>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($offer['street'] . ', ' . $offer['postal_code'] . ' ' . $offer['city']); ?></span>
                        </div>
                        <h3>Contact</h3>
                        <?php if (!empty($offer['phone'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>Téléphone : <?php echo htmlspecialchars($offer['phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="map-placeholder">
                            <img src="images/map.png" alt="Carte de localisation">
                        </div>
                    </div>
                </div>

                <div class="offre-avis-section">
                    <h2>Avis</h2>
                    <div class="avis-list">
                        <?php if (empty($reviews)): ?>
                            <p>Aucun avis pour le moment.</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="avis-card">
                                    <div class="avis-avatar">
                                        <img src="images/bertrand.jpg" alt="Avatar">
                                    </div>
                                    <div class="avis-content">
                                        <div class="avis-header">
                                            <div class="avis-user-info">
                                                <span class="name"><?php echo htmlspecialchars($review['title']); ?></span>
                                                <div class="username-date">
                                                    <?php echo htmlspecialchars($review['user_alias']); ?> - 
                                                    <?php echo date('d/m/Y', $review['published_at']); ?>
                                                </div>
                                            </div>
                                            <div class="avis-rating">
                                                <?php echo display_stars($review['rating']); ?>
                                            </div>
                                        </div>
                                        <div class="avis-comment">
                                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="avis-footer">
                        <a href="creation-compte.php" class="btn-laisser-avis">Laisser un avis</a>
                        <div class="avis-navigation">
                            <button class="prev-avis" aria-label="Avis précédents"><i class="fas fa-chevron-left"></i></button>
                            <button class="next-avis" aria-label="Avis suivants"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                 </div>
        </div>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <a href="../index.html"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="footer-logo"></a>
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
    <script src="script.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const favoriteButton = document.querySelector('.offre-favorite-btn');
            if (favoriteButton) {
                favoriteButton.addEventListener('click', function() { this.classList.toggle('active'); });
            }

            const avisListContainer = document.querySelector('.avis-list');
            const allAvisCards = avisListContainer ? Array.from(avisListContainer.querySelectorAll('.avis-card')) : [];
            const prevAvisBtn = document.querySelector('.prev-avis');
            const nextAvisBtn = document.querySelector('.next-avis');
            
            let currentAvisPage = 1;
            const avisPerPage = 3; / Nombre d'avis à afficher par page
            let totalAvisPages = 0;

            function displayCurrentAvisPage() {
                if (!avisListContainer || allAvisCards.length === 0) return;
                const startIndex = (currentAvisPage - 1) * avisPerPage;
                const endIndex = startIndex + avisPerPage;

                allAvisCards.forEach((card, index) => {
                    if (index >= startIndex && index < endIndex) {
                        card.style.display = 'flex'; / Ou 'block' selon le style initial des cartes
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            function updateAvisNavigation() {
                if (!prevAvisBtn || !nextAvisBtn) return;
                totalAvisPages = Math.ceil(allAvisCards.length / avisPerPage);
                prevAvisBtn.disabled = currentAvisPage === 1;
                nextAvisBtn.disabled = currentAvisPage === totalAvisPages || allAvisCards.length === 0;
                
                if (allAvisCards.length > 0) {
                    displayCurrentAvisPage();
                } else { / S'il n'y a aucun avis
                    if(avisListContainer) avisListContainer.innerHTML = "<p>Aucun avis pour le moment.</p>";
                }
                / console.log(`Page d'avis actuelle: ${currentAvisPage}, Total pages: ${totalAvisPages}`);
            }
            
            if (allAvisCards.length > 0) { / S'il y a des avis, initialiser la pagination
                if (prevAvisBtn) {
                    prevAvisBtn.addEventListener('click', () => {
                        if (currentAvisPage > 1) {
                            currentAvisPage--;
                            updateAvisNavigation();
                        }
                    });
                }

                if (nextAvisBtn) {
                    nextAvisBtn.addEventListener('click', () => {
                        if (currentAvisPage < totalAvisPages) {
                            currentAvisPage++;
                            updateAvisNavigation();
                        }
                    });
                }
                updateAvisNavigation(); / Appel initial pour afficher la première page et définir l'état des boutons
            } else if (prevAvisBtn && nextAvisBtn) { / S'il n'y a pas d'avis, désactiver les boutons
                 updateAvisNavigation(); / Appel pour gérer le cas où il n'y a pas d'avis
            }

            const offreCarouselWrapper = document.getElementById('offreImageCarouselWrapper');
            if (offreCarouselWrapper) {
                updateOffreCarouselArrowsVisibility(offreCarouselWrapper);
                const imageContainer = offreCarouselWrapper.querySelector('.gallery-image-container.cards-container');
                if (imageContainer) {
                    let scrollEndTimer;
                    imageContainer.addEventListener('scroll', () => {
                        clearTimeout(scrollEndTimer);
                        scrollEndTimer = setTimeout(() => updateOffreCarouselArrowsVisibility(offreCarouselWrapper), 100); 
                    });
                }
            }
        });

        function scrollOffreCarousel(carouselWrapperId, direction) {
            const wrapper = document.getElementById(carouselWrapperId);
            if (!wrapper) return;
            const container = wrapper.querySelector('.gallery-image-container.cards-container');
            if (!container) return;
            container.scrollBy({ left: direction * wrapper.clientWidth, behavior: 'smooth' });
        }

        function updateOffreCarouselArrowsVisibility(wrapperElement) {
            const container = wrapperElement.querySelector('.gallery-image-container.cards-container');
            const prevArrow = wrapperElement.querySelector('.carousel-arrow.prev-arrow');
            const nextArrow = wrapperElement.querySelector('.carousel-arrow.next-arrow');
            if (!container || !prevArrow || !nextArrow) return;
            
            const atStart = container.scrollLeft < 10;
            const atEnd = container.scrollWidth - container.scrollLeft - container.clientWidth < 10;
            const hasNoScroll = container.scrollWidth <= container.clientWidth;

            prevArrow.style.display = (atStart || hasNoScroll) ? 'none' : 'flex';
            nextArrow.style.display = (atEnd || hasNoScroll) ? 'none' : 'flex';
        }

        window.addEventListener('resize', () => {
            const offreCarouselWrapper = document.getElementById('offreImageCarouselWrapper');
            if (offreCarouselWrapper) updateOffreCarouselArrowsVisibility(offreCarouselWrapper);
        });
    </script>
</body>
</html>