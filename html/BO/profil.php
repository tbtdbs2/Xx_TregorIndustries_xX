<?php
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
?>
<?php
// --- AJOUT POUR AFFICHER LA NOTIFICATION ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// --- FIN DE L'AJOUT ---
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Profil</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Add your custom styles here */
        .container.content-area {
            padding: 32px 0px;
            text-align: center; /* centre le texte à l’intérieur */
            display: flex;
            flex-direction: column;
            align-items: center; /* centre le contenu horizontalement */

        }

        input {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            
        }

        .profil {

            box-sizing: border-box;
            border: 1px solid #D9D9D9;
            border-radius: 16px;
            padding: 32px;
            display: flex;
            gap : 24px;
            width: 660px;
            height: 870px;
            flex-wrap: wrap;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        .email, .denomination, .adresse_postal, .siren, .iban, .bic {

            display: flex;
            flex-wrap: wrap;
            width: 595px;
            height: 70px;
            gap: 8px;
        
        }

        .telephone, .ville, .secteur, .code_postal {

            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
        
        }

        #email, #denomination, #adresse, #siren, #iban, #bic {

            box-sizing: border-box;
            width: 595px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;

        }

        #telephone, #ville, #secteur, #code_postal {

            box-sizing: border-box;
            width: 240px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;

        }

        .ville_code_postal{

            display: flex;
            gap: 32px;
            width: 520px;
            height: 70px;


        }
        
        /* --- STYLE POUR LE MESSAGE D'ERREUR --- */
        .message.error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            max-width: 660px;
            margin-left: auto;
            margin-right: auto;
        }
        /* --- FIN STYLE POUR LE MESSAGE D'ERREUR --- */
                
        @media (max-width: 768px) {
    .container.content-area {
        padding: 16px 0px; /* Réduire le padding pour les petits écrans */
    }

    .profil {
        width: 100%; /* Prendre toute la largeur de l'écran */
        height: auto; /* Permettre à la hauteur de s'ajuster automatiquement */
        padding: 16px; /* Réduire le padding */
    }

    .email,
    .denomination,
    .adresse_postal,   
    .siren,
    .iban,
    .bic,
    .telephone,
    .ville,
    .secteur,
    .code_postal {
        width: 100%; /* Prendre toute la largeur de l'écran */
        margin-left: 0; /* Supprimer la marge gauche */
    }

    #email,
    #denomination, 
    #adresse,
    #siren,
    #iban,
    #bic,
    #telephone,
    #ville,
    #secteur,
    #code_postal {
    
        width: 100%; /* Prendre toute la largeur de l'écran */
    }

    h1 {
        font-size: 24px; /* Réduire la taille de la police pour le titre */
    }
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

    <main>
        <div class="container content-area">

            <?php
            // --- AJOUT POUR AFFICHER LA NOTIFICATION ---
            if (isset($_SESSION['notification_error'])) {
                echo "<div class='message error'>" . htmlspecialchars($_SESSION['notification_error']) . "</div>";
                // On supprime la notification pour qu'elle ne s'affiche pas à nouveau
                unset($_SESSION['notification_error']);
            }
            // --- FIN DE L'AJOUT ---
            ?>
            
            <h1>Mes Informations</h1>
            <div class="profil">
                <div class="email">
                        <label for="email">Email</label>
                        <input type="text" id="email" placeholder="john.doe@mail.com" readonly="readonly">
                </div>
                <div class="telephone">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" placeholder="+33701020304" readonly="readonly">   
                </div>
                <div class="denomination">
                    <label for="denomination">Dénomination / Raison sociale</label>
                    <input type="text" id="denomination" placeholder="toto Entreprise" readonly="readonly">
                </div>
                <div class="adresse_postal">
                    <label for="adresse">Ligne d'adresse</label>
                    <input type="text" id="adresse" placeholder="23 rue saint marc" readonly="readonly">
                </div>

                <div class="ville_code_postal">
                    <div class="ville">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" placeholder="Paris" readonly="readonly">
                    </div>
                    <div class="code_postal">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" placeholder="75002" readonly="readonly">
                    </div>
                </div>

                <div class="siren">
                    <label for="siren">Numéro de SIREN</label>
                    <input type="text" id="siren" placeholder="123 456 789 0000" readonly="readonly">
                </div>
                <div class="secteur">
                    <label for="siren">Secteur</label>
                    <select name="secteur" id="secteur">
                        <option value="privé">Privé</option>
                        <option value="public">Public</option>
                    </select>
                </div>
                <div class="iban">
                    <label for="iban">IBAN</label>
                    <input type="text" id="iban" placeholder="FR76 1234 5678 9012 3456 7890 1234" readonly="readonly">
                </div>
                <div class="bic">
                    <label for="bic">BIC</label>
                    <input type="text" id="bic" placeholder="123456789" readonly="readonly">
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
</body>
</html>