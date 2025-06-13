<?php
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Détail de l'offre</title><link rel="icon" href="images/Logo2withoutbg.png">
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
            background-color: var(--couleur-secondaire);
            color: var(--couleur-principale);
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 0.75em;
            font-weight: var(--font-weight-medium);
            margin-right: 8px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .offre-modify-btn {
            position: absolute; /* MODIFIED */
            top: 20px;          /* ADDED */
            right: 20px;         /* ADDED */
            background: none;
            border: none;
            color: var(--couleur-principale);
            font-size: 2em;
            cursor: pointer;
            padding: 0;
            /* margin-top: 5px; /* REMOVED/COMMENTED OUT */
            z-index: 5; /* Optional: Added to ensure visibility */
        }
         .offre-modify-btn:hover {
            color: var(--couleur-principale-hover);
        }
        .offre-modify-btn .fas.fa-pen-to-square {
            display: none;
        }
        .offre-modify-btn.active .far.fa-pen-to-square {
            display: none;
        }
        .offre-modify-btn.active .fas.fa-pen-to-square {
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

        .gallery-image-container { /* Cette règle peut être fusionnée ou supprimée si redéfinie plus bas pour le carrousel */
            display: flex;
            height: 450px;
        }

        .gallery-image-container img { /* Cette règle peut être fusionnée ou supprimée si redéfinie plus bas pour le carrousel */
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .gallery-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0,0,0,0.4);
            color: white;
            border: none;
            padding: 10px 12px;
            cursor: pointer;
            z-index: 10;
            font-size: 1.3em;
            border-radius: 50%;
        }
        .gallery-arrow.prev { left: 15px; }
        .gallery-arrow.next { right: 15px; }


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
        .offre-purchase-details .price-info {
            font-size: 0.8em;
            color: var(--couleur-texte-footer);
            margin-bottom: 15px;
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

        .offre-purchase-details .title { /* Répétition, déjà définie plus haut */
            font-size: 1.6em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 2px;
        }

        .offre-purchase-details .provider { /* Répétition, déjà définie plus haut */
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            margin-bottom: 8px;
        }

        .offre-purchase-details .tags {
            margin-bottom: 10px;
        }

        .offre-purchase-details .tags span { /* Répétition, déjà définie plus haut */
            background-color: var(--couleur-secondaire);
            color: var(--couleur-principale);
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 0.75em;
            font-weight: var(--font-weight-medium);
            margin-right: 8px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .offre-purchase-details .price { /* Répétition, déjà définie plus haut */
            font-size: 2em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 5px;
            margin-top: 0;
        }

        .offre-purchase-details .price-info { /* Répétition, déjà définie plus haut */
            font-size: 0.8em;
            color: var(--couleur-texte-footer);
            margin-bottom: 15px;
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
        .detail-item span {
            flex: 1;
            line-height: 1.5;
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
        .avis-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 25px;
            border: 1px solid var(--couleur-bordure);
            border-radius: 8px;
            overflow: hidden;
            width: fit-content;
        }
        .avis-tabs button {
            padding: 10px 20px;
            border: none;
            background-color: var(--couleur-blanche);
            cursor: pointer;
            font-size: 0.9em;
            font-weight: var(--font-weight-medium);
            color: var(--couleur-texte-footer);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
         .avis-tabs button:not(:last-child) {
            border-right: 1px solid var(--couleur-bordure);
        }

        .avis-tabs button.active, .avis-tabs button:hover {
            background-color: var(--couleur-principale);
            color: var(--couleur-blanche);
        }

        .avis-card {
            background-color: var(--couleur-blanche);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--couleur-bordure);
            display: flex; /* Modifié en JS pour pagination, mais flex par défaut */
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
        .avis-header-icons {
            color: #999;
            font-size: 1em;
        }
         .avis-header-icons i { cursor: pointer; }
         .avis-header-icons i:hover { color: var(--couleur-texte); }


        .avis-comment p {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .avis-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .avis-actions button {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.9em;
            padding: 0;
            display: flex;
            align-items: center;
        }
         .avis-actions button i {
            margin-right: 6px;
            font-size: 1.1em;
        }
        .avis-actions button:hover { color: var(--couleur-principale); }
        .avis-actions .report-action {
            margin-left: auto;
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
        .avis-navigation button:hover { color: var(--couleur-principale); }
        .avis-navigation button:disabled { color: #ccc; cursor: not-allowed; }

        /* Styles pour le conteneur EXTERNE du carrousel d'images */
        .offre-gallery.cards-container-wrapper { /* S'assure que c'est bien .offre-gallery qui a ces styles */
            flex: 0 0 60%; 
            position: relative;
            overflow: hidden; 
            border-radius: 8px;
        }

        /* MODIFICATIONS POUR LE CAROUSEL CI-DESSOUS */
        /* Styles pour le conteneur INTERNE qui défile (contient les images) */
        .gallery-image-container.cards-container {
            display: flex;
            height: 450px; /* Conservez votre hauteur définie */
            overflow-x: auto; /* Permet le défilement horizontal */
            -webkit-overflow-scrolling: touch; /* Améliore le défilement sur iOS */
            scrollbar-width: none; /* Masque la barre de défilement standard (Firefox) */
            -ms-overflow-style: none;  /* Masque la barre de défilement standard (IE/Edge) */
            border-radius: inherit; /* Hérite du border-radius du parent */

            /* NOUVEAU : Ajout du scroll snapping */
            scroll-snap-type: x mandatory; /* Force l'alignement sur l'axe X */
        }
        .gallery-image-container.cards-container::-webkit-scrollbar {
            display: none; /* Masque la barre de défilement standard (Chrome/Safari/Opera) */
        }

        /* Styles pour CHAQUE image dans le carrousel */
        .gallery-image-container.cards-container img {
            flex: 0 0 100%; /* Chaque image occupe 100% de la largeur du conteneur visible */
            width: 100%;    /* S'assure que l'image remplit l'espace alloué par flex-basis */
            height: 100%;   /* L'image prend toute la hauteur de son parent */
            object-fit: cover; /* L'image couvre l'espace, quitte à être rognée, sans distorsion */

            /* NOUVEAU : Alignement pour le scroll snapping */
            scroll-snap-align: start; /* Aligne le début de l'image avec le début du conteneur de défilement.
                                         Vous pourriez aussi utiliser 'center' si vous préférez centrer l'image. */
        }
        /* FIN DES MODIFICATIONS POUR LE CAROUSEL */


        /* Styles pour les flèches de navigation (identiques à index.php pour l'apparence) */
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--couleur-bordure);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex; /* Garder flex pour centrer l'icône */
            align-items: center;
            justify-content: center;
            color: var(--couleur-principale);
            font-size: 1.5em;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s ease, color 0.2s ease, opacity 0.3s ease, visibility 0.3s ease; /* Ajout de transitions pour affichage/masquage */
        }
        .carousel-arrow:hover {
            background-color: var(--couleur-blanche);
            color: var(--couleur-principale-hover);
        }
        .carousel-arrow.prev-arrow {
            left: 15px;
        }
        .carousel-arrow.next-arrow {
            right: 15px;
        }
        
        /* Styles responsives pour la galerie */
        @media (max-width: 992px) {
            .offre-gallery.cards-container-wrapper {
                flex-basis: 100%; 
                height: 350px;
            }
            /* S'assurer que le conteneur d'images interne s'adapte aussi si nécessaire */
            .gallery-image-container.cards-container {
                height: 350px; /* Adapter la hauteur ici aussi */
                 /* width: 100%; /* Inutile si flex-basis:100% sur le parent suffit */
            }
        }
        @media (max-width: 768px) {
             .offre-gallery.cards-container-wrapper {
                height: 280px;
            }
            .gallery-image-container.cards-container {
                height: 280px; /* Adapter la hauteur ici aussi */
            }
        }

        @media (max-width: 992px) {
            .offre-gallery-and-purchase, .offre-detailed-info {
                flex-direction: column;
            }
            .offre-gallery, .offre-purchase-details, /* .offre-gallery est maintenant .offre-gallery.cards-container-wrapper */
            .offre-description-text, .offre-additional-details {
                flex-basis: auto; /* flex-basis: 100% est déjà géré pour .offre-gallery.cards-container-wrapper */
            }
            /* .offre-gallery { height: 350px; } /* Redondant si .offre-gallery.cards-container-wrapper a la hauteur */
            .breadcrumb-bar, .offre-container { padding-left: 0; padding-right: 0;}
        }
        @media (max-width: 768px) {
            .offre-header { flex-direction: column; align-items: flex-start;}
             .offre-modify-btn {
                font-size: 1.8em; 
            }
            /* .gallery-image-container { height: 280px; } /* Redondant si .gallery-image-container.cards-container a la hauteur */
            .offre-purchase-details .price { font-size: 1.8em; }
            .offre-container { padding: 0 10px; }

            .avis-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .avis-avatar {
                margin-bottom: 10px;
            }
            .avis-header {
                flex-direction: row;
                align-items: center;
                width: 100%;
            }
            .avis-user-info {
                flex-grow: 1;
            }
            .avis-rating {
                margin-top: 0;
                margin-left: 10px;
            }
             .avis-header-icons {
                margin-left: auto;
            }
            .avis-tabs { width: 100%; }
            .avis-tabs button { flex-grow: 1; text-align: center; }
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

<header>
    <div class="container header-container">
        <div class="header-left">
            <a href="index.php"><img src="images/Logowithoutbgorange.png" alt="Logo" class="logo"></a>
            <span class="pro-text">Professionnel</span>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Accueil</a></li>
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

    <main class="main-content-offre">
        <div class="container">
            <div class="breadcrumb-bar">
                <a href="../BO/recherche.php"><i class="fas fa-arrow-left"></i></a>
                </div>

            <div class="offre-container">
                <div class="offre-gallery-and-purchase">
                    <div class="offre-gallery cards-container-wrapper" id="offreImageCarouselWrapper">
                        <div class="gallery-image-container cards-container">
                            <img src="images/kayak.jpg" alt="Kayak dans l'archipel de Bréhat - Vue 1">
                            <img src="images/louer_velo_famille.jpg" alt="Vélo en famille - Vue 2"> 
                            <img src="images/centre-ville.jpg" alt="Centre ville - Vue 3">
                        </div>
                        <button class="carousel-arrow prev-arrow" onclick="scrollOffreCarousel('offreImageCarouselWrapper', -1)" aria-label="Précédent">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-arrow next-arrow" onclick="scrollOffreCarousel('offreImageCarouselWrapper', 1)" aria-label="Suivant">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="offre-purchase-details">
                        <button class="offre-modify-btn" aria-label="Ajouter aux favoris">
                            <i class="far fa-pen-to-square"></i>
                            <i class="fas fa-pen-to-square"></i>
                        </button>
                        <h1 class="title">Archipel de Bréhat en kayak</h1>
                        <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                        <p class="provider">Planète Kayak</p>
                        <div class="tags">
                            <span>Ouvert</span>
                            <span>Sport</span>
                            <span>Famille</span>
                            <span>Plein air</span>
                        </div>
                        <p class="price">€50</p>
                        <h3 class="summary-title">Résumé</h3>
                        <p class="summary-text">Montrer que l'on peut réaliser localement de belles balades à vélo, en empruntant de petites routes tranquilles et sans trop de montées.</p>
                        <a href="#" class="btn-acces-site">Accéder au site web</a>
                    </div>
                </div>

                <div class="offre-detailed-info">
                    <div class="offre-description-text">
                        <h2>Archipel de Bréhat en kayak</h2>
                        <p>Les sorties sont volontairement limitées entre 15 km et 20 km pour permettre à un large public familial de se joindre à nous. À partir de 6 ou 7 ans, un enfant à l'aise sur son vélo, peut en général parcourir une telle distance sans problème : le rythme est suffisamment lent (adapté aux plus faibles), avec des pauses, et le fait d'être en groupe est en général un bon stimulant pour les enfants ... et les plus grands ! Les plus jeunes peuvent aussi participer en charrette sur un siège vélo ou bien avec une barre de traction.</p>
                    </div>
                    <div class="offre-additional-details">
                        <h3>Horaires d'ouverture</h3>
                        <div class="detail-item">
                           <span>Lundi au jeudi : 9h30 - 20h<br>Vendredi et Samedi : 11h - 19h</span>
                        </div>

                        <h3>Adresse</h3>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>3 Allée des Soupirs, 22300 Lannion</span>
                        </div>

                        <h3>Contact</h3>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email : MagieDesArbres@gmail.com</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>Téléphone : +33700000000</span>
                        </div>
                        <div class="map-placeholder">
                            <img src="images/map.png" alt="Carte de localisation">
                        </div>
                    </div>
                </div>

                <div class="offre-avis-section">
                    <h2>Avis avec photos</h2>
                    <div class="avis-tabs">
                        <button data-tab="note">Note</button> <button data-tab="recommandation" class="active">Recommandation</button>
                        </div>

                    <div class="avis-list">
                        <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/bertrand.jpg" alt="Avatar Bertrand">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Parfait</span>
                                        <div class="username-date">Bertrand - 26 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Parfait du début à la fin ! Équipe au top et nature sublime. Je recommande vivement.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                    </div>
                            </div>
                        </div>

                        <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/benoit.jpg" alt="Avatar Benoît">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Merci</span>
                                        <div class="username-date">Benoît - 24 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                    </div>
                                    <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Belle balade, mais l'attente avant de commencer était longue. Encadrement un peu froid, dommage.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                </div>
                            </div>
                        </div>
                         <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/yannick.jpg" alt="Avatar Yannick">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Génial !</span>
                                        <div class="username-date">Yannick - 18 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                    </div>
                                     <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Super moment sur l'eau, paysages magnifiques. Juste un peu court à mon goût.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/avatar4.jpg" alt="Avatar Claire">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Inoubliable</span>
                                        <div class="username-date">Claire - 15 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Une expérience à vivre absolument. Le guide était passionnant et les paysages à couper le souffle.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                    </div>
                            </div>
                        </div>
                        <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/avatar5.jpg" alt="Avatar Marc">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Bonne activité</span>
                                        <div class="username-date">Marc - 12 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                                    </div>
                                    <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>C'était sympa, mais le matériel mériterait un petit coup de neuf. L'endroit reste très beau.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                </div>
                            </div>
                        </div>
                         <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/avatar6.jpg" alt="Avatar Sophie">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Très relaxant</span>
                                        <div class="username-date">Sophie - 10 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                    </div>
                                     <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Idéal pour se déconnecter. Le calme de l'archipel est vraiment appréciable.</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="avis-card">
                            <div class="avis-avatar">
                                <img src="images/avatar7.jpg" alt="Avatar Julien">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">A refaire !</span>
                                        <div class="username-date">Julien - 05 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <div class="avis-header-icons">
                                        <i class="fas fa-flag" title="Signaler l'avis"></i>
                                    </div>
                                </div>
                                <div class="avis-comment">
                                    <p>Nous avons passé un excellent moment en famille. Les enfants ont adoré et nous aussi !</p>
                                </div>
                                <div class="avis-actions">
                                    <button><i class="fas fa-thumbs-up"></i></button>
                                    <button><i class="fas fa-thumbs-down"></i></button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="avis-footer">
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
            / Favorite button toggle
            const favoriteButton = document.querySelector('.offre-modify-btn');
            if (favoriteButton) {
                favoriteButton.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const isPressed = this.classList.contains('active');
                    this.setAttribute('aria-pressed', isPressed);
                });
                 const isInitiallyPressed = favoriteButton.classList.contains('active');
                 favoriteButton.setAttribute('aria-pressed', isInitiallyPressed);
            }

            / Avis tabs
            const avisTabsContainer = document.querySelector('.avis-tabs');
            if (avisTabsContainer) {
                const tabs = avisTabsContainer.querySelectorAll('button');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        tabs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        / console.log(`Onglet sélectionné: ${this.dataset.tab}`);
                    });
                });
            }

            / Avis pagination
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
            / Fin Avis pagination


            / Initialisation des flèches pour le carrousel d'images de l'offre
            const offreCarouselWrapper = document.getElementById('offreImageCarouselWrapper');
            if (offreCarouselWrapper) {
                const imageContainer = offreCarouselWrapper.querySelector('.gallery-image-container.cards-container');

                updateOffreCarouselArrowsVisibility(offreCarouselWrapper);

                if (imageContainer) {
                    let scrollEndTimer;
                    imageContainer.addEventListener('scroll', function() {
                        clearTimeout(scrollEndTimer);
                        scrollEndTimer = setTimeout(function() {
                            updateOffreCarouselArrowsVisibility(offreCarouselWrapper);
                        }, 100); 
                    });
                }
            }
        });

        function scrollOffreCarousel(carouselWrapperId, direction) {
            const wrapper = document.getElementById(carouselWrapperId);
            if (!wrapper) {
                / console.error('Carousel wrapper not found:', carouselWrapperId);
                return;
            }
            const container = wrapper.querySelector('.gallery-image-container.cards-container');
            if (!container) {
                / console.error('Image container not found in wrapper:', carouselWrapperId);
                return;
            }

            const scrollAmount = wrapper.clientWidth; 
            container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }

        function updateOffreCarouselArrowsVisibility(wrapperElement) {
            const container = wrapperElement.querySelector('.gallery-image-container.cards-container');
            if (!container) {
                return;
            }

            const prevArrow = wrapperElement.querySelector('.carousel-arrow.prev-arrow');
            const nextArrow = wrapperElement.querySelector('.carousel-arrow.next-arrow');

            if (!prevArrow || !nextArrow) {
                return;
            }

            const scrollLeft = container.scrollLeft;
            const scrollWidth = container.scrollWidth;
            const clientWidth = container.clientWidth;
            const tolerance = 1.5; 

            if (scrollLeft <= tolerance) {
                prevArrow.style.display = 'none';
            } else {
                prevArrow.style.display = 'flex'; 
            }

            if (scrollLeft + clientWidth >= scrollWidth - tolerance) {
                nextArrow.style.display = 'none';
            } else {
                nextArrow.style.display = 'flex';
            }

            if (scrollWidth <= clientWidth + tolerance) {
                prevArrow.style.display = 'none';
                nextArrow.style.display = 'none';
            }
        }

        window.addEventListener('resize', () => {
            const offreCarouselWrapper = document.getElementById('offreImageCarouselWrapper');
            if (offreCarouselWrapper) {
                updateOffreCarouselArrowsVisibility(offreCarouselWrapper);
            }
        });
    </script>
</body>
</html>