<?php
// Inclure la connexion à la base de données pour pouvoir interagir avec la table des tokens
require_once '../includes/db.php';

// 1. Invalider le token côté serveur
// On vérifie si le cookie d'authentification existe et si la connexion à la BDD est active
if (isset($_COOKIE['auth_token']) && isset($pdo)) {
    // On supprime le token de la base de données pour qu'il ne soit plus valide
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE token = :token");
    $stmt->execute([':token' => $_COOKIE['auth_token']]);
}

// 2. Effacer les cookies côté client
// On leur donne une date d'expiration dans le passé pour que le navigateur les supprime.
setcookie('auth_token', '', time() - 3600, '/');
setcookie('user_type', '', time() - 3600, '/');

// 3. Rediriger vers la page d'accueil avec un chemin absolu correct
// Ce chemin part de la racine du site et pointe directement vers le bon fichier.
header("Location: /index.html?status=deconnecte");
exit();
?>