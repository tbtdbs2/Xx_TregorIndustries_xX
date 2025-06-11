<?php
// Ce script vérifie si l'utilisateur est un professionnel authentifié.
// S'il ne l'est pas, il le redirige vers la page de connexion.
// S'il l'est, il retourne l'ID de l'utilisateur.

// Inclure la connexion à la base de données. Le `__DIR__` assure que le chemin est toujours correct.
require_once __DIR__ . '/db.php';

// 1. Vérifier la présence des cookies et le bon type d'utilisateur
if (!isset($_COOKIE['auth_token']) || !isset($_COOKIE['user_type']) || $_COOKIE['user_type'] !== 'pro') {
    // Rediriger si les cookies sont absents ou si le type n'est pas 'pro'
    header('Location: /BO/connexion-compte.php?error=acces_interdit');
    exit();
}

$user_id = null;

if (isset($pdo)) {
    // 2. Vérifier si le token existe dans la base de données
    $token = $_COOKIE['auth_token'];
    $stmt = $pdo->prepare("SELECT email FROM auth_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $auth_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auth_user) {
        // Le token est invalide (expiré ou faux)
        // On nettoie les cookies erronés et on redirige
        setcookie('auth_token', '', time() - 3600, '/');
        setcookie('user_type', '', time() - 3600, '/');
        header('Location: /BO/connexion-compte.php?error=session_expiree');
        exit();
    }
    
    // 3. Le token est valide, on récupère l'ID du professionnel via son email
    $stmt_pro = $pdo->prepare("SELECT id FROM comptes_pro WHERE email = :email");
    $stmt_pro->execute([':email' => $auth_user['email']]);
    $pro_user = $stmt_pro->fetch(PDO::FETCH_ASSOC);

    if (!$pro_user) {
        // Cas rare : le token est valide mais l'utilisateur a été supprimé
        header('Location: /BO/connexion-compte.php?error=utilisateur_introuvable');
        exit();
    }
    
    // 4. Authentification réussie ! On retourne l'ID de l'utilisateur.
    return $pro_user['id'];

} else {
    die("Erreur critique: Impossible de se connecter à la base de données pour vérifier l'authentification.");
}
?>