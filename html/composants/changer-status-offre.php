<?php
// Le chemin vers le dossier /includes est toujours correct (../.. remonte à la racine)
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
require_once __DIR__ . '/../../includes/db.php'; 

// CHEMIN CORRIGÉ : generate_uuid.php est maintenant dans le même dossier
require_once __DIR__ . '/generate_uuid.php'; 

// Le reste du code ne change pas
$offre_id = $_GET['id'] ?? null;
$new_status = isset($_GET['status']) ? (int)$_GET['status'] : null;

if (!$offre_id || !in_array($new_status, [0, 1])) {
    // On redirige vers la page de recherche dans le BO
    header('Location: ../BO/recherche.php?error=invalid_params');
    exit();
}

try {
    $stmt_check = $pdo->prepare("SELECT id FROM offres WHERE id = :offre_id AND pro_id = :pro_id");
    $stmt_check->execute([':offre_id' => $offre_id, ':pro_id' => $current_pro_id]);
    if ($stmt_check->fetch() === false) {
        header('Location: ../BO/recherche.php?error=unauthorized');
        exit();
    }

    $stmt_insert = $pdo->prepare(
        "INSERT INTO statuts (id, offre_id, status, changed_at) VALUES (:id, :offre_id, :status, NOW())"
    );
    $stmt_insert->execute([
        ':id' => generate_uuid(),
        ':offre_id' => $offre_id,
        ':status' => $new_status
    ]);

    // On redirige vers la page de recherche dans le BO
    header('Location: ../BO/recherche.php?status_change=success');
    exit();

} catch (PDOException $e) {
    error_log("Erreur lors du changement de statut pour l'offre ID {$offre_id}: " . $e->getMessage());
    header('Location: ../BO/recherche.php?status_change=error');
    exit();
}