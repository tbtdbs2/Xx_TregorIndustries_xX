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
$unanswered_reviews_count = 0; // Initialiser le compteur de notifications

if (isset($pdo)) {
    // 2. Vérifier si le token existe dans la base de données
    $token = $_COOKIE['auth_token'];
    $stmt = $pdo->prepare("SELECT email FROM auth_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $auth_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auth_user) {
        // Le token est invalide (expiré ou faux)
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
    
    // 4. Authentification réussie !
    $current_pro_id_for_logic = $pro_user['id'];

    // --- MISE À JOUR : Calcul du nombre d'avis non vus ---
    try {
        // Nouvelle requête qui se base sur le champ 'viewed' = false
        $sql_count_reviews = "
            SELECT COUNT(a.id)
            FROM avis a
            JOIN offres o ON a.offre_id = o.id
            WHERE o.pro_id = :pro_id AND a.viewed = FALSE
        ";
        $stmt_count = $pdo->prepare($sql_count_reviews);
        $stmt_count->execute([':pro_id' => $current_pro_id_for_logic]);
        $unanswered_reviews_count = (int) $stmt_count->fetchColumn();

    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des avis non vus pour le pro ID {$current_pro_id_for_logic}: " . $e->getMessage());
        $unanswered_reviews_count = 0;
    }
    // --- FIN DE LA MISE À JOUR ---

    // 5. On retourne l'ID de l'utilisateur pour le reste de la page.
    return $current_pro_id_for_logic;

} else {
    die("Erreur critique: Impossible de se connecter à la base de données pour vérifier l'authentification.");
}
?>