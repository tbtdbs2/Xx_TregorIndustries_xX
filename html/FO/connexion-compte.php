<?php
// On démarre la session au tout début du script.
session_start();

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once '../../includes/db.php';
    require_once '../composants/generate_uuid.php';

    if (!isset($pdo)) {
        $error_message = "Erreur de connexion à la base de données.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = "Veuillez saisir votre email et votre mot de passe.";
        } else {
            // --- CORRECTION DE LA REQUÊTE ---
            // On ajoute le champ 'email' à la sélection
            $stmt = $pdo->prepare("SELECT id, email, password, otp_enabled, otp_secret FROM comptes_membre WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérification du mot de passe en clair
            if ($user && $password === $user['password']) {
                
                if ($user['otp_enabled'] && !empty($user['otp_secret'])) {
                    // La variable $user['email'] contient maintenant la bonne valeur.
                    $_SESSION['2fa_user_email'] = $user['email'];
                    header("Location: verifier-login-a2f.php");
                    exit();
                } else {
                    // Connexion normale
                    $token = bin2hex(random_bytes(32));
                    
                    $stmtDelete = $pdo->prepare("DELETE FROM auth_tokens WHERE email = :email");
                    $stmtDelete->execute([':email' => $email]);
                    
                    $stmtInsert = $pdo->prepare("INSERT INTO auth_tokens (id, email, token) VALUES (:id, :email, :token)");
                    $stmtInsert->execute([':id' => generate_uuid(), ':email' => $email, ':token' => $token]);
                    
                    $cookie_options = ['expires' => time() + 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax'];
                    setcookie('auth_token', $token, $cookie_options);
                    setcookie('user_type', 'membre', $cookie_options);

                    header("Location: recherche.php");
                    exit();
                }
            } else {
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

        .content-area p {
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
            background-color: var(--couleur-principale);
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
                <a href="../index.php"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="logo"></a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.php">Accueil</a></li>
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
                <li><a href="creation-compte.php">S'enregistrer</a></li>
                <li><a href="connexion-compte.php" class="active">Se connecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area">
            <h1>Connexion</h1>
            <p>Bon retour parmi nous !</p>

            <div class="login-container">
                <?php if (!empty($error_message)) {
                    echo '<p style="color: red; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($error_message) . '</p>';
                } ?>

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