<?php
// Démarrer la session (doit être au TOUT DÉBUT du fichier)
session_start();

$dsn = 'mysql:host=localhost;dbname=sae;charset=utf8'; 
$username_db = 'root'; 
$password_db = ''; 

$update_message = '';
$validation_errors_from_session = []; // Pour les erreurs de validation serveur
$submitted_data_from_session = []; // Pour repeupler le formulaire après erreur serveur

// Récupérer les erreurs de validation et les données soumises de la session, si elles existent
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
            $pdo_update = new PDO($dsn, $username_db, $password_db);
            $pdo_update->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_update->beginTransaction();

            $sqlAdresse = "UPDATE adresses SET street = :street, city = :city, postal_code = :postal_code WHERE id = :adresse_id_val";
            $stmtAdresse = $pdo_update->prepare($sqlAdresse);
            $stmtAdresse->bindParam(':street', $submitted_data['adresse_postale']);
            $stmtAdresse->bindParam(':city', $submitted_data['ville']);
            $stmtAdresse->bindParam(':postal_code', $submitted_data['code_postal']);
            $stmtAdresse->bindParam(':adresse_id_val', $adresseIdToUpdate);
            $stmtAdresse->execute();

            $sqlMembre = "UPDATE comptes_membre SET alias = :pseudonyme, firstname = :prenom, lastname = :nom, email = :email, phone = :telephone WHERE id = :userId";
            $stmtMembre = $pdo_update->prepare($sqlMembre);
            $stmtMembre->bindParam(':pseudonyme', $submitted_data['pseudonyme']);
            $stmtMembre->bindParam(':prenom', $submitted_data['prenom']);
            $stmtMembre->bindParam(':nom', $submitted_data['nom']);
            $stmtMembre->bindParam(':email', $submitted_data['email']);
            $stmtMembre->bindParam(':telephone', $submitted_data['telephone']);
            $stmtMembre->bindParam(':userId', $userIdToUpdate);
            $stmtMembre->execute();

            $pdo_update->commit();
            header('Location: profil.php?update=success');
            exit;

    } catch (PDOException $e) {
            if (isset($pdo_update) && $pdo_update->inTransaction()) {
                $pdo_update->rollBack();
            }
            error_log("Erreur de mise à jour du profil pour user ID " . $userIdToUpdate . " (Exception attrapée): " . $e->getMessage());
            header('Location: profil.php?update=error'); 
            exit;
    }
}


// Afficher les messages de succès/erreur de la BDD (après redirection)
if (isset($_GET['update'])) {
    if ($_GET['update'] === 'success') {
        $update_message = "<p style='color:green; text-align:center; margin-bottom:15px;'>Vos informations ont été mises à jour avec succès !</p>";
    } elseif ($_GET['update'] === 'error') {
        $update_message = "<p style='color:red; text-align:center; margin-bottom:15px;'>Une erreur technique est survenue lors de la mise à jour. Veuillez réessayer.</p>";
    } elseif ($_GET['update'] === 'error_no_address_id') {
        $update_message = "<p style='color:red; text-align:center; margin-bottom:15px;'>Erreur : Identifiant d'adresse manquant pour la mise à jour.</p>";
    }
}


// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion-compte.php'); 
    exit; 
}

// 2. Récupérer l'identifiant de l'utilisateur connecté
$userId = $_SESSION['user_id'];
$userLoggedIn = true; 

// 3. Interroger la base de données
$membre = null; 
$pdo_select = null; 

try {
    $pdo_select = new PDO($dsn, $username_db, $password_db);
    $pdo_select->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $stmt = $pdo_select->prepare($sql);
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
    <title>PACT - Mon Profil</title><link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .container.content-area {
            padding: 32px 0px;
            text-align: center; 
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
            gap : 24px;
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

        .input_pseudo { width: 270px; }
        .input_prenom { width: 270px; }
        .input_nom { width: 270px; }
        .adresse_postal { width: 580px; margin-left: 20px;}
        .ville { width: 270px; margin-left: 20px;}
        .code_postal { width: 270px; margin-left: 20px;}
        .mail { width: 580px; margin-left: 20px;}
        .telephone { width: 270px; margin-left: 20px;}

        #pseudo, #prenom, #nom, #adresse, #ville, #code_postal, #email, #telephone {
            box-sizing: border-box;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }
        #pseudo, #prenom, #nom, #ville, #code_postal, #telephone { width: 270px; }
        #adresse, #email { width: 580px; }
        
        .nom_prenom { display: flex; gap: 40px; }
        
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
        #btnModifierProfil { background-color: #008C8C; color: white; }
        #btnConfirmerProfil { background-color:rgb(0, 140, 98); color: white; }
        #btnAnnulerProfil { background-color:rgb(255, 72, 90); color: white; }

        .error-message-server, .error-message-js {
            color: red;
            display: block; /* Assure que le message d'erreur serveur s'affiche sur sa propre ligne */
            font-size: 0.9em;
            text-align: left;
            width: 100%;
            margin-top: 4px;
        }
        .error-message-js { /* S'assurer que le JS error span est caché par défaut */
             display: none;
        }


        @media (max-width: 768px) {
            .container.content-area { padding: 16px 0px; }
            .card_section {
                width: 90%; 
                padding: 16px;
                flex-direction: column; 
                align-items: center; 
            }
            .input_pseudo, .input_prenom, .input_nom, .adresse_postal, .ville, .code_postal, .mail, .telephone {
                width: 100%;
                margin-left: 0; 
            }
            #pseudo, #prenom, #nom, #adresse, #ville, #code_postal, #email, #telephone { width: 100%; }
            h1 { font-size: 24px; }
            .grid-card { gap: 16px; }
            .nom_prenom { flex-direction: column; width: 100%; gap: 16px; }
            .header-right .desktop-only { display: none; }
            .mobile-nav-links ul { list-style: none; padding: 0; margin: 0; }
            .mobile-nav-links ul li { padding: 10px 0; text-align: center; }
            .buttons-form-profil { flex-direction: column; align-items: center; }
            .buttons-form-profil button { width: 80%; max-width: 300px; }
        }
    </style>
    
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="header-left">
                <a href="../index.html"><img src="images/Logowithoutbg.png" alt="Logo PACT" class="logo"></a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.html">Accueil</a></li>
                        <li><a href="recherche.php">Recherche</a></li>
                    </ul>
                </nav>
            </div>
            <div class="header-right">
                <a href="../BO/index.php" class="pro-link desktop-only">Je suis professionnel</a>
                <?php if ($userLoggedIn): ?>
                    <a href="profil.php" class="btn btn-secondary desktop-only">Mon Profil</a>
                    <a href="logout.php" class="btn btn-primary desktop-only">Se déconnecter</a> <?php else: ?>
                    <a href="creation-compte.php" class="btn btn-secondary desktop-only">S'enregistrer</a>
                    <a href="connexion-compte.php" class="btn btn-primary desktop-only">Se connecter</a>
                <?php endif; ?>
                
                <div class="mobile-icons">
                    <a href="index.php" class="mobile-icon" aria-label="Accueil"><i class="fas fa-home"></i></a>
                    <a href="profil.php" class="mobile-icon active" aria-label="Profil"><i class="fas fa-user"></i></a>
                    <button class="mobile-icon hamburger-menu" aria-label="Menu" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        <nav class="mobile-nav-links">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="recherche.php">Recherche</a></li>
                <li><a href="../BO/index.php">Je suis professionnel</a></li>
                <?php if ($userLoggedIn): ?>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <li><a href="logout.php">Se déconnecter</a></li>
                <?php else: ?>
                    <li><a href="creation-compte.php">S'enregistrer</a></li>
                    <li><a href="connexion-compte.php">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area">
            <h1>Mes Informations</h1>
            <?php if (!empty($update_message)) echo $update_message; ?>

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
            
        </div>
    </main>

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
                    <li><a href="../index.html">Accueil</a></li>
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

        const fieldValidators = [
            { input: document.getElementById('pseudo'), errorSpan: document.getElementById('pseudoError'), validations: [
                { type: 'required', message: 'Le pseudonyme est requis.'}
            ]},
            { input: document.getElementById('prenom'), errorSpan: document.getElementById('prenomError'), validations: [
                { type: 'required', message: 'Le prénom est requis.'},
                { type: 'format', regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u, message: 'Le prénom contient des caractères non autorisés.'}
            ]},
            { input: document.getElementById('nom'), errorSpan: document.getElementById('nomError'), validations: [
                { type: 'required', message: 'Le nom est requis.'},
                { type: 'format', regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u, message: 'Le nom contient des caractères non autorisés.'}
            ]},
            { input: document.getElementById('adresse'), errorSpan: document.getElementById('adresseError'), validations: [
                { type: 'required', message: 'L\'adresse postale est requise.'}
            ]},
            { input: document.getElementById('ville'), errorSpan: document.getElementById('villeError'), validations: [
                { type: 'required', message: 'La ville est requise.'},
                { type: 'format', regex: /^[a-zA-ZÀ-ÿ\s'-]+$/u, message: 'Le nom de la ville contient des caractères non autorisés.'}
            ]},
            { input: document.getElementById('code_postal'), errorSpan: document.getElementById('codePostalError'), validations: [
                { type: 'required', message: 'Le code postal est requis.'},
                { type: 'format', regex: /^\d{5}$/, message: 'Le code postal doit être composé de 5 chiffres.'}
            ]},
            { input: document.getElementById('email'), errorSpan: document.getElementById('emailError'), validations: [
                { type: 'required', message: 'L\'email est requis.'},
                { type: 'format', regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Le format de l\'email est invalide.'} // Regex simple
            ]},
            { input: document.getElementById('telephone'), errorSpan: document.getElementById('telephoneError'), validations: [
                { type: 'format', regex: /^[^a-zA-ZÀ-ÿ]*$/u, message: 'Le numéro de téléphone ne doit pas contenir de lettres.', checkNonEmpty: true }
            ]}
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
                            if (field.input.id === 'telephone' && testValue !== '' && !validation.regex.test(testValue)){
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

                            if (validation.type === 'required' && testValue === '') { stillError = true; break; }
                            if (validation.type === 'format') {
                                if (validation.checkNonEmpty && testValue === '') {
                                    // OK
                                } else if (testValue !== '' && !validation.regex.test(testValue)) {
                                    stillError = true; break;
                                } else if (!validation.checkNonEmpty && !validation.regex.test(testValue) && testValue !== '') {
                                     stillError = true; break;
                                }
                                // Cas spécifique du téléphone
                                if (field.input.id === 'telephone' && testValue !== '' && !validation.regex.test(testValue)){
                                   stillError = true; break;
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
    });
    </script>
    <script src="script.js" defer></script> 
</body>
</html>