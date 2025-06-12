<?php
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Accueil</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles spécifiques à la page d'accueil BO */

        /* Hero Section BO */
        .hero-section-bo {
            background-color: var(--couleur-secondaire); /* Couleur de fond douce pour le BO */
            padding: 80px var(--espacement-double); /* Moins de padding que le hero FO */
            text-align: center;
            color: var(--couleur-texte);
            position: relative;
            border-bottom: 1px solid var(--couleur-bordure);
        }

        .hero-content-bo h1 {
            font-size: 2.5em; /* Taille de titre adaptée */
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-principale); /* Titre avec la couleur principale du BO */
        }

        .hero-content-bo p {
            font-size: 1.1em;
            margin-bottom: var(--espacement-triple);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .bo-quick-stats {
            display: flex;
            justify-content: center;
            gap: var(--espacement-double);
            flex-wrap: wrap; /* Pour la responsivité */
        }

        .stat-card {
            background-color: var(--couleur-blanche);
            padding: var(--espacement-double);
            border-radius: var(--border-radius-standard);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            min-width: 220px;
            text-align: center;
            border: 1px solid var(--couleur-bordure);
        }

        .stat-card h3 {
            font-size: 1.1em;
            font-weight: var(--font-weight-medium);
            color: var(--couleur-texte-footer);
            margin-top: 0;
            margin-bottom: var(--espacement-standard);
        }

        .stat-card .stat-number {
            font-size: 2.2em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-principale);
            margin-bottom: var(--espacement-standard);
        }
        .stat-card .stat-link {
            font-size: 0.9em;
            color: var(--couleur-principale);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }
        .stat-card .stat-link:hover {
            text-decoration: underline;
            color: var(--couleur-principale-hover);
        }


        /* Section Actions Rapides BO */
        .bo-actions-section {
            padding: var(--espacement-triple) var(--espacement-double);
            background-color: var(--couleur-fond-body);
        }

        .bo-actions-section h2 {
            font-size: 1.8em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: var(--espacement-double);
            text-align: left; /* Ou center si préféré */
        }

        .action-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Grille responsive */
            gap: var(--espacement-double);
        }

        .action-card {
            background-color: var(--couleur-blanche);
            border: var(--bordure-standard-interface);
            border-radius: var(--border-radius-standard);
            padding: var(--espacement-double);
            text-align: center;
            text-decoration: none;
            color: var(--couleur-texte);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .action-card i {
            font-size: 2.5em; /* Taille des icônes */
            color: var(--couleur-principale);
            margin-bottom: var(--espacement-moyen);
        }

        .action-card h3 {
            font-size: 1.2em;
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-texte);
        }

        .action-card p {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            line-height: 1.5;
        }

        /* Section Mes Offres BO */
        .news-section-bo {
            padding: var(--espacement-triple) var(--espacement-double);
        }

        .news-section-bo h2 {
            font-size: 1.8em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: var(--espacement-double);
            text-align: left;
        }

        /* Styles pour les cartes d'offres dans le BO */
        .card-bo { /* Classe spécifique pour les cartes BO pour éviter conflits */
            background-color: var(--couleur-blanche);
            border: var(--bordure-standard-interface);
            border-radius: 12px;
            padding: var(--espacement-moyen);
            min-width: 280px;
            max-width: 320px; /* Un peu plus large si besoin */
            flex: 0 0 auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card-bo:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .card-bo .card-image-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            margin-bottom: var(--espacement-moyen);
        }

        .card-bo .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px; /* Coins arrondis pour l'image */
            display: block;
        }

        .card-bo .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-bo .card-title {
            font-size: 1.2em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: calc(var(--espacement-standard) / 2);
        }

        .card-status-bo {
            font-size: 0.85em;
            color: var(--couleur-texte-footer);
            margin-bottom: var(--espacement-standard);
        }
        .card-status-bo .status-active {
            color: #28a745; /* Vert pour actif */
            font-weight: var(--font-weight-medium);
        }
        .card-status-bo .status-inactive {
            color: #dc3545; /* Rouge pour inactif */
            font-weight: var(--font-weight-medium);
        }
        .card-views-bo {
            font-size: 0.9em;
            color: var(--couleur-texte);
            margin-bottom: var(--espacement-moyen);
        }

        .card-actions-bo {
            display: flex;
            gap: var(--espacement-standard);
            margin-top: auto; /* Pousse les actions en bas de la carte */
        }

        .btn-bo-card {
            flex-grow: 1;
            padding: calc(var(--espacement-standard)*0.8) var(--espacement-moyen);
            border-radius: var(--border-radius-bouton);
            font-weight: var(--font-weight-medium);
            text-align: center;
            text-decoration: none;
            font-size: 0.85em;
            transition: var(--transition-bouton-standard);
        }

        .btn-bo-view {
            background-color: var(--couleur-secondaire);
            color: var(--couleur-principale);
            border: 1px solid var(--couleur-secondaire);
        }
        .btn-bo-view:hover {
            background-color: var(--couleur-secondaire-hover-bg);
            color: var(--couleur-principale-hover);
            border-color: var(--couleur-secondaire-hover-border);
        }

        .btn-bo-edit {
            background-color: var(--couleur-principale);
            color: var(--couleur-blanche);
            border: 1px solid var(--couleur-principale);
        }
        .btn-bo-edit:hover {
            background-color: var(--couleur-principale-hover);
            border-color: var(--couleur-principale-hover);
        }


        /* Styles généraux pour les carrousels (réutilisés du FO) */
        .cards-container-wrapper {
            position: relative;
        }

        .cards-container {
            display: flex;
            gap: var(--espacement-double);
            overflow-x: auto;
            padding-bottom: var(--espacement-standard); /* Pour l'ombre de la scrollbar si visible */
            padding-top: var(--espacement-standard); /* Espace au-dessus des cartes */
            align-items: stretch; /* Cartes de même hauteur */
        }

        /* Masquer la scrollbar */
        .cards-container::-webkit-scrollbar { display: none; }
        .cards-container { -ms-overflow-style: none; scrollbar-width: none; }

        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%); /* Centre verticalement */
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .carousel-arrow:hover {
            background-color: var(--couleur-blanche);
            color: var(--couleur-principale-hover);
        }
        .prev-arrow { left: -20px; }
        .next-arrow { right: -20px; }


        /* Media Queries pour la responsivité de la page d'accueil BO */
        @media (max-width: 992px) {
            .hero-content-bo h1 { font-size: 2em; }
            .hero-content-bo p { font-size: 1em; margin-bottom: var(--espacement-double); }
            .bo-quick-stats { gap: var(--espacement-moyen); }
            .stat-card { min-width: 200px; padding: var(--espacement-moyen); }
            .news-section-bo, .bo-actions-section { padding: var(--espacement-double) var(--espacement-standard); }
            .action-cards-container { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
        }

        @media (max-width: 768px) {
            .hero-section-bo { padding: 60px var(--espacement-standard); }
            .hero-content-bo h1 { font-size: 1.8em; }
            .stat-card { width: 100%; margin-bottom: var(--espacement-moyen); } /* Stats en colonne */
            .stat-card:last-child { margin-bottom: 0; }
            .bo-quick-stats { flex-direction: column; align-items: center; }
            
            .carousel-arrow { display: none; } /* Masquer les flèches sur mobile si le scroll est privilégié */
            .action-cards-container { grid-template-columns: 1fr; } /* Actions en une seule colonne */
            .card-bo { min-width: 250px; } /* Ajustement taille carte */
        }

        @media (max-width: 480px) {
            .hero-content-bo h1 { font-size: 1.6em; }
            .stat-card .stat-number { font-size: 1.8em; }
            .bo-actions-section h2, .news-section-bo h2 { font-size: 1.5em; }
            .action-card h3 { font-size: 1.1em; }
            .card-bo .card-title { font-size: 1.1em; }
        }
      
    .success-notification-modal-style {
        position: fixed;
        top: 20px;
        left: 20px;
        background-color: #d4edda; 
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 20px 30px;
        border-radius: var(--border-radius-standard, 8px);
        z-index: 1050; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        font-family: var(--police-principale, 'Poppins', sans-serif); 
        font-size: 1em;
        display: none; 
        max-width: 90%; 
        width: auto; 
        min-width: 300px; 
    }

    .success-notification-modal-style .close-modal-btn {
        position: absolute;
        top: 8px;
        right: 12px;
        font-size: 1.5em; /* Taille de la croix */
        font-weight: bold;
        color: #155724;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        line-height: 1;
    }

    .success-notification-modal-style p {
        margin: 0;
        padding-right: 20px; 
    }

   
    @keyframes fadeInModal {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .success-notification-modal-style.show {
        display: block;
        animation: fadeInModal 0.3s ease-out;
    }
    /* --- STYLES POUR LA NOTIFICATION PROFIL --- */
    .profile-link-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .notification-bubble {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
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
    </style>
</head>
<body>
    <div id="success-notification-modal" class="success-notification-modal-style">
        <button class="close-modal-btn" aria-label="Fermer">&times;</button>
        <p id="success-modal-message"></p>
    </div>
    <header>
    <div class="container header-container">
        <div class="header-left">
            <a href="index.php"><img src="images/Logowithoutbgorange.png" alt="Logo" class="logo"></a>
            <span class="pro-text">Professionnel</span>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php" class="active">Accueil</a></li>
                <li><a href="recherche.php">Mes Offres</a></li>
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <div class="profile-link-container">
                <a href="profil.php" class="btn btn-secondary">Mon profil</a>
                <?php if (isset($unanswered_reviews_count) && $unanswered_reviews_count > 0): ?>
                    <span class="notification-bubble"><?php echo $unanswered_reviews_count; ?></span>
                <?php endif; ?>
            </div>
            <a href="/deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

<main style="padding: 0px;">
    <section class="hero-section-bo">
        <div class="hero-content-bo">
            <h1>Bienvenue sur votre Espace Professionnel</h1>
            <p>Gérez vos offres, suivez vos performances et interagissez avec vos clients.</p>
            <div class="bo-quick-stats">
                <div class="stat-card">
                    <h3>Offres Totales</h3>
                    <p class="stat-number">3</p> 
                    <a href="recherche.php" class="stat-link">Voir mes offres <i class="fas fa-arrow-right fa-xs"></i></a>
                </div>
                <div class="stat-card">
                    <h3>Offres Actives</h3>
                    <p class="stat-number">2</p> 
                    <a href="recherche.php" class="stat-link">Voir mes offres <i class="fas fa-arrow-right fa-xs"></i></a>
                </div>
                <div class="stat-card">
                    <h3>Avis sur mes offres</h3>
                    <p class="stat-number">12</p>
                    <a href="recherche.php" class="stat-link">Voir les offres<i class="fas fa-arrow-right fa-xs"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="bo-actions-section container">
        <h2>Actions Rapides</h2>
        <div class="action-cards-container">
            <a href="publier-une-offre.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Publier une Nouvelle Offre</h3>
                <p>Créez et mettez en ligne une nouvelle activité, visite, ou autre prestation.</p>
            </a>
            <a href="recherche.php" class="action-card">
                <i class="fas fa-edit"></i>
                <h3>Gérer mes Offres</h3>
                <p>Modifiez, supprimez ou mettez à jour vos offres existantes.</p>
            </a>
            <a href="profil.php" class="action-card">
                <i class="fas fa-user-cog"></i>
                <h3>Modifier mon Profil</h3>
                <p>Mettez à jour les informations de votre établissement ou de contact.</p>
            </a>
            <a href="../index.html" class="action-card">
                <i class="fas fa-chart-line"></i>
                <h3>Accéder au site pour les visiteurs</h3>
                <p>Regardez les offres proposer par les autres professionel.</p>
            </a>
        </div>
    </section>

    <section class="news-section-bo container" id="mesOffresCarouselWrapper">
        <h2>Vos Offres Publiées</h2>
        <div class="cards-container-wrapper">
            <div class="cards-container">
                <div class="card card-bo">
                    <div class="card-image-wrapper">
                        <img src="images/kayak.jpg" alt="Mon offre Kayak"> </div>
                    <div class="card-content">
                        <h3 class="card-title">Archipel de Bréhat en kayak</h3>
                        <p class="card-status-bo">Statut : <span class="status-active">Actif</span></p>
                        <div class="card-actions-bo">
                            <a href="offre.php?id=1" class="btn-bo-card btn-bo-view">Voir</a>
                            <a href="publier-une-offre.php?edit_id=1" class="btn-bo-card btn-bo-edit">Modifier</a>
                        </div>
                    </div>
                </div>
                <div class="card card-bo">
                    <div class="card-image-wrapper">
                        <img src="images/randonnee.jpg" alt="Ma Randonnée Guidée"> </div>
                    <div class="card-content">
                        <h3 class="card-title">Randonnée Guidée Montagnes</h3>
                        <p class="card-status-bo">Statut : <span class="status-inactive">Inactif</span></p>
                        <div class="card-actions-bo">
                            <a href="offre.php?id=2" class="btn-bo-card btn-bo-view">Voir</a>
                            <a href="publier-une-offre.php?edit_id=2" class="btn-bo-card btn-bo-edit">Modifier</a>
                        </div>
                    </div>
                </div>
                 <div class="card card-bo">
                    <div class="card-image-wrapper">
                        <img src="images/cuisine.jpg" alt="Cours de Cuisine Locale"> </div>
                    <div class="card-content">
                        <h3 class="card-title">Cours de Cuisine Locale</h3>
                        <p class="card-status-bo">Statut : <span class="status-active">Actif</span></p>
                        <div class="card-actions-bo">
                            <a href="offre.php?id=3" class="btn-bo-card btn-bo-view">Voir</a>
                            <a href="publier-une-offre.php?edit_id=3" class="btn-bo-card btn-bo-edit">Modifier</a>
                        </div>
                    </div>
                </div>
                </div>
            <div class="carousel-arrow prev-arrow" onclick="scrollSpecificCarousel('mesOffresCarouselWrapper', -1)"><i class="fas fa-chevron-left"></i></div>
            <div class="carousel-arrow next-arrow" onclick="scrollSpecificCarousel('mesOffresCarouselWrapper', 1)"><i class="fas fa-chevron-right"></i></div>
        </div>
    </section>

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
    / Script pour le carrousel (similaire à celui du FO)
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

        let card = container.querySelector('.card'); / Adaptez si la classe de carte est différente (ex: .card-bo)
        if (!card) { 
            / Fallback si la classe .card n'est pas trouvée directement, essayez .card-bo
            card = container.querySelector('.card-bo');
        }
        
        if (!card) { 
            console.warn('No cards found in container for width calculation:', container);
            return;
        }

        const cardWidth = card.offsetWidth;
        const gapStyle = getComputedStyle(container).gap;
        const gap = (gapStyle && gapStyle !== 'normal' && !isNaN(parseFloat(gapStyle))) ? parseFloat(gapStyle) : 20; / Valeur de gap par défaut si non trouvée
        const scrollAmount = cardWidth + gap;

        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });

        / Mise à jour de la visibilité des flèches (Optionnel, mais recommandé pour une bonne UX)
        const prevArrow = wrapper.querySelector('.prev-arrow');
        const nextArrow = wrapper.querySelector('.next-arrow');

        / Attendre un peu que le scroll se termine pour mettre à jour les flèches
        setTimeout(() => {
            if (prevArrow) {
                prevArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
            }
            if (nextArrow) {
                / Ajustement pour la condition de fin de scroll
                const isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - (gap / 2) - 2; / Tolérance pour la fin
                nextArrow.style.display = isAtEnd ? 'none' : 'flex';

                / Si le contenu est plus petit que le conteneur, masquer les deux flèches
                if (container.scrollWidth <= container.clientWidth) {
                    nextArrow.style.display = 'none';
                     if (prevArrow) prevArrow.style.display = 'none';
                }
            }
        }, 350); / Délai pour la mise à jour (ajuster si besoin)
    }

    document.addEventListener('DOMContentLoaded', () => {
        const carouselWrappers = document.querySelectorAll('.cards-container-wrapper');
        const isMobile = window.matchMedia("(max-width: 768px)").matches;

        carouselWrappers.forEach(wrapper => {
            const container = wrapper.querySelector('.cards-container');
            if (!container) return;

            const prevArrow = wrapper.querySelector('.prev-arrow');
            const nextArrow = wrapper.querySelector('.next-arrow');
            
            / Fonction pour mettre à jour la visibilité des flèches
            const updateArrowVisibility = () => {
                if (isMobile) { / Si mobile, masquer les flèches (carrousel par swipe)
                     if(prevArrow) prevArrow.style.display = 'none';
                     if(nextArrow) nextArrow.style.display = 'none';
                     return; / Sortir de la fonction si mobile
                }

                / Logique pour desktop
                if (container.scrollWidth > container.clientWidth) { / Si le contenu dépasse
                    if (prevArrow) {
                        prevArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
                    }
                    if (nextArrow) {
                        / Ajustement pour la condition de fin de scroll
                        const isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 2; / Petite tolérance
                        nextArrow.style.display = isAtEnd ? 'none' : 'flex';
                    }
                } else { / Si le contenu ne dépasse pas, masquer les deux
                    if (prevArrow) prevArrow.style.display = 'none';
                    if (nextArrow) nextArrow.style.display = 'none';
                }
            };
            
            / Appel initial pour définir l'état des flèches
            updateArrowVisibility(); 
            
            / Ajouter un écouteur pour le redimensionnement pour réévaluer la visibilité des flèches
            / Uniquement si ce n'est PAS mobile, car sur mobile elles sont toujours cachées
            if (!isMobile) { 
                window.addEventListener('resize', updateArrowVisibility);
                / Optionnel: écouter l'événement de scroll sur le conteneur pour mettre à jour dynamiquement
                / container.addEventListener('scroll', updateArrowVisibility); 
            }
        });
    });
    </script>
    <script>document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const publishStatus = params.get('publish_status');
        const notificationMessage = params.get('notification_message');

        const modal = document.getElementById('success-notification-modal');
        const messageElement = document.getElementById('success-modal-message');
        const closeModalBtn = modal ? modal.querySelector('.close-modal-btn') : null;

        function showModal(message) {
            if (modal && messageElement) {
                messageElement.textContent = decodeURIComponent(message);
                modal.classList.add('show');

                setTimeout(() => {
                    hideModal();
                }, 7000); 
            }
        }

        function hideModal() {
            if (modal && modal.classList.contains('show')) {
                modal.classList.remove('show');
                
                if (window.history.replaceState) {
                    const cleanUrl = window.location.protocol + "/" + window.location.host + window.location.pathname;
                    window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                }
            }
        }

        if (publishStatus === 'success' && notificationMessage) {
            showModal(notificationMessage);
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', hideModal);
        }

        window.addEventListener('click', function(event) {
            if (event.target === modal && modal.classList.contains('show')) {
                hideModal();
            }
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('show')) {
                hideModal();
            }
        });
    });
</script>
</body>
</html>