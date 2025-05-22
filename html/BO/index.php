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
                <li><a href="recherche.php">Offres</a></li>
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
                <li><a href="profil.php">Profil</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <a href="creation-compte.php" class="btn btn-secondary">S'enregistrer</a>
            <a href="connexion-compte.php" class="btn btn-primary">Se connecter</a>
        </div>
    </div>
</header>

    <main>
        <div class="container content-area">
            <h1>Page d'accueil</h1>
            <p>Contenu principal de la page...</p>
        </div>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" aria-label="X"><i class="fab fa-x"></i></a>
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
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
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