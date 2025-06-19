<?php
session_start();

// On utilise le script de sécurité qui doit maintenant fonctionner
$userId = require_once __DIR__ . '/../../includes/auth_check_membre.php';

require_once '../../vendor/autoload.php';
require_once '../../includes/db.php';

use OTPHP\TOTP;

header('Content-Type: application/json');

if (!isset($_SESSION['otp_secret_temp']) || !isset($_SESSION['user_id_temp']) || $_SESSION['user_id_temp'] !== $userId) {
    echo json_encode(['success' => false, 'message' => 'Session de vérification invalide. Veuillez rafraîchir la page.']);
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
    
    if ($otp->verify($otp_code)) {
        $stmt = $pdo->prepare("UPDATE comptes_membre SET otp_secret = :secret, otp_enabled = 1 WHERE id = :id");
        $stmt->execute(['secret' => $_SESSION['otp_secret_temp'], 'id' => $userId]);
        
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