<?php
session_start();

$userId = require_once __DIR__ . '/../../includes/auth_check_membre.php';

require_once '../../vendor/autoload.php';
require_once '../../includes/db.php';

$stmt = $pdo->prepare("SELECT email FROM comptes_membre WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    die("Erreur : Utilisateur non trouvé.");
}
$user_email = $user['email'];

use OTPHP\TOTP;

$otp = TOTP::create();
$secret = $otp->getSecret();
$_SESSION['otp_secret_temp'] = $secret;
$_SESSION['user_id_temp'] = $userId;
$otp->setLabel($user_email);
$otp->setIssuer('PACT');
$qrCodeUri = $otp->getProvisioningUri();
$qrCodeImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUri);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Activer A2F</title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .a2f-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; width: 100%; max-width: 450px; display: flex; flex-direction: column; justify-content: center; margin-top: 20px; }
        .a2f-instructions { text-align: left; margin-bottom: 20px; }
        .a2f-instructions ol { padding-left: 20px; }
        .a2f-instructions li { margin-bottom: 10px; }
        .qr-code-container { margin: 20px 0; display: flex; justify-content: center; }
        .form-group input[type="text"] { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 18px; text-align: center; letter-spacing: 5px; }
        .error-message, .success-message { display: none; color: white; padding: 10px; border-radius: 4px; margin-top: 15px; font-size: 0.9em; }
        .error-message { background-color: #dc3545; }
        .success-message { background-color: #28a745; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main>
        <div class="container content-area">
            <h1>Activer l'authentification à deux facteurs (A2F)</h1>
            <p>Sécurisez votre compte en ajoutant une couche de protection.</p>

            <div class="a2f-container">
                <div class="a2f-instructions">
                    <ol>
                        <li>Installez une application d'authentification sur votre téléphone (ex: Google Authenticator, Authy).</li>
                        <li>Scannez le QR Code ci-dessous avec votre application.</li>
                        <li>Saisissez le code à 6 chiffres généré par l'application pour finaliser la configuration.</li>
                    </ol>
                </div>

                <div class="qr-code-container">
                    <img src="<?php echo htmlspecialchars($qrCodeImageUrl); ?>" alt="QR Code pour A2F">
                </div>

                <form class="login-form" id="a2f-verify-form">
                    <div class="form-group">
                        <label for="otp-code">Code de vérification</label>
                        <input type="text" id="otp-code" name="otp-code" required maxlength="6" pattern="\d{6}" placeholder="123456">
                    </div>
                    <button type="submit" class="login-button">Activer</button>
                </form>

                <p id="a2f-error" class="error-message"></p>
                <p id="a2f-success" class="success-message"></p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('a2f-verify-form');
            const otpInput = document.getElementById('otp-code');
            const errorElement = document.getElementById('a2f-error');
            const successElement = document.getElementById('a2f-success');

            if (form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); 

                    const otpCode = otpInput.value;
                    errorElement.style.display = 'none';
                    successElement.style.display = 'none';

                    if (!/^\d{6}$/.test(otpCode)) {
                        showError('Veuillez saisir un code à 6 chiffres.');
                        return;
                    }

                    fetch('verifier-a2f.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ otp_code: otpCode }),
                        credentials: 'same-origin' // LIGNE CORRIGÉE
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau ou serveur: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message);
                            otpInput.disabled = true;
                            form.querySelector('button').disabled = true;
                        } else {
                            showError(data.message || 'Le code de vérification est incorrect.');
                            otpInput.value = '';
                        }
                    })
                    .catch(err => {
                        console.error('Erreur AJAX:', err);
                        showError('Une erreur technique est survenue. Veuillez réessayer.');
                    });
                });
            }

            function showError(message) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }

            function showSuccess(message) {
                successElement.textContent = message;
                successElement.style.display = 'block';
            }
        });
    </script>
    <script src="script.js" defer></script>
</body>
</html>