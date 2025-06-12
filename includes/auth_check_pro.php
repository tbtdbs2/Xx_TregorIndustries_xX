<?php
// includes/auth_check_pro.php

// 1. Démarrer la session pour accéder aux variables $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// 2. Vérifier la présence des cookies et le bon type d'utilisateur
if (!isset($_COOKIE['auth_token']) || !isset($_COOKIE['user_type']) || $_COOKIE['user_type'] !== 'pro') {
    header('Location: /BO/connexion-compte.php?error=acces_interdit');
    exit();
}

// Initialisation des variables
$unanswered_reviews_count = 0;
$total_offers_count = 0;
$active_offers_count = 0;
$total_reviews_count = 0;
$current_pro_id = null;

if (isset($pdo)) {
    // 3. Vérifier la validité du token et récupérer l'ID du pro
    $token = $_COOKIE['auth_token'];
    $stmt = $pdo->prepare("SELECT email FROM auth_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $auth_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($auth_user) {
        $stmt_pro = $pdo->prepare("SELECT id FROM comptes_pro WHERE email = :email");
        $stmt_pro->execute([':email' => $auth_user['email']]);
        $pro_user = $stmt_pro->fetch(PDO::FETCH_ASSOC);

        if ($pro_user) {
            // 4. Authentification réussie. On stocke l'ID.
            $current_pro_id = $pro_user['id'];
            
            // CORRECTION : On stocke l'ID dans la session pour le réutiliser partout
            $_SESSION['pro_id'] = $current_pro_id;

            // --- CALCUL DES STATISTIQUES POUR LE DASHBOARD ---
            try {
                // ... (Le reste des calculs de statistiques reste identique)
                // Assurez-vous que les requêtes ici utilisent bien 'pro_id' et non 'professionnel_id'
                
                // Compteur d'avis non répondus
                $stmt_unanswered = $pdo->prepare("SELECT COUNT(a.id) FROM avis a JOIN offres o ON a.offre_id = o.id WHERE o.pro_id = :pro_id AND NOT EXISTS (SELECT 1 FROM reponses_pro rp WHERE rp.avis_id = a.id)");
                $stmt_unanswered->execute([':pro_id' => $current_pro_id]);
                $unanswered_reviews_count = (int) $stmt_unanswered->fetchColumn();

                // Compteur d'offres totales
                $stmt_total_offers = $pdo->prepare("SELECT COUNT(id) FROM offres WHERE pro_id = :pro_id");
                $stmt_total_offers->execute([':pro_id' => $current_pro_id]);
                $total_offers_count = (int) $stmt_total_offers->fetchColumn();
                
                // Compteur d'offres actives
                $stmt_active_offers = $pdo->prepare("SELECT COUNT(DISTINCT o.id) FROM offres o JOIN statuts s ON o.id = s.offre_id WHERE o.pro_id = :pro_id AND s.status = 1 AND s.changed_at = (SELECT MAX(s2.changed_at) FROM statuts s2 WHERE s2.offre_id = o.id)");
                $stmt_active_offers->execute([':pro_id' => $current_pro_id]);
                $active_offers_count = (int) $stmt_active_offers->fetchColumn();

                // Compteur total des avis reçus
                $stmt_total_reviews = $pdo->prepare("SELECT COUNT(a.id) FROM avis a JOIN offres o ON a.offre_id = o.id WHERE o.pro_id = :pro_id");
                $stmt_total_reviews->execute([':pro_id' => $current_pro_id]);
                $total_reviews_count = (int) $stmt_total_reviews->fetchColumn();

            } catch (PDOException $e) {
                error_log("Erreur lors du calcul des statistiques pour le pro ID {$current_pro_id}: " . $e->getMessage());
            }
        }
    }

    if (!$current_pro_id) {
        // Le token est invalide ou l'utilisateur n'existe plus
        setcookie('auth_token', '', time() - 3600, '/');
        setcookie('user_type', '', time() - 3600, '/');
        header('Location: /BO/connexion-compte.php?error=session_expiree');
        exit();
    }
    
    // 5. On retourne l'ID de l'utilisateur pour la page qui fait le 'require_once'
    return $current_pro_id;

} else {
    die("Erreur critique: Impossible de se connecter à la base de données pour vérifier l'authentification.");
}
?>