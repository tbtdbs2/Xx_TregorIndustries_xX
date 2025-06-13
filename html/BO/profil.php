<?php
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
?>
<?php
// --- AJOUT POUR AFFICHER LA NOTIFICATION ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// --- FIN DE L'AJOUT ---
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
        #btnModifierProfil{background-color:#008c8c;color:#fff}
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
        .popup-content button.success{background-color:#008c8c;color:#fff}
        .popup-content button.error{background-color:rgb(255,72,90);color:#fff}
        @media (max-width:768px){.profil{width:100%;height:auto;padding:16px}.email,.denomination,.adresse_postal,.siren,.iban,.bic,.telephone,.ville,.secteur,.code_postal{width:100%}#email,#denomination,#adresse,#siren,#iban,#bic,#telephone,#ville,#secteur,#code_postal{width:100%}.ville_code_postal,.siren_secteur{flex-direction:column;gap:24px}h1{font-size:24px}}
        /* Add your custom styles here */
        .container.content-area {
            padding: 32px 0px;
            text-align: center; /* centre le texte à l’intérieur */
            display: flex;
            flex-direction: column;
            align-items: center; /* centre le contenu horizontalement */

        }

        input {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            
        }

        .profil {

            box-sizing: border-box;
            border: 1px solid #D9D9D9;
            border-radius: 16px;
            padding: 32px;
            display: flex;
            gap : 24px;
            width: 660px;
            height: 870px;
            flex-wrap: wrap;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        .email, .denomination, .adresse_postal, .siren, .iban, .bic {

            display: flex;
            flex-wrap: wrap;
            width: 595px;
            height: 70px;
            gap: 8px;
        
        }

        .telephone, .ville, .secteur, .code_postal {

            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
        
        }

        #email, #denomination, #adresse, #siren, #iban, #bic {

            box-sizing: border-box;
            width: 595px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;

        }

        #telephone, #ville, #secteur, #code_postal {

            box-sizing: border-box;
            width: 240px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;

        }

        .ville_code_postal{

            display: flex;
            gap: 32px;
            width: 520px;
            height: 70px;


        }
        
        /* --- STYLE POUR LE MESSAGE D'ERREUR --- */
        .message.error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            max-width: 660px;
            margin-left: auto;
            margin-right: auto;
        }
        /* --- FIN STYLE POUR LE MESSAGE D'ERREUR --- */
                
        @media (max-width: 768px) {
    .container.content-area {
        padding: 16px 0px; /* Réduire le padding pour les petits écrans */
    }

    .profil {
        width: 100%; /* Prendre toute la largeur de l'écran */
        height: auto; /* Permettre à la hauteur de s'ajuster automatiquement */
        padding: 16px; /* Réduire le padding */
    }

    .email,
    .denomination,
    .adresse_postal,   
    .siren,
    .iban,
    .bic,
    .telephone,
    .ville,
    .secteur,
    .code_postal {
        width: 100%; /* Prendre toute la largeur de l'écran */
        margin-left: 0; /* Supprimer la marge gauche */
    }

    #email,
    #denomination, 
    #adresse,
    #siren,
    #iban,
    #bic,
    #telephone,
    #ville,
    #secteur,
    #code_postal {
    
        width: 100%; /* Prendre toute la largeur de l'écran */
    }

    h1 {
        font-size: 24px; /* Réduire la taille de la police pour le titre */
    }
}
    /* --- STYLES POUR LA NOTIFICATION PROFIL --- */

    .main-nav ul li.nav-item-with-notification {
        position: relative; /* Contexte pour le positionnement absolu de la bulle */
    }

    .profile-link-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .notification-bubble {
        position: absolute;
        top: -16px;
        right: 80px;
        width: 20px;
        height: 20px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8em;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .header-right .profile-link-container + .btn-primary {
        margin-left: 1rem; 
    }

    .nav-item-with-notification .notification-bubble {
        position: absolute;
        top: -15px; /* Ajustez pour la position verticale */
        right: 80px; /* Ajustez pour la position horizontale */
        width: 20px;
        height: 20px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75em; /* Police un peu plus petite pour la nav */
        font-weight: bold;
        border: 2px solid white;
    }
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
                <li><a href="index.php" class="active">Accueil</a></li>
                <li class="nav-item-with-notification">
                    <a href="recherche.php">Mes Offres</a>
                    <?php if (isset($unanswered_reviews_count) && $unanswered_reviews_count > 0): ?>
                        <span class="notification-bubble"><?php echo $unanswered_reviews_count; ?></span>
                    <?php endif; ?>
                </li>
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <div class="profile-link-container">
                <a href="profil.php" class="btn btn-secondary">Mon profil</a>
            </div>
            <a href="/deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>
    <main>
        <div class="container content-area">

            <?php
            // --- AJOUT POUR AFFICHER LA NOTIFICATION ---
            if (isset($_SESSION['notification_error'])) {
                echo "<div class='message error'>" . htmlspecialchars($_SESSION['notification_error']) . "</div>";
                // On supprime la notification pour qu'elle ne s'affiche pas à nouveau
                unset($_SESSION['notification_error']);
            }
            // --- FIN DE L'AJOUT ---
            ?>
            
            <h1>Mes Informations</h1>
            
            <?php if ($pro_user && !empty($pro_user)): ?>
            <form id="profilProForm" method="POST" action="profil_pro.php">
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
                                 <option value="privé" <?php echo (($submitted_data_from_session['secteur'] ?? $pro_user['secteur'] ?? '') === 'privé') ? 'selected' : ''; ?>>Privé</option>
                                 <option value="public" <?php echo (($submitted_data_from_session['secteur'] ?? $pro_user['secteur'] ?? '') === 'public') ? 'selected' : ''; ?>>Public</option>
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
    
    <footer>
       </footer>

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
        
        let initialValues = {};

        function storeInitialValues() {
            allInputs.forEach(input => initialValues[input.id] = input.value);
            initialValues[selectSecteur.id] = selectSecteur.value;
        }

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
            storeInitialValues();
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
            
            if (document.getElementById('iban').value.trim() === '') showError('ibanError', 'L\'IBAN est requis.');
            if (document.getElementById('bic').value.trim() === '') showError('bicError', 'Le BIC est requis.');

            if (!isValid) {
                event.preventDefault();
            }
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