<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Création de compte</title><link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Vos styles CSS restent inchangés */
        .content-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
        }

        .register-container {
            background-color: #fff;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 600px; /* Ajuster la largeur pour mieux correspondre à l'image */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .content-area h1 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 36px;
        }

        .content-area p{
            color: #000000;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }

        .register-form {
            display: grid; /* Utilisation de grid pour un placement plus précis */
            grid-template-columns: repeat(2, 1fr); /* Création de deux colonnes de largeur égale */
            gap: 15px 20px; /* Espacement vertical et horizontal entre les éléments */
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        /* Occuper deux colonnes pour les éléments sur une seule ligne */
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: #333;
            margin-bottom: 5px; /* Réduction de la marge pour un aspect plus compact */
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input[type="name"],
        .form-group input[type="text"],
        .form-group input[type="last-name"],
        .form-group input[type="tel"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .register-button-container {
            grid-column: 1 / -1; /* Le bouton occupe toute la largeur */
            display: flex;
            flex-direction: row;
            justify-content: space-between; /* Alignement à droite du bouton */
            margin-top: 20px;
        }

        .register-button {
            background-color:var(--couleur-principale);
            color: #fff;
            border: none;
            padding: 15px 30px; /* Réduction du padding pour un bouton moins grand */
            border-radius: 16px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .register-button:hover {
            background-color: var(--couleur-principale-hover);
        }

        .already-registered-container {
            grid-column: 1 / -1; /* "J'ai déjà un compte" occupe toute la largeur */
            text-align: left;
            margin-top: 20px;
        }

        .already-registered {
            text-decoration: underline;
            color: #000;
        }

        .already-registered:hover {
            text-decoration: underline;
            color: #000;
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
                        <li><a href="recherche.php">Recherche</a></li>
                    </ul>
                </nav>
            </div>
            <div class="header-right">
                <a href="../BO/index.php" class="pro-link desktop-only">Je suis professionnel</a>
                <a href="creation-compte.php" class="btn btn-secondary desktop-only active">S'enregistrer</a>
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
                <li><a href="recherche.php">Recherche</a></li>
                <li><a href="../BO/index.php">Je suis professionnel</a></li>
                <li><a href="creation-compte.php" class="active">S'enregistrer</a></li>
                <li><a href="connexion-compte.php">Se connecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area">
            <h1>Créer un compte</h1>
            <p>Rejoignez nous !</p>
            <div class="register-container">
                <form class="register-form">
                    <div class="form-group">
                        <label for="name">Prénom</label>
                        <input type="name" id="name" name="name" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label for="last-name">Nom</label>
                        <input type="last-name" id="last-name" name="last-name" placeholder="Doe">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="adressemail@exemple.com">
                    </div>
                    <div class="form-group">
                        <label for="pseudo">Pseudonyme</label>
                        <input type="text" id="pseudo" name="pseudo" placeholder="john_doe">
                    </div>
                    <div class="form-group">
                        <label for="tel">N° Téléphone</label>
                        <input type="tel" id="tel" name="tel" placeholder="+33701020304">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresse">Adresse postale</label>
                        <input type="text" id="adresse" name="adresse" placeholder="1 impasse Victor Hugo">
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" placeholder="Lannion">
                    </div>
                    <div class="form-group">
                        <label for="code_postal">Code Postal</label>
                        <input type="text" id="code_postal" name="code_postal" placeholder="22300">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Mot de passe">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmation de mot de passe</label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Mot de passe">
                    </div>
                    <div class="register-button-container">
                        <a href="#" class="already-registered">J'ai déjà un compte</a>
                        <button type="submit" class="register-button">S'inscrire</button>
                    </div>
                </form>
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
</body>
</html>