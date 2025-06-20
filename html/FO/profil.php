<?php
// 1. Inclure le script d'authentification qui vérifie le token.
// Ce script redirige si l'utilisateur n'est pas connecté et retourne son ID.
$userId = require_once __DIR__ . '/../../includes/auth_check_membre.php';

// La session reste utilisée pour les messages flash (erreurs de validation, succès de mise à jour).
session_start();


require_once __DIR__ . '/../../includes/db.php';

$update_message = '';
$validation_errors_from_session = []; // Pour les erreurs de validation serveur
$submitted_data_from_session = []; // Pour repeupler le formulaire après erreur serveur

// Variables pour les messages de pop-up
$show_popup = false;
$popup_message = '';
$popup_type = ''; // 'success' ou 'error'

// Récupérer les erreurs de validation et les données soumises de la session, si elles existent
if (isset($_GET['validation_error']) && $_GET['validation_error'] === 'true') {
    $validation_errors_from_session = $_SESSION['validation_errors'] ?? [];
    $submitted_data_from_session = $_SESSION['submitted_post_data'] ?? [];
    unset($_SESSION['validation_errors'], $_SESSION['submitted_post_data']); // Nettoyer la session
}

// --- GESTION DE LA MISE À JOUR DU PROFIL (POST REQUEST) ---
// La vérification de l'authentification est déjà faite. $userId contient l'ID du membre.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userIdToUpdate = $userId; // Utilisation de l'ID fourni par le script d'authentification
    $adresseIdToUpdate = $_POST['actual_adresse_id'] ?? null;

    // --- Validation des données ---
    $errors = [];
    $submitted_data = $_POST; // Conserver toutes les données POST

    // Pseudonyme
    if (empty(trim($submitted_data['pseudonyme'] ?? ''))) {
        $errors['pseudonyme'] = 'Le pseudonyme est requis.';
    }

    // Prénom
    if (empty(trim($submitted_data['prenom'] ?? ''))) {
        $errors['prenom'] = 'Le prénom est requis.';
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/u', $submitted_data['prenom'])) {
        $errors['prenom'] = 'Le prénom contient des caractères non autorisés.';
    }

    // Nom
    if (empty(trim($submitted_data['nom'] ?? ''))) {
        $errors['nom'] = 'Le nom est requis.';
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/u', $submitted_data['nom'])) {
        $errors['nom'] = 'Le nom contient des caractères non autorisés.';
    }

    // Adresse Postale
    if (empty(trim($submitted_data['adresse_postale'] ?? ''))) {
        $errors['adresse_postale'] = 'L\'adresse postale est requise.';
    }

    // Ville
    if (empty(trim($submitted_data['ville'] ?? ''))) {
        $errors['ville'] = 'La ville est requise.';
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/u', $submitted_data['ville'])) {
        $errors['ville'] = 'Le nom de la ville contient des caractères non autorisés.';
    }

    // Code Postal
    if (empty(trim($submitted_data['code_postal'] ?? ''))) {
        $errors['code_postal'] = 'Le code postal est requis.';
    } elseif (!preg_match('/^\d{5}$/', $submitted_data['code_postal'])) {
        $errors['code_postal'] = 'Le code postal doit être composé de 5 chiffres.';
    }

    // Email
    if (empty(trim($submitted_data['email'] ?? ''))) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($submitted_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Le format de l\'email est invalide.';
    }

    // Téléphone (vérification anti-lettres existante)
    $telephone_val_from_post = $submitted_data['telephone'] ?? null;
    if (!empty($telephone_val_from_post) && preg_match('/[a-zA-ZÀ-ÿ]/u', $telephone_val_from_post)) { // Ajout de À-ÿ et modificateur u
        $errors['telephone'] = 'Le numéro de téléphone ne peut pas contenir de lettres.';
    }

    // S'il y a des erreurs de validation
    if (!empty($errors)) {
        $_SESSION['validation_errors'] = $errors;
        $_SESSION['submitted_post_data'] = $submitted_data;
        header('Location: profil.php?validation_error=true');
        exit;
    }

    // Si pas d'erreurs de validation, procéder à la mise à jour DB
    if (!$adresseIdToUpdate) {
        error_log("Tentative de mise à jour du profil sans actual_adresse_id pour user ID " . $userIdToUpdate);
        header('Location: profil.php?update=error_no_address_id');
        exit;
    }

    try {

        $pdo->beginTransaction();

        $sqlAdresse = "UPDATE adresses SET street = :street, city = :city, postal_code = :postal_code WHERE id = :adresse_id_val";
        $stmtAdresse = $pdo->prepare($sqlAdresse);
        $stmtAdresse->bindParam(':street', $submitted_data['adresse_postale']);
        $stmtAdresse->bindParam(':city', $submitted_data['ville']);
        $stmtAdresse->bindParam(':postal_code', $submitted_data['code_postal']);
        $stmtAdresse->bindParam(':adresse_id_val', $adresseIdToUpdate);
        $stmtAdresse->execute();

        $sqlMembre = "UPDATE comptes_membre SET alias = :pseudonyme, firstname = :prenom, lastname = :nom, email = :email, phone = :telephone WHERE id = :userId";
        $stmtMembre = $pdo->prepare($sqlMembre);
        $stmtMembre->bindParam(':pseudonyme', $submitted_data['pseudonyme']);
        $stmtMembre->bindParam(':prenom', $submitted_data['prenom']);
        $stmtMembre->bindParam(':nom', $submitted_data['nom']);
        $stmtMembre->bindParam(':email', $submitted_data['email']);
        $stmtMembre->bindParam(':telephone', $submitted_data['telephone']);
        $stmtMembre->bindParam(':userId', $userIdToUpdate);
        $stmtMembre->execute();

        $pdo->commit();
        header('Location: profil.php?update=success');
        exit;
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erreur de mise à jour du profil pour user ID " . $userIdToUpdate . " (Exception attrapée): " . $e->getMessage());
        header('Location: profil.php?update=error');
        exit;
    }
}


// Définir les messages de la pop-up basés sur les paramètres GET
if (isset($_GET['update'])) {
    if ($_GET['update'] === 'success') {
        $show_popup = true;
        $popup_message = "Vos informations ont été mises à jour avec succès !";
        $popup_type = 'success';
    } elseif ($_GET['update'] === 'error') {
        $show_popup = true;
        $popup_message = "Une erreur technique est survenue lors de la mise à jour. Veuillez réessayer.";
        $popup_type = 'error';
    } elseif ($_GET['update'] === 'error_no_address_id') {
        $show_popup = true;
        $popup_message = "Erreur : Identifiant d'adresse manquant pour la mise à jour.";
        $popup_type = 'error';
    }
}


// 2. L'ID de l'utilisateur est déjà dans $userId.
// La vérification de connexion et la redirection sont gérées par auth_check_membre.php.
$userLoggedIn = true;

// 3. Interroger la base de données pour récupérer les informations de l'utilisateur
$membre = null;

try {

    $sql = "SELECT cm.alias AS pseudonyme,
                   cm.firstname AS prenom, 
                   cm.lastname AS nom, 
                    a.street AS adresse_postale,
                    a.city AS ville, 
                    a.postal_code AS code_postal, 
                    cm.email,
                    cm.phone AS telephone,
                    cm.adresse_id AS actual_adresse_id 
            FROM comptes_membre cm
            JOIN adresses a ON cm.adresse_id = a.id
            WHERE cm.id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $membre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$membre) {
        error_log("Erreur : Utilisateur non trouvé dans la base de données pour l'ID de session : " . $userId);
        $membre = [];
    }
} catch (PDOException $e) {
    error_log("Erreur de base de données sur profil.php (SELECT) : " . $e->getMessage());
    $membre = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Mon Profil</title>
    <link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .container.content-area {
            padding: 32px 0px;
            text-align: center;
            align-items: center;
            display: flex;
            flex-direction: column;
        }

        input {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        input:focus {
            outline: none;
        }

        .grid-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        .card_section {
            box-sizing: border-box;
            border: 1px solid #D9D9D9;
            border-radius: 16px;
            padding: 32px;
            display: flex;
            gap: 24px;
            width: 700px;
            flex-wrap: wrap;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        .input_pseudo,
        .input_prenom,
        .input_nom,
        .adresse_postal,
        .ville,
        .code_postal,
        .mail,
        .telephone {
            display: flex;
            flex-wrap: wrap;
            height: auto;
            gap: 8px;
            margin: 10px;
        }

        .input_pseudo {
            width: 270px;
        }

        .input_prenom {
            width: 270px;
        }

        .input_nom {
            width: 270px;
        }

        .adresse_postal {
            width: 580px;
            margin-left: 20px;
        }

        .ville {
            width: 270px;
            margin-left: 20px;
        }

        .code_postal {
            width: 270px;
            margin-left: 20px;
        }

        .mail {
            width: 580px;
            margin-left: 20px;
        }

        .telephone {
            width: 270px;
            margin-left: 20px;
        }

        #pseudo,
        #prenom,
        #nom,
        #adresse,
        #ville,
        #code_postal,
        #email,
        #telephone {
            box-sizing: border-box;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }

        #pseudo,
        #prenom,
        #nom,
        #ville,
        #code_postal,
        #telephone {
            width: 270px;
        }

        #adresse,
        #email {
            width: 580px;
        }

        .nom_prenom {
            display: flex;
            gap: 40px;
        }

        input[readonly] {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            border: 1px solid #ced4da;
        }

        input:not([readonly]) {
            background-color: #fff;
            color: #495057;
        }

        .buttons-form-profil {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 24px;
            padding-bottom: 20px;
        }

        .buttons-form-profil button {
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid transparent;
        }

        #btnModifierProfil {
            background-color: #008C8C;
            color: white;
        }

        #btnConfirmerProfil {
            background-color: rgb(0, 140, 98);
            color: white;
        }

        #btnAnnulerProfil {
            background-color: rgb(255, 72, 90);
            color: white;
        }

        .error-message-server,
        .error-message-js {
            color: red;
            display: block;
            /* Assure que le message d'erreur serveur s'affiche sur sa propre ligne */
            font-size: 0.9em;
            text-align: left;
            width: 100%;
            margin-top: 4px;
        }

        .error-message-js {
            /* S'assurer que le JS error span est caché par défaut */
            display: none;
        }

        /* Styles pour la pop-up */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .popup-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .popup-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .popup-overlay.show .popup-content {
            transform: scale(1);
        }

        .popup-content h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.8em;
            color: #333;
        }

        .popup-content p {
            margin-bottom: 20px;
            font-size: 1.1em;
            line-height: 1.5;
            color: #555;
        }

        .popup-content button {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }

        .popup-content button.success {
            background-color: #008C8C;
            /* Vert PACT */
            color: white;
        }

        .popup-content button.error {
            background-color: rgb(255, 72, 90);
            /* Rouge */
            color: white;
        }

        .popup-content button:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .container.content-area {
                padding: 16px 0px;
            }

            .card_section {
                width: 90%;
                padding: 16px;
                flex-direction: column;
                align-items: center;
            }

            .input_pseudo,
            .input_prenom,
            .input_nom,
            .adresse_postal,
            .ville,
            .code_postal,
            .mail,
            .telephone {
                width: 100%;
                margin-left: 0;
            }

            #pseudo,
            #prenom,
            #nom,
            #adresse,
            #ville,
            #code_postal,
            #email,
            #telephone {
                width: 100%;
            }

            h1 {
                font-size: 24px;
            }

            .grid-card {
                gap: 16px;
            }

            .nom_prenom {
                flex-direction: column;
                width: 100%;
                gap: 16px;
            }

            .header-right .desktop-only {
                display: none;
            }

            .mobile-nav-links ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .mobile-nav-links ul li {
                padding: 10px 0;
                text-align: center;
            }

            .buttons-form-profil {
                flex-direction: column;
                align-items: center;
            }

            .buttons-form-profil button {
                width: 80%;
                max-width: 300px;
            }
        }
    </style>

</head>

<body>
    <?php require_once 'header.php'; ?>
    <main>
        <div class="container content-area">
            <h1>Mes Informations</h1>
            <?php // if (!empty($update_message)) echo $update_message; // Ce bloc est remplacé par la pop-up 
            ?>

            <?php if ($membre && !empty($membre)): ?>
                <form id="profilForm" method="POST" action="profil.php">
                    <?php if (isset($membre['actual_adresse_id'])): ?>
                        <input type="hidden" name="actual_adresse_id" value="<?php echo htmlspecialchars($membre['actual_adresse_id']); ?>">
                    <?php endif; ?>

                    <div class="grid-card">
                        <div class="card_section Pseudonyme">
                            <div class="input_pseudo">
                                <label for="pseudo">Pseudonyme</label>
                                <input type="text" id="pseudo" name="pseudonyme" placeholder="Non défini" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['pseudonyme'] ?? $membre['pseudonyme'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['pseudonyme'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['pseudonyme']); ?></span>
                                <?php endif; ?>
                                <span id="pseudoError" class="error-message-js"></span>
                            </div>
                            <div class="nom_prenom">
                                <div class="input_prenom">
                                    <label for="prenom">Prénom</label>
                                    <input type="text" id="prenom" name="prenom" placeholder="Non défini" readonly="readonly"
                                        value="<?php echo htmlspecialchars($submitted_data_from_session['prenom'] ?? $membre['prenom'] ?? ''); ?>">
                                    <?php if (isset($validation_errors_from_session['prenom'])): ?>
                                        <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['prenom']); ?></span>
                                    <?php endif; ?>
                                    <span id="prenomError" class="error-message-js"></span>
                                </div>
                                <div class="input_nom">
                                    <label for="nom">Nom</label>
                                    <input type="text" id="nom" name="nom" placeholder="Non défini" readonly="readonly"
                                        value="<?php echo htmlspecialchars($submitted_data_from_session['nom'] ?? $membre['nom'] ?? ''); ?>">
                                    <?php if (isset($validation_errors_from_session['nom'])): ?>
                                        <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['nom']); ?></span>
                                    <?php endif; ?>
                                    <span id="nomError" class="error-message-js"></span>
                                </div>
                            </div>
                        </div>

                        <div class="card_section Adresse">
                            <div class="adresse_postal">
                                <label for="adresse">Adresse postale</label>
                                <input type="text" id="adresse" name="adresse_postale" placeholder="Non définie" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['adresse_postale'] ?? $membre['adresse_postale'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['adresse_postale'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['adresse_postale']); ?></span>
                                <?php endif; ?>
                                <span id="adresseError" class="error-message-js"></span>
                            </div>
                            <div class="ville">
                                <label for="ville">Ville</label>
                                <input type="text" id="ville" name="ville" placeholder="Non définie" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['ville'] ?? $membre['ville'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['ville'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['ville']); ?></span>
                                <?php endif; ?>
                                <span id="villeError" class="error-message-js"></span>
                            </div>
                            <div class="code_postal">
                                <label for="code_postal">Code postal</label>
                                <input type="text" id="code_postal" name="code_postal" placeholder="Non défini" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['code_postal'] ?? $membre['code_postal'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['code_postal'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['code_postal']); ?></span>
                                <?php endif; ?>
                                <span id="codePostalError" class="error-message-js"></span>
                            </div>
                        </div>

                        <div class="card_section Contact">
                            <div class="mail">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Non défini" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['email'] ?? $membre['email'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['email'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['email']); ?></span>
                                <?php endif; ?>
                                <span id="emailError" class="error-message-js"></span>
                            </div>
                            <div class="telephone">
                                <label for="telephone">Téléphone</label>
                                <input type="tel" id="telephone" name="telephone" placeholder="Non défini" readonly="readonly"
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['telephone'] ?? $membre['telephone'] ?? ''); ?>">
                                <?php if (isset($validation_errors_from_session['telephone'])): ?>
                                    <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['telephone']); ?></span>
                                <?php endif; ?>
                                <span id="telephoneError" class="error-message-js"></span>
                            </div>
                        </div>
                    </div>

                    <div class="buttons-form-profil">
                        <button type="button" id="btnModifierProfil">Modifier les informations</button>
                        <button type="submit" id="btnConfirmerProfil" style="display:none;">Confirmer les modifications</button>
                        <button type="button" id="btnAnnulerProfil" style="display:none;">Annuler</button>
                    </div>
                </form>
            <?php else: ?>
                <p>Vos informations de profil n'ont pas pu être chargées ou ne sont pas disponibles. Veuillez contacter le support si le problème persiste.</p>
                <?php if (isset($e) && $e instanceof PDOException) : ?>
                    <p style="color:red; font-family:monospace;">Erreur DÉVELOPPEMENT (SELECT): <?php echo htmlspecialchars($e->getMessage()); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <div class="card_section" style="margin-top: 20px; width: 700px; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; font-size: 1.2em;">Sécurité du compte</h2>
                    <p style="margin: 5px 0 0 0; color: #555;">Protégez votre compte avec la double authentification.</p>
                </div>
                <a href="Activation-A2F.php" class="btn-primary" style="text-decoration: none; padding: 12px 24px; border-radius: 8px;">Activer l'A2F</a>
            </div>
        </div>
    </main>
    <div id="popupOverlay" class="popup-overlay">
        <div class="popup-content">
            <h2 id="popupTitle"></h2>
            <p id="popupMessage"></p>
            <button id="popupCloseButton"></button>
        </div>
    </div>

    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <a href="index.php"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="footer-logo"></a>
                <div class="social-icons">
                    <a href="#" aria-label="Twitter PACT"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram PACT"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube PACT"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn PACT"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Professionnel</h3>
                <ul>
                    <li><a href="../BO/index.php">Comment poster une annonce</a></li>
                    <li><a href="../BO/creation-compte.php">Je crée mon compte pro</a></li>
                    <li><a href="../BO/connexion-compte.php">Je me connecte en tant que pro</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Découvrir</h3>
                <ul>
                    <li><a href="../index.php">Accueil</a></li>
                    <li><a href="recherche.php">Recherche</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Ressources</h3>
                <ul>
                    <li><a href="conditions-generales-d-utilisation.php">Conditions générales d'utilisation</a></li>
                    <li><a href="contact-du-responsable-du-site.php">Contact du responsable du site</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PACT. Tous droits réservés.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilForm = document.getElementById('profilForm');
            if (!profilForm) return;

            const btnModifier = document.getElementById('btnModifierProfil');
            const btnConfirmer = document.getElementById('btnConfirmerProfil');
            const btnAnnuler = document.getElementById('btnAnnulerProfil');
            const allInputs = profilForm.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');

            // Éléments de la pop-up
            const popupOverlay = document.getElementById('popupOverlay');
            const popupTitle = document.getElementById('popupTitle');
            const popupMessage = document.getElementById('popupMessage');
            const popupCloseButton = document.getElementById('popupCloseButton');

            const fieldValidators = [{
                    input: document.getElementById('pseudo'),
                    errorSpan: document.getElementById('pseudoError'),
                    validations: [{
                        type: 'required',
                        message: 'Le pseudonyme est requis.'
                    }]
                },
                {
                    input: document.getElementById('prenom'),
                    errorSpan: document.getElementById('prenomError'),
                    validations: [{
                            type: 'required',
                            message: 'Le prénom est requis.'
                        },
                        {
                            type: 'format',
                            regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u,
                            message: 'Le prénom contient des caractères non autorisés.'
                        }
                    ]
                },
                {
                    input: document.getElementById('nom'),
                    errorSpan: document.getElementById('nomError'),
                    validations: [{
                            type: 'required',
                            message: 'Le nom est requis.'
                        },
                        {
                            type: 'format',
                            regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u,
                            message: 'Le nom contient des caractères non autorisés.'
                        }
                    ]
                },
                {
                    input: document.getElementById('adresse'),
                    errorSpan: document.getElementById('adresseError'),
                    validations: [{
                        type: 'required',
                        message: 'L\'adresse postale est requise.'
                    }]
                },
                {
                    input: document.getElementById('ville'),
                    errorSpan: document.getElementById('villeError'),
                    validations: [{
                            type: 'required',
                            message: 'La ville est requise.'
                        },
                        {
                            type: 'format',
                            regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u,
                            message: 'Le nom de la ville contient des caractères non autorisés.'
                        }
                    ]
                },
                {
                    input: document.getElementById('code_postal'),
                    errorSpan: document.getElementById('codePostalError'),
                    validations: [{
                            type: 'required',
                            message: 'Le code postal est requis.'
                        },
                        {
                            type: 'format',
                            regex: /^\d{5}$/,
                            message: 'Le code postal doit être composé de 5 chiffres.'
                        }
                    ]
                },
                {
                    input: document.getElementById('email'),
                    errorSpan: document.getElementById('emailError'),
                    validations: [{
                            type: 'required',
                            message: 'L\'email est requis.'
                        },
                        {
                            type: 'format',
                            regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                            message: 'Le format de l\'email est invalide.'
                        } // Regex simple
                    ]
                },
                {
                    input: document.getElementById('telephone'),
                    errorSpan: document.getElementById('telephoneError'),
                    validations: [{
                            type: 'format',
                            regex: /^[^a-zA-ZÀ-ÿ]*$/u,
                            message: 'Le numéro de téléphone ne doit pas contenir de lettres.',
                            checkNonEmpty: true
                        },
                        {
                            type: 'format',
                            regex: /\d{10,}/,
                            message: 'Le numéro de téléphone doit être composé de 10 chiffres minimum.'
                        }
                    ]
                }
            ];

            let initialValues = {};

            function storeInitialValues() {
                allInputs.forEach(input => {
                    initialValues[input.id] = input.value;
                });
            }
            storeInitialValues();

            function clearAllJsErrors() {
                fieldValidators.forEach(field => {
                    if (field.errorSpan) {
                        field.errorSpan.textContent = '';
                        field.errorSpan.style.display = 'none';
                    }
                });
            }

            function enterEditMode() {
                storeInitialValues();
                allInputs.forEach(input => {
                    input.removeAttribute('readonly');
                });
                btnModifier.style.display = 'none';
                btnConfirmer.style.display = 'inline-block';
                btnAnnuler.style.display = 'inline-block';
            }

            function exitEditModeAndRevert() {
                allInputs.forEach(input => {
                    input.setAttribute('readonly', 'readonly');
                    // Les valeurs sont rechargées par PHP si validation serveur échoue,
                    // ou depuis $membre si pas de soumission.
                    // Pour annuler des modifs en cours non soumises, on remet les valeurs initiales chargées.
                    input.value = initialValues[input.id] || '';
                });
                btnModifier.style.display = 'inline-block';
                btnConfirmer.style.display = 'none';
                btnAnnuler.style.display = 'none';
                clearAllJsErrors();
                // Effacer aussi les messages d'erreur serveur affichés (si besoin, mais ils sont via PHP)
                document.querySelectorAll('.error-message-server').forEach(span => span.style.display = 'none');
            }

            if (btnModifier) {
                btnModifier.addEventListener('click', enterEditMode);
            }
            if (btnAnnuler) {
                btnAnnuler.addEventListener('click', exitEditModeAndRevert);
            }

            if (profilForm) {
                profilForm.addEventListener('submit', function(event) {
                    let overallIsValid = true;
                    clearAllJsErrors();

                    fieldValidators.forEach(field => {
                        if (!field.input || !field.errorSpan) return;

                        // On ne valide que si le champ n'est pas readonly (c-a-d en mode édition)
                        if (field.input.hasAttribute('readonly')) return;

                        const value = field.input.value.trim();
                        const originalValue = field.input.value; // Pour les regex qui ne doivent pas ignorer les espaces internes

                        for (const validation of field.validations) {
                            let fieldIsValid = true;
                            let testValue = value; // Valeur trimmée pour 'required'
                            if (validation.type === 'format' && validation.regex.source.includes('[a-zA-ZÀ-ÿ]') === false && validation.regex.source.includes('\\d{5}') === false && validation.regex.source.includes('@')) {
                                // Pour les regex qui ne sont pas des vérifications de lettres, code postal ou email, utiliser la valeur originale pour préserver les espaces etc.
                                testValue = originalValue;
                            }
                            if (validation.type === 'format' && validation.regex.source.includes('^[^a-zA-ZÀ-ÿ]*$')) { // Spécifique pour téléphone (pas de lettres)
                                testValue = originalValue;
                            }


                            if (validation.type === 'required') {
                                if (testValue === '') fieldIsValid = false;
                            } else if (validation.type === 'format') {
                                if (validation.checkNonEmpty && testValue === '') {
                                    fieldIsValid = true; // Valide si vide et que la validation de format ne s'applique qu'aux non-vides
                                } else if (testValue !== '' && !validation.regex.test(testValue)) { // Appliquer la regex si non vide ou si checkNonEmpty n'est pas là
                                    fieldIsValid = false;
                                } else if (!validation.checkNonEmpty && !validation.regex.test(testValue) && testValue !== '') {
                                    // Si ce n'est pas checkNonEmpty, on valide même si vide, sauf si la regex elle-même est pour un format qui implique non-vide (ex: email)
                                    // Cette logique devient complexe, simplifions : la regex s'applique. Si elle doit passer sur vide, la regex doit le permettre.
                                    // La plupart des regex de format n'accepteront pas une chaîne vide.
                                    if (!validation.regex.test(testValue)) fieldIsValid = false;
                                }
                                // Cas spécifique du téléphone: autoriser vide, mais si non vide, pas de lettres
                                if (field.input.id === 'telephone' && testValue !== '' && !validation.regex.test(testValue)) {
                                    fieldIsValid = false;
                                } else if (field.input.id === 'telephone' && testValue === '' && validation.type === 'format') {
                                    fieldIsValid = true; // Le téléphone vide est OK pour le format "pas de lettres"
                                }

                            }

                            if (!fieldIsValid) {
                                field.errorSpan.textContent = validation.message;
                                field.errorSpan.style.display = 'block';
                                overallIsValid = false;
                                break;
                            }
                        }
                    });

                    if (!overallIsValid) {
                        event.preventDefault();
                    }
                });
            }

            fieldValidators.forEach(field => {
                if (field.input && field.errorSpan) {
                    field.input.addEventListener('input', function() {
                        if (field.errorSpan.style.display === 'block') { // Seulement si une erreur JS est affichée
                            let value = field.input.value.trim();
                            let originalValue = field.input.value;
                            let stillError = false;

                            for (const validation of field.validations) {
                                let testValue = value;
                                if (validation.type === 'format' && validation.regex.source.includes('[a-zA-ZÀ-ÿ]') === false && validation.regex.source.includes('\\d{5}') === false && validation.regex.source.includes('@')) {
                                    testValue = originalValue;
                                }
                                if (validation.type === 'format' && validation.regex.source.includes('^[^a-zA-ZÀ-ÿ]*$')) {
                                    testValue = originalValue;
                                }

                                if (validation.type === 'required' && testValue === '') {
                                    stillError = true;
                                    break;
                                }
                                if (validation.type === 'format') {
                                    if (validation.checkNonEmpty && testValue === '') {
                                        // OK
                                    } else if (testValue !== '' && !validation.regex.test(testValue)) {
                                        stillError = true;
                                        break;
                                    } else if (!validation.checkNonEmpty && !validation.regex.test(testValue) && testValue !== '') {
                                        stillError = true;
                                        break;
                                    }
                                    // Cas spécifique du téléphone
                                    if (field.input.id === 'telephone' && testValue !== '' && !validation.regex.test(testValue)) {
                                        stillError = true;
                                        break;
                                    } else if (field.input.id === 'telephone' && testValue === '' && validation.type === 'format') {
                                        // vide est ok
                                    }
                                }
                            }
                            if (!stillError) {
                                field.errorSpan.textContent = '';
                                field.errorSpan.style.display = 'none';
                            }
                        }
                    });
                }
            });

            // Fonction pour afficher la pop-up
            function showPopup(title, message, type) {
                popupTitle.textContent = title;
                popupMessage.textContent = message;
                popupCloseButton.textContent = 'Fermer';
                // Supprimer toutes les classes de type précédentes
                popupCloseButton.classList.remove('success', 'error');
                // Ajouter la classe de type actuelle
                popupCloseButton.classList.add(type);
                popupOverlay.classList.add('show');
            }

            // Fonction pour cacher la pop-up
            function hidePopup() {
                popupOverlay.classList.remove('show');
                // Optionnel: Réinitialiser l'URL si nécessaire pour retirer les paramètres GET
                const url = new URL(window.location.href);
                url.searchParams.delete('update');
                history.replaceState(null, '', url.toString());
            }

            // Gérer le clic sur le bouton de fermeture de la pop-up
            popupCloseButton.addEventListener('click', hidePopup);
            // Gérer le clic en dehors de la pop-up pour la fermer
            popupOverlay.addEventListener('click', function(event) {
                if (event.target === popupOverlay) {
                    hidePopup();
                }
            });

            // Afficher la pop-up au chargement si les paramètres GET sont présents
            <?php if ($show_popup): ?>
                showPopup(
                    '<?php echo ($popup_type === "success") ? "Succès !" : "Erreur !"; ?>',
                    '<?php echo htmlspecialchars($popup_message); ?>',
                    '<?php echo $popup_type; ?>'
                );
            <?php endif; ?>
        });
    </script>
    <script src="script.js" defer></script>
</body>

</html>