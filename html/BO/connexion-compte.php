<?php

session_start(); // Démarre la session PHP (doit être la première chose)

require_once __DIR__ . '/../../includes/db.php';

$login_error = ''; // Variable pour stocker les messages d'erreur de connexion
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sae');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
// --- Redirection si déjà connecté ---
// Si l'utilisateur est déjà connecté via la session, on le redirige directement vers le profil.
if (isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit();
}

// --- Traitement du formulaire de connexion ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifie si l'email et le mot de passe ont été soumis
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validation du format de l'email côté serveur
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $login_error = "Le format de l'adresse email est invalide.";
        } else {
            try {
                

                // Prépare et exécute la requête pour récupérer l'utilisateur par son email
                $stmt = $pdo->prepare("SELECT id, email, password FROM comptes_pro WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(); // Récupère la première ligne de résultat
                
                // Vérifie si un utilisateur a été trouvé et si le mot de passe correspond
                //if ($user && password_verify($password, $user['password'])) {
                if ($user && $password === $user['password']) {
                    // --- AUTHENTIFICATION RÉUSSIE ---

                    session_regenerate_id(true); // Régénère l'ID de session pour prévenir la fixation de session

                    // Stockage des informations utilisateur en session
                    $_SESSION['user_id'] = $user['id']; // ID du membre
                    $_SESSION['user_email'] = $user['email'];

                    // ====================================================================
                    // GESTION DU TOKEN DE PERSISTENCE (TABLE auth_tokens)
                    // ====================================================================

                    $raw_persistence_token = bin2hex(random_bytes(64));
                    $hashed_token_to_store = hash('sha256', $raw_persistence_token);

                    $stmt_check_token = $pdo->prepare("SELECT COUNT(*) FROM auth_tokens WHERE email = :email");
                    $stmt_check_token->execute(['email' => $user['email']]);
                    $token_exists = $stmt_check_token->fetchColumn();

                    if ($token_exists) {
                        $stmt_update_token = $pdo->prepare(
                            "UPDATE auth_tokens SET token = :new_token_hash WHERE email = :email"
                        );
                        $stmt_update_token->execute([
                            'new_token_hash' => $hashed_token_to_store,
                            'email' => $user['email']
                        ]);
                    } else {
                        $stmt_insert_token = $pdo->prepare(
                            "INSERT INTO auth_tokens (email, token) VALUES (:email, :token_hash)"
                        );
                        $stmt_insert_token->execute([
                            'email' => $user['email'],
                            'token_hash' => $hashed_token_to_store
                        ]);
                    }

                    $_SESSION['session_token'] = $raw_persistence_token;

                    if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                        setcookie(
                            'remember_me',
                            $raw_persistence_token,
                            [
                                'expires' => time() + (86400 * 30),
                                'path' => '/',
                                'httponly' => true,
                                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                                'samesite' => 'Lax'
                            ]
                        );
                    }

                    // Redirection vers la page de profil après connexion réussie
                    header("Location: profil.php");
                    exit();

                } else {
                    // Email ou mot de passe incorrect
                    $login_error = "Email ou mot de passe incorrect.";
                }

            } catch (PDOException $e) {
                $login_error = "Erreur de connexion. Veuillez réessayer plus tard.";
                error_log("Erreur PDO dans connexion-compte.php: " . $e->getMessage());
            }
        }
    } else {
        $login_error = "Veuillez saisir votre email et votre mot de passe.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Connexion</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <a href="" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

<main>
    <div class="container content-area">
        <h1>Connexion</h1>
        <p>Consultez vos offres !</p>
        <div class="login-container">
            <form class="login-form">
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
                    <a href="#" class="inscription-pro-lien">Je m'inscris en tant que professionnel</a>
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