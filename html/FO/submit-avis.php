<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../composants/generate_uuid.php';

header('Content-Type: application/json'); // La réponse sera du JSON

$response = ['success' => false, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($pdo)) {

    $offer_id = $_POST['offer_id'] ?? null;
    $membre_id = $_POST['membre_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $rating = $_POST['rating'] ?? null;
    $visit_date = $_POST['visit_date'] ?? '';
    $context = $_POST['context'] ?? '';

    function validate_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        return $data;
    }

    // Validation des données
    if (empty($membre_id)) {
        $response['errors']['membre_id'] = "ID du membre manquant. Vous devez être connecté.";
    }
    if (empty($offer_id)) {
        $response['errors']['offer_id'] = "ID de l'offre manquant.";
    }
    if (empty($title)) {
        $response['errors']['title'] = "Veuillez entrer un titre pour votre avis.";
    } elseif (strlen($title) > 64) {
        $response['errors']['title'] = "Le titre ne peut pas dépasser 64 caractères.";
    }
    if (empty($comment)) {
        $response['errors']['comment'] = "Veuillez entrer votre commentaire.";
    } elseif (strlen($comment) > 512) {
        $response['errors']['comment'] = "Le commentaire ne peut pas dépasser 512 caractères.";
    }
    if ($rating === null || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $response['errors']['rating'] = "Veuillez donner une note entre 1 et 5 étoiles.";
    }
    if (empty($visit_date)) {
        $response['errors']['visit_date'] = "Veuillez entrer la date de votre visite.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $visit_date) || strtotime($visit_date) > time()) {
        $response['errors']['visit_date'] = "Date de visite invalide ou future.";
    }
    if (empty($context) || !in_array($context, ['affaires', 'couple', 'famille', 'amis', 'solo'])) {
        $response['errors']['context'] = "Veuillez sélectionner un contexte de visite valide.";
    }

    if (empty($response['errors'])) {
        try {
            $pdo->beginTransaction();

            // Vérifier que le membre n'a pas déjà laissé un avis pour cette offre
            $stmt_check_existing_review = $pdo->prepare("SELECT id FROM avis WHERE membre_id = :membre_id AND offre_id = :offer_id");
            $stmt_check_existing_review->execute([':membre_id' => $membre_id, ':offer_id' => $offer_id]);
            if ($stmt_check_existing_review->fetch()) {
                $response['errors']['duplicate_review'] = "Vous avez déjà laissé un avis pour cette offre.";
                $pdo->rollBack();
            } else {
                // Insérer l'avis
                $avis_id = generate_uuid();
                $stmt_insert_avis = $pdo->prepare("
                    INSERT INTO avis (id, membre_id, offre_id, title, comment, rating, visit_date, context, viewed, published_at)
                    VALUES (:id, :membre_id, :offre_id, :title, :comment, :rating, :visit_date, :context, 0, :published_at)
                ");
                $stmt_insert_avis->execute([
                    ':id' => $avis_id,
                    ':membre_id' => $membre_id,
                    ':offre_id' => $offer_id,
                    ':title' => validate_input($title),
                    ':comment' => validate_input($comment),
                    ':rating' => (float)$rating,
                    ':visit_date' => $visit_date,
                    ':context' => $context,
                    ':published_at' => time() // Timestamp UNIX actuel
                ]);

                // Mettre à jour la note moyenne de l'offre
                $stmt_avg_rating = $pdo->prepare("
                    UPDATE offres
                    SET
                        rating = (SELECT AVG(rating) FROM avis WHERE offre_id = :offre_id),
                        reviews_nb = (SELECT COUNT(id) FROM avis WHERE offre_id = :offre_id)
                    WHERE id = :offre_id
                ");
                $stmt_avg_rating->execute([':offre_id' => $offer_id]);

                $pdo->commit();
                $response['success'] = true;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erreur PDO lors de la soumission de l'avis : " . $e->getMessage());
            $response['errors']['db_error'] = "Erreur de base de données. Veuillez réessayer. " . $e->getMessage();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erreur générale lors de la soumission de l'avis : " . $e->getMessage());
            $response['errors']['general'] = "Une erreur inattendue est survenue.";
        }
    }
} else {
    $response['errors']['request'] = "Requête invalide.";
}

echo json_encode($response);
?>