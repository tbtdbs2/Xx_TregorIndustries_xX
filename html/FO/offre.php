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
            background-color: var(--couleur-blanche); /* Mockup has white background */
        }

        .main-content-offre {
            padding: 20px 0;
        }

        .breadcrumb-bar {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            margin-top: 5px;
            padding-left: 15px; /* Align with content padding */
        }

        .breadcrumb-bar a {
            color: var(--couleur-texte); /* Back arrow is black in mockup */
            text-decoration: none;
            font-size: 1.2em; /* Slightly larger arrow */
            display: flex;
            align-items: center;
        }
        .breadcrumb-bar a:hover {
            color: var(--couleur-principale);
        }
        /* Removed icon from breadcrumb text, just arrow */

        .offre-container {
            background-color: var(--couleur-blanche);
            padding: 0 15px; /* Padding applied to inner elements or globally to container */
            border-radius: 8px;
            /* Removed box-shadow from main container to match mockup */
        }

        .offre-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .offre-header-main .title {
            font-size: 1.6em; /* Adjusted to mockup */
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 2px; /* Reduced margin */
        }

        .offre-header-main .provider {
            font-size: 0.9em; /* Adjusted to mockup */
            color: var(--couleur-texte-footer);
            margin-bottom: 8px; /* Space before tags */
        }

        .offre-header-main .tags span {
            background-color: #eef8f8; /* Light blueish background for tags */
            color: var(--couleur-principale);
            padding: 6px 14px; /* Slightly adjusted padding */
            border-radius: 16px;
            font-size: 0.75em; /* Smaller font for tags */
            font-weight: var(--font-weight-medium);
            margin-right: 8px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .offre-favorite-btn {
            background: none;
            border: none;
            color: var(--couleur-principale); /* Heart is main color */
            font-size: 2em; /* Larger heart icon */
            cursor: pointer;
            padding: 0; /* Remove padding to align better */
            margin-top: 5px; /* Align with title */
        }
         .offre-favorite-btn:hover {
            color: var(--couleur-principale-hover);
        }
        .offre-favorite-btn .fas.fa-heart { /* Filled heart */
            display: none;
        }
        .offre-favorite-btn.active .far.fa-heart { /* Empty heart */
            display: none;
        }
        .offre-favorite-btn.active .fas.fa-heart { /* Filled heart */
            display: inline-block;
        }


        .offre-gallery-and-purchase {
            display: flex;
            gap: 30px; /* Increased gap */
            margin-bottom: 30px;
        }

        .offre-gallery {
            flex: 0 0 60%; /* Gallery takes more space */
            position: relative;
            overflow: hidden;
            border-radius: 8px; /* Rounded corners for gallery */
        }

        .gallery-image-container {
            display: flex;
            height: 450px; /* Increased height for the main image */
        }

        .gallery-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .gallery-arrow { /* Mockup shows arrows */
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
            flex: 0 0 calc(40% - 30px); /* Remaining space minus gap */
            background-color: var(--couleur-blanche); /* White background */
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--couleur-bordure); /* Border as in mockup */
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Subtle shadow */
        }

        .offre-purchase-details .price {
            font-size: 2em; /* Adjusted to mockup */
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 5px; /* Reduced margin */
        }
        .offre-purchase-details .price-info { /* Not present in mockup, can be removed or kept */
            font-size: 0.8em;
            color: var(--couleur-texte-footer);
            margin-bottom: 15px;
        }

        .offre-purchase-details .summary-title {
            font-size: 1em; /* "Résumé" */
            font-weight: var(--font-weight-semibold); /* Bolder */
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
            border-radius: 8px; /* Matching mockup button radius */
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
            gap: 30px; /* Increased gap */
            margin-bottom: 30px;
        }

        .offre-description-text {
            flex: 0 0 60%; /* Description takes more space */
        }
         .offre-description-text h2 { /* Title "Archipel de Bréhat en kayak" */
            font-size: 1.3em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 10px;
            /* Removed border from here */
        }
        .offre-description-text p {
            font-size: 0.95em;
            color: var(--couleur-texte-footer);
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .offre-additional-details { /* Contact/Address/Hours block */
            flex: 0 0 calc(40% - 30px); /* Remaining space */
            background-color: var(--couleur-blanche);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--couleur-bordure);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
         .offre-additional-details h3 { /* "Horaires d'ouverture", "Adresse", "Contact" */
            font-size: 1em; /* Adjusted size */
            font-weight: var(--font-weight-semibold); /* Bolder */
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
        .detail-item i { /* Icons for details */
            color: var(--couleur-texte); /* Icons are black in mockup */
            margin-right: 10px;
            font-size: 1.1em;
            width: 20px;
            text-align: center;
            margin-top: 1px; /* Fine-tune alignment */
        }
        .detail-item span {
            flex: 1;
            line-height: 1.5;
        }
        .map-placeholder {
            width: 100%;
            height: 200px; /* Adjusted height */
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            margin-top: 15px;
            overflow: hidden; /* Ensure image respects border radius */
        }
         .map-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .offre-avis-section {
            margin-top: 40px; /* More space before reviews */
            border-top: 1px solid var(--couleur-bordure); /* Separator line */
            padding-top: 30px;
        }
        .offre-avis-section > h2 { /* "Avis avec photos" - main title for section */
            font-size: 1.3em;
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            margin-bottom: 20px;
            /* border-bottom: 1px solid var(--couleur-bordure); */
            /* padding-bottom: 8px; */
        }
        .avis-tabs { /* "Note", "Recommandation" */
            display: flex;
            gap: 0; /* No gap, borders will separate */
            margin-bottom: 25px;
            border: 1px solid var(--couleur-bordure); /* Border around tab container */
            border-radius: 8px;
            overflow: hidden; /* To clip button borders */
            width: fit-content; /* Make it only as wide as its content */
        }
        .avis-tabs button {
            padding: 10px 20px; /* Adjusted padding */
            border: none;
            /* border-right: 1px solid var(--couleur-bordure); */ /* Separator between tabs */
            background-color: var(--couleur-blanche); /* Default tab background */
            cursor: pointer;
            font-size: 0.9em; /* Adjusted size */
            font-weight: var(--font-weight-medium);
            color: var(--couleur-texte-footer);
           /* border-bottom: 3px solid transparent; */ /* Removed bottom border for this style */
            transition: background-color 0.3s ease, color 0.3s ease;
        }
         .avis-tabs button:not(:last-child) {
            border-right: 1px solid var(--couleur-bordure);
        }

        .avis-tabs button.active, .avis-tabs button:hover {
            background-color: var(--couleur-principale); /* Active/hover tab background */
            color: var(--couleur-blanche); /* Active/hover tab text color */
           /* border-bottom-color: var(--couleur-principale); */
        }
        /* .avis-tabs button:last-child { border-right: none; } */


        .avis-card {
            background-color: var(--couleur-blanche); /* White background for card */
            padding: 20px; /* Increased padding */
            border-radius: 8px;
            margin-bottom: 20px; /* Increased margin */
            border: 1px solid var(--couleur-bordure); /* Border around each review card */
            display: flex;
            gap: 20px; /* Increased gap */
            box-shadow: 0 1px 3px rgba(0,0,0,0.04); /* Subtle shadow for cards */
        }
        .avis-avatar img {
            width: 40px; /* Slightly smaller avatar */
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
        .avis-user-info .name { /* This is the review title "Parfait", "Merci" */
            font-weight: var(--font-weight-semibold);
            color: var(--couleur-texte);
            font-size: 1em;
        }
        .avis-user-info .username-date { /* Container for actual username and date */
             font-size: 0.85em;
             color: #6c757d;
             margin-top: 3px;
        }
        /* .avis-user-info .date {
            font-size: 0.8em;
            color: #6c757d;
             margin-left: 0; Separated to new line
        } */
        .avis-rating { display: flex; align-items: center; } /* Ensure stars align */
        .avis-rating .fa-star { color: #FFC107; font-size: 0.9em; margin-left: 2px; }
        .avis-rating .fa-star.empty { color: #e0e0e0; }
        .avis-header-icons { /* For flag and other icons on top right of review card */
            color: #999;
            font-size: 1em;
        }
         .avis-header-icons i { cursor: pointer; }
         .avis-header-icons i:hover { color: var(--couleur-texte); }


        .avis-comment p {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            line-height: 1.6;
            margin-bottom: 12px; /* Increased margin */
        }
        .avis-actions {
            display: flex;
            align-items: center;
            gap: 15px; /* Gap between like/dislike */
        }
        .avis-actions button {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.9em;
            padding: 0; /* Remove padding */
            display: flex;
            align-items: center;
        }
         .avis-actions button i {
            margin-right: 6px;
            font-size: 1.1em; /* Slightly larger icons */
        }
        .avis-actions button:hover { color: var(--couleur-principale); }
        .avis-actions .report-action { /* For "Signaler" if it needs to be separate */
            margin-left: auto; /* Pushes it to the right if needed */
        }

        .avis-footer { /* Container for "Laisser un avis" button and navigation */
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px; /* Space above this section */
        }

        .btn-laisser-avis {
            background-color: var(--couleur-principale); /* Matches mockup button */
            color: var(--couleur-blanche);
            padding: 10px 25px; /* Adjusted padding */
            border-radius: 8px; /* Matches mockup button radius */
            text-decoration: none;
            text-align: center;
            font-weight: var(--font-weight-medium);
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
            margin-bottom: 25px; /* Space below button, before arrows */
            border: none;
            /* border: 1px solid var(--couleur-secondaire-hover-border); */
        }
        .btn-laisser-avis:hover {
            background-color: var(--couleur-principale-hover);
            /* color: var(--couleur-principale-hover); */
        }

        .avis-navigation {
            display: flex;
            justify-content: space-between; /* Pushes arrows to edges */
            align-items: center;
            width: 100px; /* Fixed width for the arrow container for centering */
            margin: 0 auto; /* Center the arrows */
        }
        .avis-navigation button {
            background: none;
            border: none;
            color: var(--couleur-texte); /* Arrows are black */
            font-size: 1.2em; /* Adjusted size */
            cursor: pointer;
            padding: 5px;
        }
        .avis-navigation button:hover { color: var(--couleur-principale); }
        .avis-navigation button:disabled { color: #ccc; cursor: not-allowed; }


        /* Responsive adjustments */
        @media (max-width: 992px) {
            .offre-gallery-and-purchase, .offre-detailed-info {
                flex-direction: column;
            }
            .offre-gallery, .offre-purchase-details,
            .offre-description-text, .offre-additional-details {
                flex-basis: auto; /* Reset flex-basis */
            }
            .offre-gallery { height: 350px; }
            .breadcrumb-bar, .offre-container { padding-left: 0; padding-right: 0;} /* Remove side padding for smaller screens if container fluid */
        }
        @media (max-width: 768px) {
            .offre-header { flex-direction: column; align-items: flex-start;}
            .offre-favorite-btn { margin-top:10px; font-size: 1.8em; align-self: flex-start;} /* Mockup has it top right, adjust as needed */
            .gallery-image-container { height: 280px; }
            .offre-purchase-details .price { font-size: 1.8em; }
            .offre-container { padding: 0 10px; } /* Small padding for mobile */

            .avis-card {
                flex-direction: column;
                align-items: flex-start; /* Align items to the start */
            }
            .avis-avatar {
                margin-bottom: 10px;
                /* text-align: left; */ /* Align avatar to left */
            }
            .avis-header {
                flex-direction: row; /* Keep header items in a row */
                align-items: center; /* Align items center in the header */
                width: 100%; /* Make header take full width */
            }
            .avis-user-info {
                flex-grow: 1; /* Allow user info to take available space */
            }
            .avis-rating {
                margin-top: 0; /* Remove top margin */
                margin-left: 10px; /* Add some space if too close */
            }
             .avis-header-icons {
                margin-left: auto; /* Push icons to the right */
            }
            .avis-tabs { width: 100%; }
            .avis-tabs button { flex-grow: 1; text-align: center; }
        }

    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="header-left">
                <a href="index.php"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="logo"></a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
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

    <main class="main-content-offre">
        <div class="container">
            <div class="breadcrumb-bar">
                <a href="recherche.php"><i class="fas fa-arrow-left"></i></a>
                </div>

            <div class="offre-container">
                <div class="offre-header">
                    <div class="offre-header-main">
                        <h1 class="title">Archipel de Bréhat en kayak</h1>
                        <p class="provider">Planète Kayak</p>
                        <div class="tags">
                            <span>Ouvert</span>
                            <span>Sport</span>
                            <span>Famille</span>
                            <span>Plein air</span>
                        </div>
                    </div>
                    <button class="offre-favorite-btn" aria-label="Ajouter aux favoris">
                        <i class="far fa-heart"></i>
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <div class="offre-gallery-and-purchase">
                    <div class="offre-gallery">
                        <div class="gallery-image-container">
                            <img src="images/kayak.jpg" alt="Kayak dans l'archipel de Bréhat">
                            </div>
                        <button class="gallery-arrow prev" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
                        <button class="gallery-arrow next" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="offre-purchase-details">
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
                            <img src="images/map_placeholder.png" alt="Carte de localisation">
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
                                <img src="images/avatar1.jpg" alt="Avatar Alicia">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Parfait</span>
                                        <div class="username-date">Alicia - 26 mars 2024</div>
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
                                <img src="images/avatar2.jpg" alt="Avatar Benoît">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Merci</span>
                                        <div class="username-date">Benoît - 24 mars 2024</div>
                                    </div>
                                    <div class="avis-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star empty"></i>
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
                                <img src="images/avatar3.jpg" alt="Avatar Luigi">
                            </div>
                            <div class="avis-content">
                                <div class="avis-header">
                                    <div class="avis-user-info">
                                        <span class="name">Génial !</span>
                                        <div class="username-date">Luigi - 18 mars 2024</div>
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
                    </div>
                    <div class="avis-footer">
                        <a href="#" class="btn-laisser-avis">Laisser un avis</a>
                        <div class="avis-navigation">
                            <button class="prev-avis" aria-label="Avis précédents" disabled><i class="fas fa-chevron-left"></i></button>
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
                <a href="index.php"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="footer-logo"></a>
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
                    <li><a href="index.php">Accueil</a></li>
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
            // Favorite button toggle
            const favoriteButton = document.querySelector('.offre-favorite-btn');
            if (favoriteButton) {
                favoriteButton.addEventListener('click', function() {
                    this.classList.toggle('active');
                    // Mettre à jour l'attribut aria-pressed pour l'accessibilité
                    const isPressed = this.classList.contains('active');
                    this.setAttribute('aria-pressed', isPressed);
                });
                // Initialiser aria-pressed au cas où l'état est déjà actif (ex: chargé depuis BDD)
                 const isInitiallyPressed = favoriteButton.classList.contains('active');
                 favoriteButton.setAttribute('aria-pressed', isInitiallyPressed);
            }

            // Avis tabs
            const avisTabsContainer = document.querySelector('.avis-tabs');
            const avisList = document.querySelector('.avis-list');
            if (avisTabsContainer) {
                const tabs = avisTabsContainer.querySelectorAll('button');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        tabs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        // Placeholder: Add logic here to filter/load reviews based on this.dataset.tab
                        console.log(`Onglet sélectionné: ${this.dataset.tab}`);
                        // Example: avisList.innerHTML = `Chargement des avis pour ${this.dataset.tab}...`;
                    });
                });
            }

            // Avis navigation (simple simulation)
            const prevAvisBtn = document.querySelector('.prev-avis');
            const nextAvisBtn = document.querySelector('.next-avis');
            let currentAvisPage = 1;
            const totalAvisPages = 3; // Example, replace with actual total pages

            function updateAvisNavigation() {
                if(prevAvisBtn) prevAvisBtn.disabled = currentAvisPage === 1;
                if(nextAvisBtn) nextAvisBtn.disabled = currentAvisPage === totalAvisPages;
                // Placeholder: Add logic to display reviews for 'currentAvisPage'
                console.log(`Page d'avis actuelle: ${currentAvisPage}`);
            }

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
            // updateAvisNavigation(); // Call to set initial state

            // Gallery arrows (simple simulation)
            const gallery = document.querySelector('.offre-gallery');
            if (gallery) {
                const prevGalleryBtn = gallery.querySelector('.prev');
                const nextGalleryBtn = gallery.querySelector('.next');
                // Placeholder: Add gallery image switching logic here
                if(prevGalleryBtn) prevGalleryBtn.addEventListener('click', () => console.log('Galerie: Précédent'));
                if(nextGalleryBtn) nextGalleryBtn.addEventListener('click', () => console.log('Galerie: Suivant'));
            }
        });
    </script>
</body>
</html>