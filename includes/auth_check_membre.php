<?php
// Ce script vérifie si l'utilisateur est un membre authentifié.
// Il est destiné à être inclus au début des pages à protéger (ex: profil.php).

require_once __DIR__ . '/db.php';

// 1. Vérifier la présence des cookies et le bon type d'utilisateur
if (!isset($_COOKIE['auth_token']) || !isset($_COOKIE['user_type']) || $_COOKIE['user_type'] !== 'membre') {
    // Rediriger vers la page de connexion du FO si non authentifié comme membre
    header('Location: /FO/connexion-compte.php?error=acces_interdit');
    exit();
}

if (!isset($pdo)) {
     die("Erreur critique: Impossible de se connecter à la base de données pour vérifier l'authentification.");
}

// 2. Vérifier si le token existe dans la base de données
$token = $_COOKIE['auth_token'];
$stmt = $pdo->prepare("SELECT email FROM auth_tokens WHERE token = :token");
$stmt->execute([':token' => $token]);
$auth_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auth_user) {
    // Token invalide, on nettoie et on redirige
    setcookie('auth_token', '', time() - 3600, '/');
    setcookie('user_type', '', time() - 3600, '/');
    header('Location: /FO/connexion-compte.php?error=session_expiree');
    exit();
}

// 3. Le token est valide, on récupère l'ID du membre
$stmt_membre = $pdo->prepare("SELECT id FROM comptes_membre WHERE email = :email");
$stmt_membre->execute(['email' => $auth_user['email']]);
$membre_user = $stmt_membre->fetch(PDO::FETCH_ASSOC);

if (!$membre_user) {
    header('Location: /FO/connexion-compte.php?error=utilisateur_introuvable');
    exit();
}

// 4. Authentification réussie. On retourne l'ID du membre.
return $membre_user['id'];
?>