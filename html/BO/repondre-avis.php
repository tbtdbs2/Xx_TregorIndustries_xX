<?php
// 1. SÉCURISATION ET INITIALISATION
// Vérifie si l'utilisateur est connecté en tant que professionnel et récupère son ID.
// Ceci est crucial pour s'assurer que seul le professionnel propriétaire de l'offre peut répondre.
$pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
require_once __DIR__ . '/../../includes/db.php'; // Inclusion du fichier de connexion à la base de données.
require_once __DIR__ . '/../composants/generate_uuid.php'; // Pour générer un UUID pour la réponse.

// Vérifie si la requête est de type POST et si le PDO est disponible.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($pdo)) {

    // Récupération et validation des données du formulaire.
    $avis_id = $_POST['avis_id'] ?? null;
    $offre_id = $_POST['offre_id'] ?? null;
    $content = $_POST['content'] ?? '';

    // Fonction de nettoyage des inputs (déjà présente dans d'autres fichiers).
    function validate_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $content = validate_input($content);

    // Initialisation du tableau d'erreurs.
    $erreurs = [];

    // Validation des champs.
    if (empty($avis_id)) {
        $erreurs['avis_id'] = "L'ID de l'avis est manquant.";
    }
    if (empty($offre_id)) {
        $erreurs['offre_id'] = "L'ID de l'offre est manquant.";
    }
    if (empty($content)) {
        $erreurs['content'] = "Le contenu de la réponse ne peut pas être vide.";
    } elseif (strlen($content) > 512) {
        $erreurs['content'] = "Le contenu de la réponse ne peut pas dépasser 512 caractères.";
    }

    // Si aucune erreur de validation des champs, procéder à l'insertion en base de données.
    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            // 1. Vérifier que l'avis existe et appartient bien à une offre du professionnel.
            // Cela empêche un professionnel de répondre à un avis sur une offre qui ne lui appartient pas.
            $stmt_check_avis = $pdo->prepare("SELECT a.id FROM avis a JOIN offres o ON a.offre_id = o.id WHERE a.id = :avis_id AND o.pro_id = :pro_id");
            $stmt_check_avis->execute([':avis_id' => $avis_id, ':pro_id' => $pro_id]);
            $avis_exists = $stmt_check_avis->fetch();

            if (!$avis_exists) {
                $erreurs['autorisation'] = "Vous n'êtes pas autorisé à répondre à cet avis ou l'avis n'existe pas.";
                $pdo->rollBack();
            } else {
                // 2. Vérifier si une réponse existe déjà pour cet avis (pour éviter les doublons).
                $stmt_check_response = $pdo->prepare("SELECT id FROM reponses_pro WHERE avis_id = :avis_id");
                $stmt_check_response->execute([':avis_id' => $avis_id]);
                $existing_response = $stmt_check_response->fetch();

                if ($existing_response) {
                    $erreurs['reponse_existante'] = "Une réponse a déjà été soumise pour cet avis.";
                    $pdo->rollBack();
                } else {
                    // 3. Insérer la nouvelle réponse dans la table `reponses_pro`.
                    $response_id = generate_uuid();
                    $stmt_insert_response = $pdo->prepare("INSERT INTO reponses_pro (id, pro_id, avis_id, content, published_at) VALUES (:id, :pro_id, :avis_id, :content, NOW())");
                    $stmt_insert_response->execute([
                        ':id' => $response_id,
                        ':pro_id' => $pro_id,
                        ':avis_id' => $avis_id,
                        ':content' => $content
                    ]);

                    // Mettre à jour le statut 'viewed' de l'avis à 1 (vu et répondu).
                    $stmt_update_avis_viewed = $pdo->prepare("UPDATE avis SET viewed = 1 WHERE id = :avis_id");
                    $stmt_update_avis_viewed->execute([':avis_id' => $avis_id]);

                    $pdo->commit();
                    $_SESSION['success_message'] = "Votre réponse a été publiée avec succès !";
                    // Rediriger vers la page de l'offre avec un message de succès.
                    header("Location: offre.php?id=" . urlencode($offre_id) . "&reponse_status=success");
                    exit();
                }
            }

        } catch (PDOException $e) {
            // En cas d'erreur PDO, annuler la transaction et enregistrer l'erreur.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erreur PDO lors de la réponse à l'avis : " . $e->getMessage());
            $erreurs['db_error'] = "Une erreur est survenue lors de l'enregistrement de votre réponse. Veuillez réessayer. Détails: " . $e->getMessage();
        } catch (Exception $e) {
            // Gérer d'autres exceptions.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erreur inattendue lors de la réponse à l'avis : " . $e->getMessage());
            $erreurs['general'] = "Une erreur inattendue est survenue. Veuillez réessayer.";
        }
    }

    // Si des erreurs existent (après validation ou try-catch), les stocker en session et rediriger.
    if (!empty($erreurs)) {
        $_SESSION['form_errors'] = $erreurs;
        $_SESSION['old_post_data'] = $_POST; // Pour pré-remplir le formulaire si besoin
        header("Location: offre.php?id=" . urlencode($offre_id) . "&reponse_status=error");
        exit();
    }
} else {
    // Si la requête n'est pas POST ou si les paramètres nécessaires sont manquants, rediriger.
    header("Location: recherche.php");
    exit();
}
?>