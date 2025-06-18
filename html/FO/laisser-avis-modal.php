<?php
session_start(); // Démarre la session
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../composants/generate_uuid.php';

// --- Début de la logique de vérification de connexion du membre (identique à offre.php) ---
$is_logged_in_member = false;
$membre_id = null;

if (isset($_COOKIE['auth_token']) && isset($_COOKIE['user_type']) && $_COOKIE['user_type'] === 'membre') {
    if (isset($pdo)) {
        $token = $_COOKIE['auth_token'];
        $stmt = $pdo->prepare("SELECT email FROM auth_tokens WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $auth_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($auth_user) {
            $stmt_membre = $pdo->prepare("SELECT id FROM comptes_membre WHERE email = :email");
            $stmt_membre->execute(['email' => $auth_user['email']]);
            $membre_user = $stmt_membre->fetch(PDO::FETCH_ASSOC);

            if ($membre_user) {
                $is_logged_in_member = true; // L'utilisateur est connecté en tant que membre
                $membre_id = $membre_user['id']; // Récupère l'ID du membre
            }
        }
    }
}
// --- Fin de la logique de vérification de connexion du membre ---


// Vérifier que l'utilisateur est bien connecté en tant que membre
if (!$is_logged_in_member) {
    // Si pas connecté, renvoyer une erreur JSON pour que le JS puisse rediriger.
    // Cette partie est cruciale pour le comportement de redirection côté client.
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non authentifié.']);
    exit();
}

$offer_id = $_GET['offer_id'] ?? null;

if (!$offer_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de l\'offre manquant.']);
    exit();
}

// Récupérer le titre de l'offre pour l'afficher dans la modale
$offer_title = '';
try {
    $stmt = $pdo->prepare("SELECT title FROM offres WHERE id = :offer_id");
    $stmt->execute(['offer_id' => $offer_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $offer_title = $result['title'];
    }
} catch (PDOException $e) {
    error_log("Erreur BDD lors de la récupération du titre de l'offre pour la modale : " . $e->getMessage());
    $offer_title = "Erreur de chargement du titre";
}

// Les données postées précédemment (si le formulaire a été soumis avec des erreurs via AJAX)
// Ces données sont gérées par le JS qui ne rechargera pas la page, donc cette partie PHP ne sera pas exécutée pour un POST AJAX échoué
// Cette partie était plus utile quand le formulaire était soumis via un rechargement de page.
// Pour les besoins actuels (AJAX), on peut la simplifier ou la retirer si le JS gère tout le pré-remplissage.
$old_post_data = []; // Le JS gérera le pré-remplissage en cas d'erreur de soumission AJAX

// Les messages d'erreur de la session (si le formulaire a été soumis avec des erreurs)
// Idem, pour AJAX, les erreurs reviennent directement dans la réponse JSON de submit-avis.php
$form_errors = []; // Le JS affichera les erreurs du JSON.
?>

<h2>Laisser un avis pour "<?php echo htmlspecialchars($offer_title); ?>"</h2>

<div class="modal-error-message" style="display: none;">
    </div>

<form id="avisForm" method="POST" action="submit-avis.php">
    <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($offer_id); ?>">
    <input type="hidden" name="membre_id" value="<?php echo htmlspecialchars($membre_id); ?>">

    <label for="avis_title">Titre de l'avis *</label>
    <input type="text" id="avis_title" name="title" required value="">

    <label for="avis_comment">Votre commentaire *</label>
    <textarea id="avis_comment" name="comment" required></textarea>

    <label for="avis_rating">Votre note *</label>
    <div class="rating-input">
        <div class="stars">
            <i class="far fa-star" data-rating="1"></i>
            <i class="far fa-star" data-rating="2"></i>
            <i class="far fa-star" data-rating="3"></i>
            <i class="far fa-star" data-rating="4"></i>
            <i class="far fa-star" data-rating="5"></i>
        </div>
        <input type="hidden" id="rating_input" name="rating" value="0" required>
    </div>

    <label for="visit_date">Date de votre visite *</label>
    <input type="date" id="visit_date" name="visit_date" required value="" max="<?php echo date('Y-m-d'); ?>">

    <label for="context">Contexte de la visite *</label>
    <select id="context" name="context" required>
        <option value="">Sélectionnez un contexte</option>
        <option value="affaires">Affaires</option>
        <option value="couple">Couple</option>
        <option value="famille">Famille</option>
        <option value="amis">Amis</option>
        <option value="solo">Solo</option>
    </select>

    <button type="submit">Soumettre mon avis</button>
</form>