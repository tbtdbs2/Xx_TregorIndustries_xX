<?php

session_start(); // Démarre la session PHP (doit être la première chose)

require_once __DIR__ . '/../../includes/db.php';

$login_error = ''; // Variable pour stocker les messages d'erreur de connexion

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
                $stmt = $pdo->prepare("SELECT id, email, password FROM comptes_membre WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(); // Récupère la première ligne de résultat

                // Vérifie si un utilisateur a été trouvé et si le mot de passe correspond
                if ($user && password_verify($password, $user['password'])) {
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
    <title>PACT - Connexion</title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const form = emailInput.closest('form');
            const errorMessage = document.createElement('p');
            errorMessage.className = 'error-message';
            errorMessage.style.color = 'red';
            errorMessage.style.fontSize = '0.9em';
            errorMessage.style.marginTop = '5px';
            errorMessage.style.textAlign = 'left';
            errorMessage.style.position = 'static';
            errorMessage.style.zIndex = 'auto';

            const emailGroup = emailInput.parentNode;
            emailGroup.insertBefore(errorMessage, emailInput.nextSibling);

            if (form) {
                form.addEventListener('submit', function(event) {
                    const emailValue = emailInput.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/;

                    if (!emailRegex.test(emailValue)) {
                        event.preventDefault();
                        errorMessage.textContent = 'Veuillez saisir une adresse email valide au format : monemail@mail.extension';
                        emailInput.classList.add('error');
                    } else {
                        errorMessage.textContent = '';
                        emailInput.classList.remove('error');
                    }
                });

                const style = document.createElement('style');
                style.textContent = `
                    .error {
                        border: 1px solid red;
                    }
                `;
                document.head.appendChild(style);
            }
        });
    </script>
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
                <a href="creation-compte.php" class="btn btn-secondary desktop-only">S'enregistrer</a>
                <a href="connexion-compte.php" class="btn btn-primary desktop-only active">Se connecter</a>
                <div class="mobile-icons">
                    <a href="index.php" class="mobile-icon" aria-label="Accueil"><i class="fas fa-home"></i></a>
                    <a href="profil.php" class="mobile-icon" aria-label="Profil"><i class="fas fa-user"></i></a> <button class="mobile-icon hamburger-menu" aria-label="Menu" aria-expanded="false">
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
                <li><a href="creation-compte.php">S'enregistrer</a></li>
                <li><a href="connexion-compte.php" class="active">Se connecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area">
            <h1>Connexion</h1>
            <p>Bon retour parmi nous !</p>

            <?php
            // Affichage du message d'erreur pour l'utilisateur
            if (!empty($login_error)) {
                echo '<p style="color: red; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($login_error) . '</p>';
            }
            ?>

            <div class="login-container">
                <form class="login-form" action="connexion-compte.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="adressemail@exemple.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-button">Connexion</button>
                    <div class="options">
                        <div>
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Rester connecté</label>
                        </div>
                        <a href="mdp-oublié.php" class="forgot-password">Mot de passe oublié ?</a>
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
                    <li><a href="conditions-generales-d'utilisation.php">Conditions générales d'utilisation</a></li>
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