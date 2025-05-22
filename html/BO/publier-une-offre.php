<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier votre offre</title>
    <link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles additionnels pour les champs dynamiques */
        .form-section {
            padding: var(--espacement-double);
            border-bottom: var(--bordure-standard-interface);
        }

        .form-section h2 {
            margin-bottom: var(--espacement-moyen);
            color: var(--couleur-texte);
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: var(--espacement-standard);
            font-weight: var(--font-weight-medium);
        }

        input[type="text"],
        input[type="date"],
        input[type="time"],
        input[type="number"],
        input[type="url"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            padding: var(--espacement-standard);
            border: var(--bordure-standard-interface);
            border-radius: var(--border-radius-bouton);
            font-size: 1em;
            font-family: var(--police-principale);
            margin-bottom: var(--espacement-double);
            background-color: var(--couleur-blanche); 
            box-sizing: border-box; 
            height: auto; 
        }
        
        #categorie {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }


        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group {
            margin-bottom: var(--espacement-double);
        }

        .message {
            padding: var(--espacement-standard);
            margin-bottom: var(--espacement-double);
            border-radius: var(--border-radius-bouton);
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        form {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: var(--espacement-double);
            margin-bottom: var(--espacement-double);
        }

        input.invalid,
        textarea.invalid,
        select.invalid {
            border-color: red;
        }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: -10px; 
            margin-bottom: 10px;
            display: none;
        }

        form button[type="submit"] {
            background-color: orange; 
            color: white;
            width: 80%;
            padding: var(--espacement-moyen);
            margin: var(--espacement-double) auto;
            display: block;
            border: none;
            border-radius: var(--border-radius-bouton);
            font-size: 1.1em;
            cursor: pointer;
        }

        form button[type="submit"]:hover {
            background-color: #e08e0b; 
        }

        #langues-guidees { display: none; }
        #langues-guidees.show { display: block; }
        
        #categorie-specific-fields .dynamic-section-subtitle {
            margin-top: var(--espacement-moyen);
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-texte);
            font-size: 1.1em;
            font-weight: var(--font-weight-semibold);
            padding-bottom: var(--espacement-petit);
        }

        #categorie-specific-fields .item-group {
            position: relative; 
            border: 1px solid var(--couleur-bordure);
            padding: var(--espacement-moyen);
            padding-top: calc(var(--espacement-moyen) + 20px); 
            margin-bottom: var(--espacement-moyen);
            border-radius: var(--border-radius-standard);
            background-color: #f9f9f9;
        }

        #categorie-specific-fields .item-group h3,
        #categorie-specific-fields .item-group h4 {
            margin-top: 0;
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-texte);
        }
        
        .text-add-link {
            background: none !important;
            border: none !important;
            padding: var(--espacement-petit) 0 !important; 
            color: var(--couleur-principale);
            cursor: pointer;
            text-decoration: none; 
            font-size: 0.9em;
            font-weight: var(--font-weight-medium);
            margin-top: var(--espacement-moyen);
            display: inline-block;
        }
        .text-add-link:hover {
            color: var(--couleur-principale-hover);
            text-decoration: underline;
        }

        .remove-icon-cross {
            position: absolute;
            top: 8px;    
            right: 8px;   
            width: 24px;  
            height: 24px; 
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4em; 
            color: #888;    
            cursor: pointer;
            border-radius: 50%;
            line-height: 1;
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .remove-icon-cross:hover {
            color: #333;
            background-color: rgba(0,0,0,0.05); 
        }
        
        #categorie-specific-fields .horaire-group-inputs {
            display: flex;
            flex-wrap: wrap; 
            gap: var(--espacement-standard);
            align-items: flex-end; 
            margin-bottom: var(--espacement-petit);
        }
        #categorie-specific-fields .horaire-group-inputs > div { 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-width: 120px; 
        }
        #categorie-specific-fields .horaire-group-inputs label {
            margin-bottom: var(--espacement-petit); 
            font-size: 0.9em;
        }
        #categorie-specific-fields .horaire-group-inputs input[type="time"],
        #categorie-specific-fields .horaire-group-inputs input[type="date"] {
            margin-bottom: 0; 
            padding: var(--espacement-standard); 
        }
        
        #categorie-specific-fields .attraction-group h3 { 
             margin-top: 0;
             margin-bottom: var(--espacement-standard);
             color: var(--couleur-primaire); 
        }

        .data-display {
            margin-top: var(--espacement-double);
            padding: var(--espacement-standard);
            border: 1px solid #ddd;
            border-radius: var(--border-radius-standard);
            background-color: #f9f9f9;
        }
        .data-display h3 { margin-top: 0; margin-bottom: var(--espacement-moyen); color: var(--couleur-primaire); }
        .data-display p { margin-bottom: var(--espacement-standard); }
        .data-display ul { list-style-type: none; padding-left: 0; }
        .data-display li { margin-bottom: var(--espacement-petit); }

        #image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: var(--espacement-standard);
            margin-top: var(--espacement-standard);
            margin-bottom: var(--espacement-double); 
        }
        #image-preview-container .preview-item {
            position: relative;
            width: 120px; 
            height: 120px;
            border: 1px solid var(--couleur-bordure);
            border-radius: var(--border-radius-petit);
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f9f9f9;
        }
        #image-preview-container .preview-image {
            max-width: 100%;
            max-height: 100%;
            display: block;
            cursor: pointer;
        }
        #image-preview-container .delete-image-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 22px;
            height: 22px;
            background-color: rgba(0,0,0,0.6);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            line-height: 22px;
            text-align: center;
            transition: background-color 0.2s ease;
        }
        #image-preview-container .delete-image-btn:hover {
            background-color: rgba(220, 53, 69, 0.9); 
        }
        
        #image-modal {
            display: none; 
            position: fixed;
            z-index: 1050; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.85);
            justify-content: center; 
            align-items: center; 
            padding: var(--espacement-double); 
            box-sizing: border-box;
        }
        #image-modal.show-modal {
             display: flex;
        }
        #modal-image-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh; 
            border-radius: var(--border-radius-standard);
        }
        #close-modal {
            position: absolute;
            top: var(--espacement-moyen);
            right: var(--espacement-double);
            color: #f1f1f1;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        #close-modal:hover {
            color: var(--couleur-bordure);
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
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="recherche.php">Offres</a></li>
                    <li><a href="publier-une-offre.php" class="active">Publier une offre</a></li>
                    <li><a href="profil.php">Profil</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="creation-compte.php" class="btn btn-secondary">S'enregistrer</a>
                <a href="connexion-compte.php" class="btn btn-primary">Se connecter</a>
            </div>
        </div>
    </header>

    <main>
        <div class="container content-area">
            <h1 style="text-align: center;">Publier votre offre</h1>
            <h2 style="text-align: center;">Dites-nous en plus</h2>
            <form id="offer-form" method="post" enctype="multipart/form-data" novalidate>
                <div class="form-section">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" required>
                    <div class="error-message">Veuillez entrer un titre pour votre offre.</div>

                    <label for="prix">Prix *</label>
                    <input type="text" id="prix" name="prix" pattern="^\d+(\.\d{1,2})?$" required>
                    <div class="error-message">Veuillez entrer un prix valide (ex: 10, 10.50).</div>

                    <label for="coordonnees_telephoniques">Coordonnées téléphoniques</label>
                    <input type="text" id="coordonnees_telephoniques" name="coordonnees_telephoniques"
                        pattern="^0[1-9]\d{8}$">
                    <div class="error-message">Veuillez entrer un numéro de téléphone à 10 chiffres commençant par 0.</div>

                    <label for="resume">Résumé *</label>
                    <textarea id="resume" name="resume" required></textarea>
                    <div class="error-message">Veuillez entrer un résumé de votre offre.</div>

                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>

                    <label for="conditions_accessibilite">Conditions d'accessibilité *</label>
                    <textarea id="conditions_accessibilite" name="conditions_accessibilite" required></textarea>
                    <div class="error-message">Veuillez entrer les conditions d'accessibilité.</div>

                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="1">Activité</option>
                        <option value="2">Visite</option>
                        <option value="3">Spectacle</option>
                        <option value="4">Parc d'attraction</option>
                        <option value="5">Restaurant</option>
                    </select>
                    <div class="error-message">Veuillez sélectionner une catégorie.</div>
                </div>

                <div id="categorie-specific-fields" class="form-section">
                    </div>
                
                <div class="form-section" id="visite-guidee-section" style="display: none;">
                    <label>
                        <input type="checkbox" id="visite_guidee" name="visite_guidee" style="width:auto; margin-right: 5px;"> La visite est guidée
                    </label>
                    <div id="langues-guidees" class="form-group">
                        <label for="langues">Langues proposées *</label>
                        <select id="langues" name="langues[]" multiple> 
                            <option value="fr">Français</option>
                            <option value="en">Anglais</option>
                            <option value="es">Espagnol</option>
                            <option value="de">Allemand</option>
                            <option value="it">Italien</option>
                            <option value="zh">Chinois</option>
                            <option value="ja">Japonais</option>
                            <option value="ru">Russe</option>
                            <option value="pt">Portugais</option>
                            <option value="ar">Arabe</option>
                        </select>
                        <div class="error-message">Veuillez sélectionner au moins une langue si la visite est guidée.</div>
                    </div>
                </div>

                <div class="form-section">
                    <div id="main-date-input-container"> 
                        <label for="date">Date de l'offre *</label>
                        <input type="date" id="date" name="date" required>
                        <div class="error-message">Veuillez entrer la date de l'offre.</div>
                    </div>

                    <label for="site">Site</label>
                    <input type="url" id="site" name="site" placeholder="https://www.example.com">
                    <div class="error-message">Veuillez entrer une URL valide.</div>

                    <label for="ligne_adresse">Ligne d'adresse *</label>
                    <input type="text" id="ligne_adresse" name="ligne_adresse" required>
                    <div class="error-message">Veuillez entrer la ligne d'adresse.</div>

                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required>
                    <div class="error-message">Veuillez entrer la ville.</div>

                    <label for="code_postal">Code postal *</label>
                    <input type="text" id="code_postal" name="code_postal" pattern="^\d{5}$" required>
                    <div class="error-message">Veuillez entrer un code postal à 5 chiffres.</div>

                    <label for="photos">Ajoutez jusqu'à 6 photos (minimum 1) *</label>
                    <input type="file" id="photos" name="photos[]" multiple accept="image/*" required>
                    <div class="error-message" id="photos-error-message">Veuillez ajouter au moins une photo (jusqu'à 6).</div>
                    <div id="image-preview-container">
                        </div>

                    <label>
                        <input type="checkbox" name="mettre_a_la_une" style="width:auto; margin-right: 5px;"> Je souhaite mettre mon offre à la une
                        (fonctionnalité payante)
                    </label>
                    <label>
                        <input type="checkbox" name="offre_special" style="width:auto; margin-right: 5px;"> Je souhaite mettre mon offre en avant
                        (fonctionnalité payante)
                    </label>
                    <button type="submit">Publier mon annonce</button>
                </div>
            </form>

            <div id="image-modal">
                <span id="close-modal" title="Fermer">&times;</span>
                <img id="modal-image-content" src="#" alt="Image agrandie">
            </div>

            <?php
            // ... (PHP code inchangé par rapport à la version précédente pour la gestion des données POST) ...
             if ($_SERVER["REQUEST_METHOD"] == "POST") {
                function validate_input($data)
                {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }
            
                $erreurs = array();
                $display_data = "<div class='data-display'><h3>Données de l'offre :</h3>";
            
                 if (empty($_POST["titre"])) {
                    $erreurs["titre"] = "Veuillez entrer un titre.";
                } else {
                    $titre = validate_input($_POST["titre"]);
                    $display_data .= "<p><strong>Titre:</strong> " . htmlspecialchars($titre) . "</p>";
                }
                // ... (Autres validations de base inchangées) ...
                if (empty($_POST["categorie"])) {
                    $erreurs["categorie"] = "Veuillez sélectionner une catégorie.";
                } else {
                    $categorie = validate_input($_POST["categorie"]);
                    $display_data .= "<p><strong>Catégorie ID:</strong> " . htmlspecialchars($categorie) . "</p>"; 
                }

                // Mise à jour de la condition pour la date principale
                $isDateHandledByCategory = ($categorie == "1" || $categorie == "3" || $categorie == "4");
                if (!$isDateHandledByCategory) {
                    if (empty($_POST["date"])) {
                        $erreurs["date"] = "Veuillez entrer une date pour l'offre.";
                    } else {
                        $date_offre_principale = validate_input($_POST["date"]);
                        $display_data .= "<p><strong>Date de l'offre (principale):</strong> " . htmlspecialchars($date_offre_principale) . "</p>";
                    }
                }
                // ... (Autres validations de base inchangées pour adresse, photos etc.) ...

                // La validation des photos côté serveur doit être adaptée pour gérer `currentSelectedFiles` si cette info est transmise
                // Pour l'instant, elle se base sur ce que le navigateur envoie via $_FILES.
                if (isset($_FILES["photos"])) {
                     $total_photos_uploaded = 0;
                    if(is_array($_FILES["photos"]["name"])){ 
                        foreach($_FILES["photos"]["name"] as $filename) {
                            if(!empty($filename)) {
                                $total_photos_uploaded++;
                            }
                        }
                    }

                    if ($total_photos_uploaded === 0 && photosInput.required) { // photosInput.required est JS, il faudrait une logique serveur
                        $erreurs["photos"] = "Veuillez ajouter au moins une photo.";
                    } elseif ($total_photos_uploaded > 6) {
                        $erreurs["photos"] = "Vous ne pouvez télécharger que 6 photos maximum.";
                    } else {
                        if ($total_photos_uploaded > 0) {
                             $display_data .= "<p><strong>Photos:</strong> " . $total_photos_uploaded . " fichier(s) reçu(s).</p>";
                        }
                    }
                } else if (photosInput.required) { // Idem, logique JS
                     $erreurs["photos"] = "Veuillez ajouter au moins une photo.";
                }


                // Traitement des champs spécifiques (à adapter LARGEMENT côté serveur)
                switch ($categorie) {
                    case "1": // Activité (inchangé par rapport à la dernière modif majeure)
                        // ...
                        break;
                    case "2": // Visite - NOUVEAUX CHAMPS
                        if (empty($_POST["duree"])) { $erreurs["duree_visite"] = "Durée visite requise."; } 
                        else { $display_data .= "<p><strong>Durée Vis.:</strong> " . validate_input($_POST["duree"]) . " h</p>"; }
                        
                        if (!empty($_POST["prix_minimum_visite"])) {
                            if (!is_numeric($_POST["prix_minimum_visite"]) || $_POST["prix_minimum_visite"] < 0) { $erreurs["prix_minimum_visite"] = "Prix minimum visite invalide."; }
                            else { $display_data .= "<p><strong>Prix Min. Visite:</strong> " . validate_input($_POST["prix_minimum_visite"]) . "</p>"; }
                        }
                        if (empty($_POST["heure_debut_visite"])) { $erreurs["heure_debut_visite"] = "Heure de début visite requise."; }
                        else { $display_data .= "<p><strong>Heure Début Visite:</strong> " . validate_input($_POST["heure_debut_visite"]) . "</p>"; }

                        if(isset($_POST["visite_guidee"]) && empty($_POST["langues"])){ $erreurs["langues_visite"] = "Veuillez sélectionner au moins une langue pour la visite guidée."; } 
                        elseif (isset($_POST["langues"])) { $display_data .= "<p><strong>Langues guidées:</strong> " . implode(', ', array_map('htmlspecialchars', $_POST["langues"])) . "</p>"; }
                        break;

                    case "3": // Spectacle - CHAMPS MODIFIÉS
                        if (empty($_POST["duree_spectacle"])) { $erreurs["duree_spectacle"] = "Durée spectacle requise."; }
                        else { $display_data .= "<p><strong>Durée Spectacle:</strong> " . validate_input($_POST["duree_spectacle"]) . " min</p>"; }

                        if (!empty($_POST["prix_minimum_spectacle"])) {
                            if (!is_numeric($_POST["prix_minimum_spectacle"]) || $_POST["prix_minimum_spectacle"] < 0) { $erreurs["prix_minimum_spectacle"] = "Prix minimum spectacle invalide."; }
                            else { $display_data .= "<p><strong>Prix Min. Spectacle:</strong> " . validate_input($_POST["prix_minimum_spectacle"]) . "</p>"; }
                        }
                        if (empty($_POST["date_spectacle"])) { $erreurs["date_spectacle"] = "Date spectacle requise."; }
                        else { $display_data .= "<p><strong>Date Spectacle:</strong> " . validate_input($_POST["date_spectacle"]) . "</p>"; }

                        if (empty($_POST["heure_debut_spectacle"])) { $erreurs["heure_debut_spectacle"] = "Heure de début spectacle requise."; }
                        else { $display_data .= "<p><strong>Heure Début Spectacle:</strong> " . validate_input($_POST["heure_debut_spectacle"]) . "</p>"; }

                        if (empty($_POST["capacite_spectacle"])) { $erreurs["capacite_spectacle"] = "Capacité spectacle requise."; }
                        elseif (!is_numeric($_POST["capacite_spectacle"]) || $_POST["capacite_spectacle"] < 1) { $erreurs["capacite_spectacle"] = "Capacité spectacle invalide."; }
                        else { $display_data .= "<p><strong>Capacité Spectacle:</strong> " . validate_input($_POST["capacite_spectacle"]) . "</p>"; }
                        break;

                    case "4": // Parc d'attraction - NOUVEAUX CHAMPS
                        if (!empty($_POST["prix_minimum_parc"])) {
                            if (!is_numeric($_POST["prix_minimum_parc"]) || $_POST["prix_minimum_parc"] < 0) { $erreurs["prix_minimum_parc"] = "Prix minimum parc invalide."; }
                            else { $display_data .= "<p><strong>Prix Min. Parc:</strong> " . validate_input($_POST["prix_minimum_parc"]) . "</p>"; }
                        }
                        if (!empty($_POST["age_requis_parc"])) {
                            if (!is_numeric($_POST["age_requis_parc"]) || $_POST["age_requis_parc"] < 0) { $erreurs["age_requis_parc"] = "Âge requis parc invalide."; }
                            else { $display_data .= "<p><strong>Âge Requis Parc:</strong> " . validate_input($_POST["age_requis_parc"]) . " ans</p>"; }
                        }
                         if (!empty($_POST["nombre_total_attractions_parc"])) {
                            if (!is_numeric($_POST["nombre_total_attractions_parc"]) || $_POST["nombre_total_attractions_parc"] < 1) { $erreurs["nombre_total_attractions_parc"] = "Nombre d'attractions invalide."; }
                            else { $display_data .= "<p><strong>Nb Attractions Estimé:</strong> " . validate_input($_POST["nombre_total_attractions_parc"]) . "</p>"; }
                        }
                        if (!empty($_POST["maps_url_parc"])) {
                            if (!filter_var($_POST["maps_url_parc"], FILTER_VALIDATE_URL)) { $erreurs["maps_url_parc"] = "URL du plan invalide."; }
                            else { $display_data .= "<p><strong>Plan du Parc:</strong> " . htmlspecialchars(validate_input($_POST["maps_url_parc"])) . "</p>"; }
                        }
                        // La gestion des attractions et horaires dynamiques reste inchangée côté serveur pour l'instant.
                        break;

                    case "5": // Restaurant - NOUVEAUX CHAMPS
                         if (!empty($_POST["lien_menu_restaurant"])) {
                            if (!filter_var($_POST["lien_menu_restaurant"], FILTER_VALIDATE_URL)) { $erreurs["lien_menu_restaurant"] = "URL du menu invalide."; }
                            else { $display_data .= "<p><strong>Lien Menu:</strong> " . htmlspecialchars(validate_input($_POST["lien_menu_restaurant"])) . "</p>"; }
                        }
                        if (!empty($_POST["prix_moyen_restaurant"])) {
                            if (!is_numeric($_POST["prix_moyen_restaurant"]) || $_POST["prix_moyen_restaurant"] < 0) { $erreurs["prix_moyen_restaurant"] = "Prix moyen invalide."; }
                            else { $display_data .= "<p><strong>Prix Moyen:</strong> " . validate_input($_POST["prix_moyen_restaurant"]) . " €</p>"; }
                        }
                        // La validation du nombre de plats (max 5) est gérée côté client, mais peut être ajoutée ici pour la robustesse.
                        if (isset($_POST['plats']) && is_array($_POST['plats'])) {
                            if (count($_POST['plats']) > 5) {
                                $erreurs["plats_restaurant_nombre"] = "Vous ne pouvez pas ajouter plus de 5 plats.";
                            }
                            // ... (traitement des plats existant)
                        }
                        break;
                }
            
                if (!empty($erreurs)) {
                    echo "<div class='error message'>";
                    echo "<strong>Veuillez corriger les erreurs suivantes :</strong><ul>";
                    foreach ($erreurs as $erreur) {
                        echo "<li>$erreur</li>";
                    }
                    echo "</ul></div>";
                } else {
                    echo $display_data . "</div>"; 
                    echo "<div class='success message'>Votre offre a été publiée avec succès (simulation) !</div>";
                }
            }
            ?>
        </div>
    </main>
    <footer>
        <div class="container footer-content">
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" aria-label="X"><i class="fab fa-x"></i></a>
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
    <script>
        const categorieSelect = document.getElementById('categorie');
        const categorieSpecificFields = document.getElementById('categorie-specific-fields');
        const visiteGuideeSection = document.getElementById('visite-guidee-section');
        const languesGuideesDiv = document.getElementById('langues-guidees');
        const languesSelect = document.getElementById('langues');
        const offerForm = document.getElementById('offer-form');
        const mainDateInputContainer = document.getElementById('main-date-input-container');
        const mainDateInput = document.getElementById('date');

        const photosInput = document.getElementById('photos');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imageModal = document.getElementById('image-modal');
        const modalImageContent = document.getElementById('modal-image-content');
        const closeModalButton = document.getElementById('close-modal');
        let currentSelectedFiles = []; 

        function fetchCategorieFields(categorieId) {
            let htmlContent = '';
            
            // MAJ de la condition pour masquer/afficher la date principale
            if (categorieId === '1' || categorieId === '3' || categorieId === '4') { 
                if(mainDateInputContainer) mainDateInputContainer.style.display = 'none';
                if(mainDateInput) mainDateInput.required = false;
            } else {
                if(mainDateInputContainer) mainDateInputContainer.style.display = 'block';
                if(mainDateInput) mainDateInput.required = true;
            }

            if (categorieId === '1') { // Activité (inchangée)
                htmlContent = `
                    <label for="duree_activite">Durée de l'activité (en minutes) *</label>
                    <input type="number" id="duree_activite" name="duree" required min="1">
                    <div class="error-message">Veuillez entrer la durée de l'activité.</div>
                    <label for="prix_minimum_activite">Prix minimum (optionnel)</label>
                    <input type="number" id="prix_minimum_activite" name="prix_minimum_activite" step="0.01" min="0" placeholder="Ex: 10.50">
                    <div class="error-message">Veuillez entrer un prix valide.</div>
                    <label for="age_requis_activite">Âge requis (optionnel)</label>
                    <input type="number" id="age_requis_activite" name="age_requis_activite" min="0" placeholder="Ex: 6">
                    <div class="error-message">Veuillez entrer un âge valide.</div>
                    <div id="horaires-container-activite" class="form-group">
                        <h4 class="dynamic-section-subtitle">Horaires de l'activité :</h4>
                        <div class="item-group horaire-group">
                            <div class="horaire-group-inputs">
                                <div><label for="activites_0_horaires_0_date">Date *</label><input type="date" id="activites_0_horaires_0_date" name="activites[0][horaires][0][date]" required><div class="error-message">Date requise.</div></div>
                                <div><label for="activites_0_horaires_0_heure_debut">Début *</label><input type="time" id="activites_0_horaires_0_heure_debut" name="activites[0][horaires][0][heure_debut]" required><div class="error-message">Heure de début requise.</div></div>
                                <div><label for="activites_0_horaires_0_heure_fin">Fin *</label><input type="time" id="activites_0_horaires_0_heure_fin" name="activites[0][horaires][0][heure_fin]" required><div class="error-message">Heure de fin requise.</div></div>
                            </div>
                        </div>
                        <a href="#" role="button" class="text-add-link add-horaire-activite">+ Ajouter un horaire</a>
                    </div>
                    <div id="services-container-activite" class="form-group">
                        <h4 class="dynamic-section-subtitle">Services proposés :</h4>
                        <div class="item-group service-group">
                            <input type="text" name="activites[0][services][0][nom_service]" placeholder="Nom du service (ex: Wifi gratuit)">
                             <label style="display: inline-flex; align-items: center; margin-top: var(--espacement-petit); margin-bottom: var(--espacement-standard);">
                                <input type="checkbox" name="activites[0][services][0][inclusion]" style="width: auto; margin-right: var(--espacement-petit);"> Inclus dans le prix
                            </label>
                        </div>
                        <a href="#" role="button" id="add-service-activite" class="text-add-link">+ Ajouter un service</a>
                    </div>
                `;
            } else if (categorieId === '2') { // Visite - MODIFIÉ
                htmlContent = `
                    <label for="duree_visite">Durée de la visite (en heures) *</label>
                    <input type="number" id="duree_visite" name="duree" required step="0.5" min="0.5">
                    <div class="error-message">Veuillez entrer la durée de la visite.</div>

                    <label for="prix_minimum_visite">Prix minimum (optionnel)</label>
                    <input type="number" id="prix_minimum_visite" name="prix_minimum_visite" step="0.01" min="0" placeholder="Ex: 5.00">
                    <div class="error-message">Veuillez entrer un prix valide.</div>

                    <label for="heure_debut_visite">Heure de début *</label>
                    <input type="time" id="heure_debut_visite" name="heure_debut_visite" required>
                    <div class="error-message">Veuillez entrer une heure de début.</div>
                `;
            } else if (categorieId === '3') { // Spectacle - MODIFIÉ
                htmlContent = `
                    <label for="duree_spectacle">Durée du spectacle (en minutes) *</label>
                    <input type="number" id="duree_spectacle" name="duree_spectacle" required min="1">
                    <div class="error-message">Veuillez entrer la durée du spectacle.</div>

                    <label for="prix_minimum_spectacle">Prix minimum (optionnel)</label>
                    <input type="number" id="prix_minimum_spectacle" name="prix_minimum_spectacle" step="0.01" min="0" placeholder="Ex: 15.00">
                    <div class="error-message">Veuillez entrer un prix valide.</div>
                    
                    <label for="date_spectacle">Date du spectacle *</label>
                    <input type="date" id="date_spectacle" name="date_spectacle" required>
                    <div class="error-message">Veuillez entrer la date du spectacle.</div>

                    <label for="heure_debut_spectacle">Heure de début *</label>
                    <input type="time" id="heure_debut_spectacle" name="heure_debut_spectacle" required>
                    <div class="error-message">Veuillez entrer l'heure de début du spectacle.</div>

                    <label for="capacite_spectacle">Capacité (nombre de places) *</label>
                    <input type="number" id="capacite_spectacle" name="capacite_spectacle" required min="1">
                    <div class="error-message">Veuillez entrer la capacité du spectacle.</div>
                `;
            } else if (categorieId === '4') { // Parc d'attraction - MODIFIÉ
                htmlContent = `
                    <label for="prix_minimum_parc">Prix minimum d'entrée (optionnel)</label>
                    <input type="number" id="prix_minimum_parc" name="prix_minimum_parc" step="0.01" min="0" placeholder="Ex: 20.00">
                    <div class="error-message">Veuillez entrer un prix valide.</div>

                    <label for="age_requis_parc">Âge requis (optionnel)</label>
                    <input type="number" id="age_requis_parc" name="age_requis_parc" min="0" placeholder="Ex: 3">
                    <div class="error-message">Veuillez entrer un âge valide.</div>
                    
                    <label for="nombre_total_attractions_parc">Nombre total d'attractions estimé</label>
                    <input type="number" id="nombre_total_attractions_parc" name="nombre_total_attractions_parc" min="1" placeholder="Ex: 25">
                    <div class="error-message">Veuillez entrer un nombre valide.</div>

                    <label for="maps_url_parc">Lien vers le plan du parc (URL)</label>
                    <input type="url" id="maps_url_parc" name="maps_url_parc" placeholder="https://example.com/plan-du-parc">
                    <div class="error-message">Veuillez entrer une URL valide.</div>

                    <div id="attractions-container"> <h4 class="dynamic-section-subtitle" style="margin-top:var(--espacement-double);">Attractions spécifiques :</h4>
                        <div class="item-group attraction-group">
                             <h3>Attraction 1</h3>
                            <label for="attractions_0_nom_attraction">Nom de l'attraction *</label>
                            <input type="text" id="attractions_0_nom_attraction" name="attractions[0][nom_attraction]" required>
                            <div class="error-message">Veuillez entrer le nom de l'attraction.</div>
                            <div class="horaires-container-attraction form-group">
                                <h4 class="dynamic-section-subtitle">Horaires de cette attraction :</h4>
                                <div class="item-group horaire-group">
                                     <div class="horaire-group-inputs">
                                        <div><label for="attractions_0_horaires_0_date">Date *</label><input type="date" id="attractions_0_horaires_0_date" name="attractions[0][horaires][0][date]" required><div class="error-message">Date requise.</div></div>
                                        <div><label for="attractions_0_horaires_0_heure_debut">Début *</label><input type="time" id="attractions_0_horaires_0_heure_debut" name="attractions[0][horaires][0][heure_debut]" required><div class="error-message">Heure de début requise.</div></div>
                                        <div><label for="attractions_0_horaires_0_heure_fin">Fin *</label><input type="time" id="attractions_0_horaires_0_heure_fin" name="attractions[0][horaires][0][heure_fin]" required><div class="error-message">Heure de fin requise.</div></div>
                                    </div>
                                </div>
                                <a href="#" role="button" class="text-add-link add-horaire-parc-attraction">+ Ajouter horaire à cette attraction</a>
                            </div>
                        </div>
                        <a href="#" role="button" id="add-attraction-parc" class="text-add-link">+ Ajouter une attraction spécifique</a>
                    </div>
                `;
            } else if (categorieId === '5') { // Restaurant - MODIFIÉ
                htmlContent = `
                    <label for="lien_menu_restaurant">Lien vers le menu (URL)</label>
                    <input type="url" id="lien_menu_restaurant" name="lien_menu_restaurant" placeholder="https://example.com/menu">
                    <div class="error-message">Veuillez entrer une URL valide pour le menu.</div>

                    <label for="prix_moyen_restaurant">Prix moyen par personne (€)</label>
                    <input type="number" id="prix_moyen_restaurant" name="prix_moyen_restaurant" step="0.01" min="0" placeholder="Ex: 25.50">
                    <div class="error-message">Veuillez entrer un prix moyen valide.</div>

                    <div id="plats-container-restaurant" class="form-group">
                        <h4 class="dynamic-section-subtitle">Plats proposés (Max 5) :</h4>
                        <div class="item-group plat-group">
                            <input type="text" name="plats[]" placeholder="Nom du plat" required>
                            <div class="error-message">Le nom du plat est requis.</div>
                        </div>
                        <a href="#" role="button" id="add-plat-restaurant" class="text-add-link">+ Ajouter un plat</a>
                    </div>
                `;
            }
            categorieSpecificFields.innerHTML = htmlContent;

            if (categorieId === '2') {
                visiteGuideeSection.style.display = 'block';
            } else {
                visiteGuideeSection.style.display = 'none';
            }
            initializeDynamicEventListeners();
        }

        function initializeDynamicEventListeners() { 
            const visiteGuideeCheckbox = document.getElementById('visite_guidee');
            if (visiteGuideeCheckbox) {
                visiteGuideeCheckbox.addEventListener('change', (e) => {
                    languesGuideesDiv.style.display = e.target.checked ? 'block' : 'none';
                    languesSelect.required = e.target.checked;
                    if (!e.target.checked) { 
                        languesSelect.classList.remove('invalid');
                        const errorMsgContainer = languesSelect.closest('.form-group');
                        if (errorMsgContainer){
                             const errorMsg = errorMsgContainer.querySelector('.error-message');
                             if(errorMsg) errorMsg.style.display = 'none';
                        }
                    } else if (e.target.checked && offerForm.dataset.submitted === 'true') { 
                        validateField(languesSelect);
                    }
                });
            }

            // Recréer les listeners pour les boutons "Ajouter" après chaque changement de catégorie
            // Pour éviter les listeners multiples, on clone et remplace, ou on utilise la délégation d'événements plus tard si besoin
            document.querySelectorAll('.add-horaire-activite').forEach(btn => {const n = btn.cloneNode(true); btn.parentNode.replaceChild(n,btn); n.addEventListener('click', addHoraireActivite);});
            document.querySelectorAll('#add-service-activite').forEach(btn => {const n = btn.cloneNode(true); btn.parentNode.replaceChild(n,btn); n.addEventListener('click', addServiceActivite);});
            document.querySelectorAll('#add-attraction-parc').forEach(btn => {const n = btn.cloneNode(true); btn.parentNode.replaceChild(n,btn); n.addEventListener('click', addAttractionParc);});
            initializeAddHoraireParcAttractionButtons();
            document.querySelectorAll('#add-plat-restaurant').forEach(btn => {const n = btn.cloneNode(true); btn.parentNode.replaceChild(n,btn); n.addEventListener('click', addPlatRestaurant);});
            
            categorieSpecificFields.querySelectorAll('.item-group').forEach(group => addRemoveCrossIfNeeded(group));
            initializeRemoveButtons(); 
        }
        
        function createRemoveCross() {
            const cross = document.createElement('span');
            cross.className = 'remove-icon-cross remove-element';
            cross.innerHTML = '&times;'; 
            cross.setAttribute('role', 'button');
            cross.setAttribute('aria-label', 'Supprimer');
            return cross;
        }
        
        function addRemoveCrossIfNeeded(groupElement) {
            if (!groupElement.querySelector('.remove-icon-cross')) {
                const cross = createRemoveCross();
                if (groupElement.classList.contains('attraction-group') && 
                    groupElement.parentElement.id === 'attractions-container' &&
                    groupElement.parentElement.querySelectorAll('.attraction-group').length === 1 &&
                    groupElement === groupElement.parentElement.querySelector('.attraction-group')) {
                     cross.style.display = 'none'; 
                }
                groupElement.insertBefore(cross, groupElement.firstChild); 
            }
        }

        function addHoraireActivite(event) {
            event.preventDefault();
            const container = document.getElementById('horaires-container-activite');
            const addButtonLink = container.querySelector('.add-horaire-activite');
            const count = container.querySelectorAll('.horaire-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group horaire-group';
            addRemoveCrossIfNeeded(newGroup); 
            const idPrefix = `activites_0_horaires_${count}`;
            newGroup.innerHTML += `
                <div class="horaire-group-inputs">
                    <div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="activites[0][horaires][${count}][date]" required><div class="error-message">Date requise.</div></div>
                    <div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="activites[0][horaires][${count}][heure_debut]" required><div class="error-message">Heure de début requise.</div></div>
                    <div><label for="${idPrefix}_heure_fin">Fin *</label><input type="time" id="${idPrefix}_heure_fin" name="activites[0][horaires][${count}][heure_fin]" required><div class="error-message">Heure de fin requise.</div></div>
                </div>
            `;
            container.insertBefore(newGroup, addButtonLink);
            initializeRemoveButtons(); 
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        function addServiceActivite(event) {
            event.preventDefault();
            const container = document.getElementById('services-container-activite');
            const addButtonLink = container.querySelector('#add-service-activite');
            const count = container.querySelectorAll('.service-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group service-group';
            addRemoveCrossIfNeeded(newGroup);
            newGroup.innerHTML += `
                <input type="text" name="activites[0][services][${count}][nom_service]" placeholder="Nom du service ${count + 1}">
                <label style="display: inline-flex; align-items: center; margin-top: var(--espacement-petit); margin-bottom: var(--espacement-standard);">
                    <input type="checkbox" name="activites[0][services][${count}][inclusion]" style="width: auto; margin-right: var(--espacement-petit);"> Inclus dans le prix
                </label>
            `;
            container.insertBefore(newGroup, addButtonLink);
            const firstGroup = container.querySelector('.service-group');
            if (firstGroup && !firstGroup.querySelector('.remove-icon-cross') && container.querySelectorAll('.service-group').length > 1) {
                 addRemoveCrossIfNeeded(firstGroup);
            }
            initializeRemoveButtons();
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        function addAttractionParc(event) {
            event.preventDefault();
            const container = document.getElementById('attractions-container');
            const addButtonLink = container.querySelector('#add-attraction-parc');
            const count = container.querySelectorAll('.attraction-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group attraction-group';
            addRemoveCrossIfNeeded(newGroup); 
            const idPrefix = `attractions_${count}`;
            newGroup.innerHTML += `
                <h3>Attraction ${count + 1}</h3>
                <label for="${idPrefix}_nom_attraction">Nom de l'attraction *</label>
                <input type="text" id="${idPrefix}_nom_attraction" name="attractions[${count}][nom_attraction]" required>
                <div class="error-message">Veuillez entrer le nom de l'attraction.</div>
                <div class="horaires-container-attraction form-group">
                    <h4 class="dynamic-section-subtitle">Horaires de cette attraction :</h4>
                    <div class="item-group horaire-group">
                         <div class="horaire-group-inputs">
                            <div><label for="${idPrefix}_horaires_0_date">Date *</label><input type="date" id="${idPrefix}_horaires_0_date" name="attractions[${count}][horaires][0][date]" required><div class="error-message">Date requise.</div></div>
                            <div><label for="${idPrefix}_horaires_0_heure_debut">Début *</label><input type="time" id="${idPrefix}_horaires_0_heure_debut" name="attractions[${count}][horaires][0][heure_debut]" required><div class="error-message">Heure de début requise.</div></div>
                            <div><label for="${idPrefix}_horaires_0_heure_fin">Fin *</label><input type="time" id="${idPrefix}_horaires_0_heure_fin" name="attractions[${count}][horaires][0][heure_fin]" required><div class="error-message">Heure de fin requise.</div></div>
                        </div>
                    </div>
                    <a href="#" role="button" class="text-add-link add-horaire-parc-attraction">+ Ajouter horaire à cette attraction</a>
                </div>
            `;
            container.insertBefore(newGroup, addButtonLink);
            const firstAttractionGroup = container.querySelector('.attraction-group');
            if (firstAttractionGroup) {
                const firstAttractionCross = firstAttractionGroup.querySelector('.remove-icon-cross');
                if(firstAttractionCross && firstAttractionCross.style.display === 'none' && container.querySelectorAll('.attraction-group').length > 1) {
                    firstAttractionCross.style.display = 'flex';
                }
            }
            addRemoveCrossIfNeeded(newGroup.querySelector('.horaire-group')); 
            initializeAddHoraireParcAttractionButtons();
            initializeRemoveButtons();
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }
        
        function initializeAddHoraireParcAttractionButtons() {
            document.querySelectorAll('.add-horaire-parc-attraction').forEach(button => {
                const newButton = button.cloneNode(true); 
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', addHoraireToAttraction);
            });
        }

        function addHoraireToAttraction(event) {
            event.preventDefault();
            const attractionGroup = event.target.closest('.attraction-group');
            const horairesContainer = attractionGroup.querySelector('.horaires-container-attraction');
            const addButtonLink = horairesContainer.querySelector('.add-horaire-parc-attraction');
            const attractionIndex = Array.from(document.querySelectorAll('.attraction-group')).indexOf(attractionGroup);
            const horaireCount = horairesContainer.querySelectorAll('.horaire-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group horaire-group';
            addRemoveCrossIfNeeded(newGroup);
            const idPrefix = `attractions_${attractionIndex}_horaires_${horaireCount}`;
            newGroup.innerHTML += `
                <div class="horaire-group-inputs">
                    <div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="attractions[${attractionIndex}][horaires][${horaireCount}][date]" required><div class="error-message">Date requise.</div></div>
                    <div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="attractions[${attractionIndex}][horaires][${horaireCount}][heure_debut]" required><div class="error-message">Heure de début requise.</div></div>
                    <div><label for="${idPrefix}_heure_fin">Fin *</label><input type="time" id="${idPrefix}_heure_fin" name="attractions[${attractionIndex}][horaires][${horaireCount}][heure_fin]" required><div class="error-message">Heure de fin requise.</div></div>
                </div>
            `;
            horairesContainer.insertBefore(newGroup, addButtonLink);
            initializeRemoveButtons();
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }
        
        function addPlatRestaurant(event) {
            event.preventDefault();
            const container = document.getElementById('plats-container-restaurant');
            const addButtonLink = container.querySelector('#add-plat-restaurant');
            const platGroups = container.querySelectorAll('.plat-group');
            
            if (platGroups.length >= 5) {
                // Optionnel: afficher un message que la limite est atteinte
                // Masquer le bouton/lien si la limite est atteinte après cet ajout potentiel (ou avant)
                if(addButtonLink) addButtonLink.style.display = 'none';
                return; 
            }

            const newGroup = document.createElement('div');
            newGroup.className = 'item-group plat-group';
            addRemoveCrossIfNeeded(newGroup);
            newGroup.innerHTML += `
                <input type="text" name="plats[]" placeholder="Nom du plat ${platGroups.length + 1}" required>
                <div class="error-message">Le nom du plat est requis.</div>
            `;
            container.insertBefore(newGroup, addButtonLink);
            
            if (container.querySelectorAll('.plat-group').length >= 5) {
                 if(addButtonLink) addButtonLink.style.display = 'none';
            }

            const firstGroup = container.querySelector('.plat-group');
            if (firstGroup && !firstGroup.querySelector('.remove-icon-cross') && container.querySelectorAll('.plat-group').length > 1) {
                 addRemoveCrossIfNeeded(firstGroup);
            }
            initializeRemoveButtons();
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }


        function initializeRemoveButtons() {
            document.querySelectorAll('.remove-element').forEach(button => {
                const newButton = button.cloneNode(true); 
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    const groupElement = event.target.closest('.item-group');
                    if (groupElement) {
                        const parentContainer = groupElement.parentNode;
                        groupElement.remove();
                        
                        // Spécifique pour la catégorie Restaurant: réafficher le bouton "Ajouter Plat" si on passe sous la limite
                        if (parentContainer.id === 'plats-container-restaurant') {
                            const addButtonLink = parentContainer.querySelector('#add-plat-restaurant');
                            if (parentContainer.querySelectorAll('.plat-group').length < 5 && addButtonLink) {
                                addButtonLink.style.display = 'inline-block';
                            }
                        }
                        // Spécifique pour la première attraction
                        if (parentContainer.id === 'attractions-container') { // Changé de classList.contains à id
                            const currentAttractions = parentContainer.querySelectorAll('.attraction-group');
                            if (currentAttractions.length === 1) {
                                const lastAttractionCross = currentAttractions[0].querySelector('.remove-icon-cross');
                                if(lastAttractionCross) lastAttractionCross.style.display = 'none';
                            } else if (currentAttractions.length > 0) { 
                                const firstCross = currentAttractions[0].querySelector('.remove-icon-cross');
                                if (firstCross) firstCross.style.display = 'flex';
                            }
                        }
                    }
                });
            });
        }
        

         function getErrorMessageElementForField(field) {
            let directSibling = field.nextElementSibling;
            if (directSibling && directSibling.classList.contains('error-message')) return directSibling;

            let parentDiv = field.closest('div'); // Pour les inputs dans un div (comme les horaires)
            if(parentDiv) {
                 // Chercher un .error-message comme frère de l'input à l'intérieur du même div parent
                let children = Array.from(parentDiv.children);
                let fieldIndex = children.indexOf(field);
                if (fieldIndex !== -1 && children[fieldIndex + 1] && children[fieldIndex + 1].classList.contains('error-message')) {
                    return children[fieldIndex + 1];
                }
                // Moins fiable: frère du div parent
                // let siblingOfParent = parentDiv.nextElementSibling; 
                // if (siblingOfParent && siblingOfParent.classList.contains('error-message')) return siblingOfParent;
            }
            // Fallback pour les champs directement sous un label
            const label = document.querySelector(`label[for="${field.id}"]`);
            if(label && label.nextElementSibling && label.nextElementSibling.id !== field.id && label.nextElementSibling.nextElementSibling && label.nextElementSibling.nextElementSibling.classList.contains('error-message')) {
                 // Cas label -> input -> error-message
                return label.nextElementSibling.nextElementSibling;
            }
            if(label && label.nextElementSibling && label.nextElementSibling.classList.contains('error-message')) {
                 // Cas label -> error-message (si input avant label, ou structure atypique)
                return label.nextElementSibling;
            }
            // Si l'input est le dernier enfant d'un groupe, et le message d'erreur est après le groupe. (Peu probable ici)
            return null; 
        }

        function validateField(field) {
            const errorMessageElement = getErrorMessageElementForField(field);
            let isValidField = field.checkValidity();

            if (field.type === 'file' && field.required && currentSelectedFiles.length === 0) isValidField = false;
            if (field.multiple && field.required && field.selectedOptions.length === 0) isValidField = false;
            
            if (isValidField) {
                field.classList.remove('invalid');
                if (errorMessageElement) errorMessageElement.style.display = 'none';
            } else {
                field.classList.add('invalid');
                if (errorMessageElement) errorMessageElement.style.display = 'block';
            }
            return isValidField;
        }

        function validateAllFields(containerOrForm) {
            let allValid = true;
            containerOrForm.querySelectorAll('input:not([type="button"]):not([type="submit"]), textarea, select').forEach(field => { // Exclure les boutons
                if (field.id === 'photos') { 
                    if (!validatePhotosField()) allValid = false;
                } else if (field.offsetParent !== null || field.type === 'hidden' || field.required) { 
                     // Vérifier si le champ est visible ou requis avant de valider
                    if ( (field.offsetWidth > 0 || field.offsetHeight > 0 || field.getClientRects().length > 0) || field.required ) {
                        if (!validateField(field)) allValid = false;
                    } else { // Si champ non visible et non requis, le considérer valide pour ne pas bloquer
                        field.classList.remove('invalid');
                        const errorMessageElement = getErrorMessageElementForField(field);
                        if (errorMessageElement) errorMessageElement.style.display = 'none';
                    }
                } else { 
                     field.classList.remove('invalid');
                     const errorMessageElement = getErrorMessageElementForField(field);
                     if (errorMessageElement) errorMessageElement.style.display = 'none';
                }
            });
            return allValid;
        }
        
        // ... (Gestion des images: handlePhotoSelection, removeImage, updateFileInput, openImageModal, closeImageModal, validatePhotosField inchangées) ...
        function handlePhotoSelection(event) {
            const files = Array.from(event.target.files);
            const photosErrorMessage = document.getElementById('photos-error-message');
            imagePreviewContainer.innerHTML = ''; 
            currentSelectedFiles = []; 

            if (files.length === 0 && photosInput.required) {
                photosErrorMessage.textContent = "Veuillez ajouter au moins une photo.";
                photosErrorMessage.style.display = 'block';
                photosInput.classList.add('invalid');
                updateFileInput(); 
                return;
            }
            if (files.length > 6) {
                photosErrorMessage.textContent = "Vous ne pouvez sélectionner que 6 photos maximum.";
                photosErrorMessage.style.display = 'block';
                photosInput.classList.add('invalid');
                photosInput.value = ""; 
                updateFileInput();
                return;
            }

            photosErrorMessage.style.display = 'none';
            photosInput.classList.remove('invalid');

            files.forEach((file, index) => {
                if (!file.type.startsWith('image/')){
                    return; 
                }
                currentSelectedFiles.push(file); 

                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    img.alt = file.name;
                    img.addEventListener('click', () => openImageModal(e.target.result));
                    
                    const deleteBtn = document.createElement('span');
                    deleteBtn.className = 'delete-image-btn';
                    deleteBtn.innerHTML = '&times;';
                    deleteBtn.title = 'Supprimer cette image';
                    deleteBtn.dataset.fileName = file.name; 
                    deleteBtn.addEventListener('click', (event) => {
                        event.stopPropagation(); 
                        removeImage(file.name);
                    });
                    
                    previewItem.appendChild(img);
                    previewItem.appendChild(deleteBtn);
                    imagePreviewContainer.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            });
            updateFileInput(); 
        }

        function removeImage(fileNameToRemove) {
            currentSelectedFiles = currentSelectedFiles.filter(file => file.name !== fileNameToRemove);
            imagePreviewContainer.innerHTML = ''; 
            
            currentSelectedFiles.forEach(file => { 
                 const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    img.alt = file.name;
                    img.addEventListener('click', () => openImageModal(e.target.result));
                    const deleteBtn = document.createElement('span');
                    deleteBtn.className = 'delete-image-btn';
                    deleteBtn.innerHTML = '&times;';
                    deleteBtn.title = 'Supprimer cette image';
                    deleteBtn.dataset.fileName = file.name;
                    deleteBtn.addEventListener('click', (event) => {
                        event.stopPropagation();
                        removeImage(file.name);
                    });
                    previewItem.appendChild(img);
                    previewItem.appendChild(deleteBtn);
                    imagePreviewContainer.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            });
            updateFileInput(); 
            validatePhotosField(); 
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            currentSelectedFiles.forEach(file => dataTransfer.items.add(file));
            photosInput.files = dataTransfer.files;
        }
        
        function openImageModal(src) {
            if (modalImageContent && imageModal && closeModalButton) {
                modalImageContent.src = src;
                imageModal.classList.add('show-modal'); 
            }
        }

        function closeImageModal() {
             if (imageModal) {
                imageModal.classList.remove('show-modal');
             }
        }
        
        function validatePhotosField() {
            const photosErrorMessage = document.getElementById('photos-error-message');
            let isValid = true;
            // Utiliser currentSelectedFiles.length pour la validation car photosInput.files est mis à jour.
            if (photosInput.required && currentSelectedFiles.length === 0) {
                photosErrorMessage.textContent = "Veuillez ajouter au moins une photo.";
                photosErrorMessage.style.display = 'block';
                photosInput.classList.add('invalid');
                isValid = false;
            } else if (currentSelectedFiles.length > 6) {
                photosErrorMessage.textContent = "Vous ne pouvez sélectionner que 6 photos maximum.";
                photosErrorMessage.style.display = 'block';
                photosInput.classList.add('invalid');
                isValid = false; 
            } else {
                photosErrorMessage.style.display = 'none';
                photosInput.classList.remove('invalid');
            }
            return isValid;
        }

        if(photosInput) photosInput.addEventListener('change', handlePhotoSelection);
        if(closeModalButton) closeModalButton.addEventListener('click', closeImageModal);
        if(imageModal) imageModal.addEventListener('click', (event) => { 
            if (event.target === imageModal) {
                closeImageModal();
            }
        });

        categorieSelect.addEventListener('change', (event) => {
            fetchCategorieFields(event.target.value);
        });

        window.onload = function () {
            if (categorieSelect.value) {
                fetchCategorieFields(categorieSelect.value);
            } else {
                 if(mainDateInputContainer) mainDateInputContainer.style.display = 'block'; 
                 if(mainDateInput) mainDateInput.required = true;
            }

            if (categorieSelect.value === '2') {
                visiteGuideeSection.style.display = 'block';
                const visiteGuideeCheckbox = document.getElementById('visite_guidee');
                if (visiteGuideeCheckbox) {
                     languesGuideesDiv.style.display = visiteGuideeCheckbox.checked ? 'block' : 'none';
                     languesSelect.required = visiteGuideeCheckbox.checked;
                }
            }

            offerForm.addEventListener('submit', (event) => {
                offerForm.dataset.submitted = 'true'; 
                if (!validateAllFields(offerForm)) {
                    event.preventDefault();
                    const firstInvalidField = offerForm.querySelector('.invalid');
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }
                }
            });
        }
    </script>
</body>
</html>