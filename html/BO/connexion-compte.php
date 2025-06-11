<?php
$error_message = '';

// Le code ne s'exécute que lorsque l'utilisateur soumet le formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Inclusion des fichiers nécessaires
    require_once '../../includes/db.php';
    require_once '../composants/generate_uuid.php';

    // Vérification de la connexion à la base de données
    if (!isset($pdo)) {
        $error_message = "Erreur de connexion à la base de données.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = "Veuillez saisir votre email et votre mot de passe.";
        } else {
            // Recherche de l'utilisateur dans la table `comptes_pro`
            $stmt = $pdo->prepare("SELECT id, password FROM comptes_pro WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérification si l'utilisateur existe et si le mot de passe correspond (en clair)
            if ($user && $password === $user['password']) {
                // Connexion réussie, on gère le token
                
                // 1. Générer un token unique et sécurisé
                $token = bin2hex(random_bytes(32));
                
                // 2. Assurer l'exclusivité : supprimer les anciens tokens pour cet utilisateur
                $stmtDelete = $pdo->prepare("DELETE FROM auth_tokens WHERE email = :email");
                $stmtDelete->execute([':email' => $email]);
                
                // 3. Insérer le nouveau token dans la BDD
                $stmtInsert = $pdo->prepare("INSERT INTO auth_tokens (id, email, token) VALUES (:id, :email, :token)");
                $stmtInsert->execute([
                    ':id'      => generate_uuid(),
                    ':email'   => $email,
                    ':token'   => $token
                ]);
                
                // 4. Définir les cookies d'authentification
                $cookie_options = [
                    'expires' => time() + 86400, // Expire dans 1 jour
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ];
                setcookie('auth_token', $token, $cookie_options);
                setcookie('user_type', 'pro', $cookie_options);

                // 5. Rediriger vers le tableau de bord du back-office
                header("Location: index.php");
                exit();
            } else {
                // En cas d'échec
                $error_message = "Email ou mot de passe incorrect.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Connexion</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https:/fonts.googleapis.com">
    <link rel="preconnect" href="https:/fonts.gstatic.com" crossorigin>
    <link href="https:/fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https:/cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px;
    }

    .login-container {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        width: 320px;
        height: 368px;
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

    .login-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        text-align: left;
    }

    .form-group label {
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
        font-weight: 500;
    }

    .form-group input[type="email"],
    .form-group input[type="password"] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    .login-button {
        background-color:var(--couleur-principale);
        
        color: #fff;
        border: none;
        padding: 12px 20px;
        border-radius: 16px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .login-button:hover {
        background-color: var(--couleur-principale-hover);
    }

    .options {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-top: 15px;
        font-size: 14px;
        width: 100%;
    }

    .options div {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .options label {
        color: #333;
        font-weight: normal;
    }

    .options input[type="checkbox"] {
        margin-right: 5px;
    }

    .forgot-password {
        text-decoration: underline;
        color: #000;
        margin-top: 5px;
    }

    .forgot-password:hover {
        text-decoration: underline;
        color: #000;
    }
    .inscription-pro-lien:hover {
        text-decoration: underline;
        color: #000;
    }
    .inscription-pro-lien {
        text-decoration: underline;
        color: #000;
        margin-top: 5px;
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
            <a href="profil.php" class="btn btn-secondary">Mon profil</a>
            <a href="/deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

<main>
    <div class="container content-area">
        <h1>Connexion</h1>
        <p>Consultez vos offres !</p>
        <div class="login-container">
            <?php if (!empty($error_message)) { echo '<p style="color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;">' . htmlspecialchars($error_message) . '</p>'; } ?>
            
            <form class="login-form" method="POST" action="connexion-compte.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="adressemail@exemple.com">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit" class="login-button">Connexion</button>
                <div class="options">
                    <div>
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Rester connecté</label>
                    </div>
                    <a href="mdp-oublié.php" class="forgot-password">Mot de passe oublié ?</a>
                    <a href="creation-compte.php" class="inscription-pro-lien">Je m'inscris en tant que professionnel</a>
                </div>
            </form>
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