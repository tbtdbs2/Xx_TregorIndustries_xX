<?php
require_once '../../includes/db.php';

if (isset($_COOKIE['auth_token']) && isset($pdo)) {
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE token = :token");
    $stmt->execute([':token' => $_COOKIE['auth_token']]);
}

setcookie('auth_token', '', time() - 3600, '/');
setcookie('user_type', '', time() - 3600, '/');

header("Location: ./connexion-compte.php?status=deconnecte");
exit();
?>