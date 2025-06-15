<?php
// 1. Inclure le script d'authentification qui vérifie le token.
// Ce script redirige si l'utilisateur n'est pas connecté et retourne son ID.
$userId = require_once __DIR__ . '/../../includes/auth_check_pro.php';

// La session reste utilisée pour les messages flash (erreurs de validation, succès de mise à jour).
session_start();

$_SESSION['user_id'] = $userId; 


require_once __DIR__ . '/../../includes/db.php';


// Variables pour les messages et les données de session
$validation_errors_from_session = [];
$submitted_data_from_session = [];
$show_popup = false;
$popup_message = '';
$popup_type = ''; // 'success' ou 'error'

// Récupérer les erreurs de validation et les données soumises de la session
if (isset($_GET['validation_error']) && $_GET['validation_error'] === 'true') {
    $validation_errors_from_session = $_SESSION['validation_errors'] ?? [];
    $submitted_data_from_session = $_SESSION['submitted_post_data'] ?? [];
    unset($_SESSION['validation_errors'], $_SESSION['submitted_post_data']); // Nettoyer la session
}

// --- GESTION DE LA MISE À JOUR DU PROFIL (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userIdToUpdate = $_SESSION['user_id'];
    $adresseIdToUpdate = $_POST['actual_adresse_id'] ?? null;

    // --- Validation des données ---
    $errors = [];
    $submitted_data = $_POST;

    if (empty(trim($submitted_data['denomination'] ?? ''))) {
        $errors['denomination'] = 'La dénomination est requise.';
    }
    if (empty(trim($submitted_data['adresse'] ?? ''))) {
        $errors['adresse'] = 'La ligne d\'adresse est requise.';
    }
    if (empty(trim($submitted_data['ville'] ?? ''))) {
        $errors['ville'] = 'La ville est requise.';
    }
    if (empty(trim($submitted_data['code_postal'] ?? ''))) {
        $errors['code_postal'] = 'Le code postal est requis.';
    } elseif (!preg_match('/^\d{5}$/', $submitted_data['code_postal'])) {
        $errors['code_postal'] = 'Le code postal doit être composé de 5 chiffres.';
    }
    if (empty(trim($submitted_data['siren'] ?? ''))) {
        $errors['siren'] = 'Le numéro de SIREN est requis.';
    } elseif (!preg_match('/^\d{9}$/', str_replace(' ', '', $submitted_data['siren']))){
        $errors['siren'] = 'Le SIREN doit être composé de 9 chiffres.';
    }
    if (empty(trim($submitted_data['email'] ?? ''))) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($submitted_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Le format de l\'email est invalide.';
    }
    // Ajout de la validation pour le numéro de téléphone
    if (!empty(trim($submitted_data['telephone'] ?? ''))) {
        $telephone = $submitted_data['telephone'];
        // Vérifie si le numéro contient des lettres
        if (preg_match('/[a-zA-Z]/', $telephone)) {
            $errors['telephone'] = 'Le numéro de téléphone ne doit pas contenir de lettres.';
        // Vérifie si le numéro a moins de 10 chiffres (en ignorant les espaces, +, etc.)
        } elseif (strlen(preg_replace('/[^0-9]/', '', $telephone)) < 10) {
            $errors['telephone'] = 'Le numéro de téléphone doit contenir au moins 10 chiffres.';
        }
    }
    // Validation pour IBAN et BIC (vérifications simples pour l'exemple)
    if (empty(trim($submitted_data['iban'] ?? ''))) {
        $errors['iban'] = 'L\'IBAN est requis.';
    }
    if (empty(trim($submitted_data['bic'] ?? ''))) {
        $errors['bic'] = 'Le BIC est requis.';
    }

    // S'il y a des erreurs, stocker en session et rediriger
    if (!empty($errors)) {
        $_SESSION['validation_errors'] = $errors;
        $_SESSION['submitted_post_data'] = $submitted_data;
        header('Location: profil_pro.php?validation_error=true');
        exit;
    }

    // Si la validation réussit, procéder à la mise à jour
    if (!$adresseIdToUpdate) {
         header('Location: profil_pro.php?update=error_no_address_id');
         exit;
    }

    $secteurForDb = ($submitted_data['secteur'] === 'privé') ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // Mettre à jour la table des adresses
        $sqlAdresse = "UPDATE adresses SET street = :street, city = :city, postal_code = :postal_code WHERE id = :adresse_id_val";
        $stmtAdresse = $pdo->prepare($sqlAdresse);
        $stmtAdresse->execute([
            ':street' => $submitted_data['adresse'],
            ':city' => $submitted_data['ville'],
            ':postal_code' => $submitted_data['code_postal'],
            ':adresse_id_val' => $adresseIdToUpdate
        ]);

        // Mettre à jour la table des comptes professionnels
        // ATTENTION: Remplacez 'comptes_pro' et les noms de colonnes par les vôtres
        
        $sqlPro = "UPDATE comptes_pro SET email = :email, phone = :telephone, company_name = :denomination, siren = :siren, is_private = :secteur, iban = :iban, bic = :bic WHERE id = :userId";
        $stmtPro = $pdo->prepare($sqlPro);
        $stmtPro->execute([
            ':email' => $submitted_data['email'],
            ':telephone' => $submitted_data['telephone'],
            ':denomination' => $submitted_data['denomination'],
            ':siren' => str_replace(' ', '', $submitted_data['siren']),
            ':secteur' => $secteurForDb,
            ':iban' => $submitted_data['iban'],
            ':bic' => $submitted_data['bic'],
            ':userId' => $userIdToUpdate
        ]);

        $pdo->commit();
        header('Location: profil.php?update=success');
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erreur de mise à jour du profil PRO pour user ID " . $userIdToUpdate . ": " . $e->getMessage());
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
        $popup_message = "Une erreur technique est survenue. Veuillez réessayer.";
        $popup_type = 'error';
    } elseif ($_GET['update'] === 'error_no_address_id') {
        $show_popup = true;
        $popup_message = "Erreur : Identifiant d'adresse manquant.";
        $popup_type = 'error';
    }
}


// --- GESTION DE L'AFFICHAGE DU PROFIL (GET REQUEST) ---
// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion-compte.php');
    exit;
}

// 2. Récupérer les informations de l'utilisateur professionnel

$pro_user = null;

try {
    // ATTENTION: Remplacez 'comptes_pro' et les noms de colonnes par les vôtres
    $sql = "SELECT cp.email, 
                   cp.phone AS telephone, 
                   cp.company_name AS denomination, 
                   cp.siren, 
                   cp.is_private AS secteur,
                   cp.iban,
                   cp.bic,
                   a.street AS adresse,
                   a.city AS ville,
                   a.postal_code AS code_postal,
                   cp.adresse_id AS actual_adresse_id
            FROM comptes_pro cp
            JOIN adresses a ON cp.adresse_id = a.id
            WHERE cp.id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $pro_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pro_user) {
        $pro_user = []; // Gérer le cas où l'utilisateur n'est pas trouvé
    }

} catch (PDOException $e) {
    error_log("Erreur de base de données sur profil_pro.php (SELECT) : " . $e->getMessage());
    $pro_user = []; // Gérer l'erreur de base de données
}

$pro_user_json = json_encode($pro_user);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Profil</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container.content-area{padding:32px 0;text-align:center;display:flex;flex-direction:column;align-items:center}
        input,select{font-family:'Inter',sans-serif;font-size:16px}
        input:focus,select:focus{outline:none}
        .profil{box-sizing:border-box;border:1px solid #d9d9d9;border-radius:16px;padding:32px;display:flex;gap:24px;width:660px;flex-wrap:wrap;font-family:'Inter',sans-serif;font-size:16px}
        .email,.denomination,.adresse_postal,.siren,.iban,.bic{display:flex;flex-wrap:wrap;width:595px;height:auto;gap:8px}
        .telephone,.ville,.secteur,.code_postal{display:flex;flex-wrap:wrap;width:285px;height:auto;gap:8px}
        #email,#denomination,#adresse,#siren,#iban,#bic{box-sizing:border-box;width:595px;height:40px;border-radius:8px;border:1px solid #d9d9d9;padding:12px 16px}
        #telephone,#ville,#code_postal{box-sizing:border-box;width:285px;height:40px;border-radius:8px;border:1px solid #d9d9d9;padding:12px 16px}
        #secteur{box-sizing:border-box;width:285px;height:40px;border-radius:8px;border:1px solid #d9d9d9;padding:0 12px;background-color:#fff}
        .ville_code_postal, .siren_secteur {display: flex; gap: 24px; width: 100%;}
        input:read-only, select:disabled{background-color:#e9ecef;color:#6c757d;cursor:not-allowed;border:1px solid #ced4da}
        .buttons-form-profil{display:flex;gap:10px;justify-content:center;margin-top:24px}
        .buttons-form-profil button{padding:10px 20px;font-family:'Poppins',sans-serif;font-size:16px;border-radius:8px;cursor:pointer;border:1px solid transparent}
        #btnModifierProfil{background-color:var(--couleur-principale);color:#fff}
        #btnConfirmerProfil{background-color:rgb(0,140,98);color:#fff}
        #btnAnnulerProfil{background-color:rgb(255,72,90);color:#fff}
        .error-message-server,.error-message-js{color:red;font-size:.9em;text-align:left;width:100%;margin-top:4px}
        .error-message-js{display:none}
        .popup-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,.5);display:flex;justify-content:center;align-items:center;z-index:1000;opacity:0;visibility:hidden;transition:opacity .3s ease,visibility .3s ease}
        .popup-overlay.show{opacity:1;visibility:visible}
        .popup-content{background-color:#fff;padding:30px;border-radius:10px;text-align:center;box-shadow:0 5px 15px rgba(0,0,0,.3);max-width:400px;width:90%;transform:scale(.9);transition:transform .3s ease}
        .popup-overlay.show .popup-content{transform:scale(1)}
        .popup-content h2{margin-top:0;margin-bottom:15px;color:#333}
        .popup-content p{margin-bottom:20px;line-height:1.5;color:#555}
        .popup-content button{padding:10px 25px;border:none;border-radius:5px;cursor:pointer;font-weight:700}
        .popup-content button.success{background-color:var(--couleur-principale);color:#fff}
        .popup-content button.error{background-color:rgb(255,72,90);color:#fff}
        @media (max-width:768px){.profil{width:100%;height:auto;padding:16px}.email,.denomination,.adresse_postal,.siren,.iban,.bic,.telephone,.ville,.secteur,.code_postal{width:100%}#email,#denomination,#adresse,#siren,#iban,#bic,#telephone,#ville,#secteur,#code_postal{width:100%}.ville_code_postal,.siren_secteur{flex-direction:column;gap:24px}h1{font-size:24px}}
    </style>
</head>
<body>
    <header>
    <div class="container header-container">
        <div class="header-left">
            <a href="index.php"><img src="images/Logowithoutbgorange.png" alt="Logo" class="logo"></a>
            <span class="pro-text">Professionnel</span>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="recherche.php">Mes Offres</a></li>
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <div class="profile-link-container">
                <a href="profil.php" class="btn btn-secondary">Mon profil</a>
                <?php if (isset($unanswered_reviews_count) && $unanswered_reviews_count > 0): ?>
                    <span class="notification-bubble"><?php echo $unanswered_reviews_count; ?></span>
                <?php endif; ?>
            </div>
            <a href="/deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

    <main>
        <div class="container content-area">
            <h1>Mes Informations</h1>
            
            <?php if ($pro_user && !empty($pro_user)): ?>
            <form id="profilProForm" method="POST" action="profil.php">
                <input type="hidden" name="actual_adresse_id" value="<?php echo htmlspecialchars($pro_user['actual_adresse_id'] ?? ''); ?>">
                
                <div class="profil">
                    <div class="email">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="john.doe@mail.com" readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['email'] ?? $pro_user['email'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['email'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['email']); ?></span>
                        <?php endif; ?>
                        <span id="emailError" class="error-message-js"></span>
                    </div>

                    <div class="telephone">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" placeholder="+33701020304" readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['telephone'] ?? $pro_user['telephone'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['telephone'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['telephone']); ?></span>
                        <?php endif; ?>
                        <span id="telephoneError" class="error-message-js"></span>
                    </div>

                    <div class="denomination">
                        <label for="denomination">Dénomination / Raison sociale</label>
                        <input type="text" id="denomination" name="denomination" placeholder="Votre Entreprise" readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['denomination'] ?? $pro_user['denomination'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['denomination'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['denomination']); ?></span>
                        <?php endif; ?>
                        <span id="denominationError" class="error-message-js"></span>
                    </div>

                    <div class="adresse_postal">
                        <label for="adresse">Ligne d'adresse</label>
                        <input type="text" id="adresse" name="adresse" placeholder="23 rue saint marc" readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['adresse'] ?? $pro_user['adresse'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['adresse'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['adresse']); ?></span>
                        <?php endif; ?>
                        <span id="adresseError" class="error-message-js"></span>
                    </div>

                    <div class="ville_code_postal">
                        <div class="ville">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" placeholder="Paris" readonly
                                   value="<?php echo htmlspecialchars($submitted_data_from_session['ville'] ?? $pro_user['ville'] ?? ''); ?>">
                            <?php if (isset($validation_errors_from_session['ville'])): ?>
                                <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['ville']); ?></span>
                            <?php endif; ?>
                            <span id="villeError" class="error-message-js"></span>
                        </div>
                        <div class="code_postal">
                            <label for="code_postal">Code postal</label>
                            <input type="text" id="code_postal" name="code_postal" placeholder="75002" readonly
                                   value="<?php echo htmlspecialchars($submitted_data_from_session['code_postal'] ?? $pro_user['code_postal'] ?? ''); ?>">
                            <?php if (isset($validation_errors_from_session['code_postal'])): ?>
                                <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['code_postal']); ?></span>
                            <?php endif; ?>
                            <span id="codePostalError" class="error-message-js"></span>
                        </div>
                    </div>

                    <div class="siren_secteur">
                        <div class="siren" style="width: 285px;">
                             <label for="siren">Numéro de SIREN</label>
                             <input type="text" id="siren" name="siren" placeholder="123456789" readonly
                                    value="<?php echo htmlspecialchars($submitted_data_from_session['siren'] ?? $pro_user['siren'] ?? ''); ?>">
                             <?php if (isset($validation_errors_from_session['siren'])): ?>
                                 <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['siren']); ?></span>
                             <?php endif; ?>
                             <span id="sirenError" class="error-message-js"></span>
                        </div>
                        <div class="secteur">
                            <label for="secteur">Secteur</label>
                            <select name="secteur" id="secteur" disabled>
                                <option value="privé" <?php echo (($submitted_data_from_session['secteur'] ?? ($pro_user['secteur'] == 1 ? 'privé' : 'public')) === 'privé') ? 'selected' : ''; ?>>Privé</option>
                                <option value="public" <?php echo (($submitted_data_from_session['secteur'] ?? ($pro_user['secteur'] == 1 ? 'privé' : 'public')) === 'public') ? 'selected' : ''; ?>>Public</option>
                            </select>
                        </div>
                    </div>

                    <div class="iban">
                        <label for="iban">IBAN</label>
                        <input type="text" id="iban" name="iban" placeholder="FR76..." readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['iban'] ?? $pro_user['iban'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['iban'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['iban']); ?></span>
                        <?php endif; ?>
                        <span id="ibanError" class="error-message-js"></span>
                    </div>

                    <div class="bic">
                        <label for="bic">BIC</label>
                        <input type="text" id="bic" name="bic" placeholder="AGRIFRPPXXX" readonly
                               value="<?php echo htmlspecialchars($submitted_data_from_session['bic'] ?? $pro_user['bic'] ?? ''); ?>">
                        <?php if (isset($validation_errors_from_session['bic'])): ?>
                            <span class="error-message-server"><?php echo htmlspecialchars($validation_errors_from_session['bic']); ?></span>
                        <?php endif; ?>
                        <span id="bicError" class="error-message-js"></span>
                    </div>
                </div>

                <div class="buttons-form-profil">
                    <button type="button" id="btnModifierProfil">Modifier les informations</button>
                    <button type="submit" id="btnConfirmerProfil" style="display:none;">Confirmer</button>
                    <button type="button" id="btnAnnulerProfil" style="display:none;">Annuler</button>
                </div>
            </form>
            <?php else: ?>
                <p>Vos informations de profil n'ont pas pu être chargées. Veuillez contacter le support.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="popupOverlay" class="popup-overlay <?php if ($show_popup) echo 'show'; ?>">
        <div class="popup-content">
            <h2 id="popupTitle"><?php echo ($popup_type === "success") ? "Succès !" : "Erreur !"; ?></h2>
            <p id="popupMessage"><?php echo htmlspecialchars($popup_message); ?></p>
            <button id="popupCloseButton" class="<?php echo $popup_type; ?>">Fermer</button>
        </div>
    </div>

    <div id="emailConfirmationPopup" class="popup-overlay">
        <div class="popup-content">
            <h2>Confirmer le changement d'e-mail ?</h2>
            <p>
                Modifier votre adresse e-mail vous déconnectera par mesure de sécurité. 
                Vous devrez vous reconnecter avec votre nouvelle adresse. <br><br>
                Voulez-vous continuer ?
            </p>
            <div class="buttons-form-profil">
                <button type="button" id="btnConfirmEmailChange" class="success" style="background-color:rgb(0,140,98); color: #fff;">Oui, confirmer</button>
                <button type="button" id="btnCancelEmailChange" class="error" style="background-color:rgb(255,72,90); color: #fff;">Annuler</button>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Visiteur</h3>
                <ul>
                    <li><a href="../index.html">Accueil</a></li>
                    <li><a href="../FO/recherche.php">Recherche d'offres</a></li>
                    <li><a href="../FO/connexion-compte.php">Je me connecte en tant que membre</a></li>
                </ul>
            </div>
            <div class="footer-section links">
                <h3>Découvrir</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="publier-une-offre.php">Publier une offre</a></li>
                    <li><a href="profil.php">Profil</a></li>
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

    <script id="initial-data" type="application/json">
        <?php echo $pro_user_json; ?>
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const profilForm = document.getElementById('profilProForm');
        if (!profilForm) return;

        const btnModifier = document.getElementById('btnModifierProfil');
        const btnConfirmer = document.getElementById('btnConfirmerProfil');
        const btnAnnuler = document.getElementById('btnAnnulerProfil');
        const allInputs = profilForm.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
        const selectSecteur = document.getElementById('secteur');

        const popupOverlay = document.getElementById('popupOverlay');
        const popupCloseButton = document.getElementById('popupCloseButton');

            // --- NOUVEAU : Popup de confirmation d'email ---
        const emailPopup = document.getElementById('emailConfirmationPopup');
        const btnConfirmEmailChange = document.getElementById('btnConfirmEmailChange');
        const btnCancelEmailChange = document.getElementById('btnCancelEmailChange');

        
        let initialValues = {};

        let isEmailChangeConfirmed = false; 

        function loadInitialValuesFromServer() {
            try {
                const initialDataScript = document.getElementById('initial-data');
                if (initialDataScript && initialDataScript.textContent) {
                    const serverData = JSON.parse(initialDataScript.textContent);
                    // On peuple initialValues avec les vraies données du serveur
                    initialValues = {
                        email: serverData.email || '',
                        telephone: serverData.telephone || '',
                        denomination: serverData.denomination || '',
                        adresse: serverData.adresse || '',
                        ville: serverData.ville || '',
                        code_postal: serverData.code_postal || '',
                        siren: serverData.siren || '',
                        // On convertit la valeur numérique du secteur en texte
                        secteur: serverData.secteur == 1 ? 'privé' : 'public',
                        iban: serverData.iban || '',
                        bic: serverData.bic || ''
                    };
                }
            } catch (e) {
                console.error("Impossible de lire ou d'analyser les données initiales du serveur.", e);
            }
        }
        
        // On exécute cette fonction une seule fois au chargement de la page.
        loadInitialValuesFromServer();

        function revertToInitialValues() {
            allInputs.forEach(input => input.value = initialValues[input.id] || '');
            selectSecteur.value = initialValues[selectSecteur.id] || 'privé';
        }
        
        function clearAllJsErrors() {
            document.querySelectorAll('.error-message-js').forEach(span => {
                span.textContent = '';
                span.style.display = 'none';
            });
        }
        
        function enterEditMode() {
            isEmailChangeConfirmed = false;
            allInputs.forEach(input => input.removeAttribute('readonly'));
            selectSecteur.removeAttribute('disabled');
            btnModifier.style.display = 'none';
            btnConfirmer.style.display = 'inline-block';
            btnAnnuler.style.display = 'inline-block';
        }

        function exitEditMode() {
            revertToInitialValues();
            allInputs.forEach(input => input.setAttribute('readonly', 'readonly'));
            selectSecteur.setAttribute('disabled', 'disabled');
            btnModifier.style.display = 'inline-block';
            btnConfirmer.style.display = 'none';
            btnAnnuler.style.display = 'none';
            clearAllJsErrors();
            document.querySelectorAll('.error-message-server').forEach(span => span.style.display = 'none');
        }

        btnModifier.addEventListener('click', enterEditMode);
        btnAnnuler.addEventListener('click', exitEditMode);
        
        // --- Validation Client ---
        profilForm.addEventListener('submit', function(event) {

                    // --- NOUVEAU : Vérification du changement d'email ---
            const initialEmail = initialValues['email'];
            const currentEmail = document.getElementById('email').value;

            // Si l'email a changé ET que l'utilisateur n'a pas encore confirmé via la popup
            if (currentEmail !== initialEmail && !isEmailChangeConfirmed) {
                event.preventDefault(); // Arrêter la soumission du formulaire
                emailPopup.classList.add('show'); // Afficher la popup de confirmation
                return; // Sortir de la fonction pour attendre l'action de l'utilisateur
            }

            let isValid = true;
            clearAllJsErrors();

            function showError(elementId, message) {
                const errorSpan = document.getElementById(elementId);
                errorSpan.textContent = message;
                errorSpan.style.display = 'block';
                isValid = false;
            }

            // Validations
            if (document.getElementById('denomination').value.trim() === '') showError('denominationError', 'La dénomination est requise.');
            if (document.getElementById('adresse').value.trim() === '') showError('adresseError', 'L\'adresse est requise.');
            if (document.getElementById('ville').value.trim() === '') showError('villeError', 'La ville est requise.');
            
            const codePostal = document.getElementById('code_postal').value;
            if (codePostal.trim() === '') showError('codePostalError', 'Le code postal est requis.');
            else if (!/^\d{5}$/.test(codePostal)) showError('codePostalError', 'Le code postal doit contenir 5 chiffres.');

            const siren = document.getElementById('siren').value.replace(/\s/g, '');
            if (siren.trim() === '') showError('sirenError', 'Le numéro de SIREN est requis.');
            else if (!/^\d{9}$/.test(siren)) showError('sirenError', 'Le SIREN doit contenir 9 chiffres.');

            const email = document.getElementById('email').value;
            if (email.trim() === '') showError('emailError', 'L\'email est requis.');
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) showError('emailError', 'Format d\'email invalide.');
            
            // Ajout de la validation pour le numéro de téléphone côté client
            const telephone = document.getElementById('telephone').value;
            if (telephone.trim() !== '') {
                // Vérifie la présence de lettres
                if (/[a-zA-Z]/.test(telephone)) {
                    showError('telephoneError', 'Le téléphone ne doit pas contenir de lettres.');
                // Compte uniquement les chiffres et vérifie la longueur
                } else if (telephone.replace(/[^0-9]/g, '').length < 10) {
                    showError('telephoneError', 'Le téléphone doit contenir au moins 10 chiffres.');
                }
            }

            if (document.getElementById('iban').value.trim() === '') showError('ibanError', 'L\'IBAN est requis.');
            if (document.getElementById('bic').value.trim() === '') showError('bicError', 'Le BIC est requis.');

            if (!isValid) {
                event.preventDefault();
            }
        });

        // --- NOUVEAU : GESTION DES BOUTONS DE LA POPUP D'EMAIL ---
        btnConfirmEmailChange.addEventListener('click', function() {
            isEmailChangeConfirmed = true; // Mettre le drapeau à vrai
            emailPopup.classList.remove('show'); // Cacher la popup
            profilForm.submit(); // Soumettre le formulaire pour de bon
        });

        btnCancelEmailChange.addEventListener('click', function() {
            emailPopup.classList.remove('show'); // Cacher la popup, ne rien faire d'autre
        });

        // --- Gestion de la Pop-up ---
        function hidePopup() {
            if(popupOverlay) popupOverlay.classList.remove('show');
            const url = new URL(window.location.href);
            url.searchParams.delete('update');
            history.replaceState(null, '', url.toString());
        }

        if(popupCloseButton) popupCloseButton.addEventListener('click', hidePopup);
        if(popupOverlay) popupOverlay.addEventListener('click', (event) => {
            if (event.target === popupOverlay) hidePopup();
        });
    });
    </script>
</body>
</html>