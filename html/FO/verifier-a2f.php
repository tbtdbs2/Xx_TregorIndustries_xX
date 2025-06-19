<?php
// On active l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// --- DÉBUT DE LA MODIFICATION DE TEST ---

// On met TEMPORAIREMENT en commentaire votre script de sécurité
// $userId = require_once __DIR__ . '/../../includes/auth_check_membre.php'; 

// À la place, on se fie à l'ID utilisateur qu'on a stocké dans la session sur la page précédente
if (!isset($_SESSION['user_id_temp'])) {
    // Si l'ID n'est pas en session, c'est que la navigation est anormale.
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de session. Veuillez rafraîchir la page d\'activation.']);
    exit();
}
$userId = $_SESSION['user_id_temp'];

// --- FIN DE LA MODIFICATION DE TEST ---

require_once '../../vendor/autoload.php';
require_once '../../includes/db.php';

use OTPHP\TOTP;

header('Content-Type: application/json');

// Vérifier que le secret temporaire existe bien et correspond à l'utilisateur
if (!isset($_SESSION['otp_secret_temp'])) {
    echo json_encode(['success' => false, 'message' => 'Session de vérification invalide ou expirée. Veuillez rafraîchir la page.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$otp_code = $data['otp_code'] ?? '';

if (empty($otp_code)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez fournir un code.']);
    exit();
}

try {
    $otp = TOTP::create($_SESSION['otp_secret_temp']);
    
    // Vérification du code
    if ($otp->verify($otp_code)) {
        // Le code est bon, on active l'A2F dans la base de données
        $stmt = $pdo->prepare(
            "UPDATE comptes_membre SET otp_secret = :secret, otp_enabled = 1 WHERE id = :id"
        );
        $stmt->execute([
            ':secret' => $_SESSION['otp_secret_temp'],
            ':id' => $userId
        ]);
        
        unset($_SESSION['otp_secret_temp']);
        unset($_SESSION['user_id_temp']);

        echo json_encode(['success' => true, 'message' => 'Authentification à deux facteurs activée avec succès !']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Code de vérification incorrect.']);
    }

} catch (Exception $e) {
    error_log('Erreur A2F (verifier-a2f.php): ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur interne est survenue.']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Vérification A2F</title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-area { display: flex; flex-direction: column; align-items: center; padding: 40px; }
        .login-container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 360px; }
        .login-container h1 { margin-top:0; margin-bottom: 10px; font-size: 28px; }
        .login-container p { margin-bottom: 25px; font-size: 16px; color: #555; line-height: 1.5; }
        .login-form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500;}
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 18px; text-align: center; letter-spacing: 5px; box-sizing: border-box; }
        .login-button { background-color:var(--couleur-principale); color: #fff; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: background-color 0.3s ease;}
        .login-button:hover { background-color: var(--couleur-principale-hover); }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <main>
        <div class="container content-area">
            <div class="login-container">
                <h1>Vérification requise</h1>
                <p>Ouvrez votre application d'authentification et saisissez le code pour vous connecter.</p>
                
                <?php if (!empty($error_message)): ?>
                    <p style="color: red; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                
                <form class="login-form" action="verifier-login-a2f.php" method="POST">
                    <div class="form-group">
                        <label for="otp-code">Code à 6 chiffres</label>
                        <input type="text" id="otp-code" name="otp_code" required maxlength="6" pattern="\d{6}" autofocus>
                    </div>
                    <button type="submit" class="login-button">Valider et se connecter</button>
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
                    <li><a href="../index.html">Accueil</a></li>
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