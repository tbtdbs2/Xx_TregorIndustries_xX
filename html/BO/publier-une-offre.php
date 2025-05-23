<?php
require_once '../composants/generate_uuid.php';
require_once '../../includes/db.php';

$current_pro_id = '7a8b9c0d-1e2f-3a4b-5c6d-7e8f9a0b1c20'; // id de l'user (à changer lorsque tous le systeme de connexion sera finit)

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($pdo)) {
    // fonction qui nettoie les input (Suppression d'espace, supprime les antislash, convertit les caractères spéciaux en entités HTML)
    function validate_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $erreurs = array();

    $titre = validate_input($_POST["titre"] ?? '');
    if (empty($titre)) {
        $erreurs["titre"] = "Veuillez entrer un titre.";
    }

    $prix_str = $_POST["prix"] ?? '';
    $prix = null;
    if (empty($prix_str)) {
        $erreurs["prix"] = "Veuillez entrer un prix.";
    } elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $prix_str)) {
        $erreurs["prix"] = "Veuillez entrer un prix valide (ex: 10, 10.50).";
    } else {
        $prix = floatval($prix_str);
    }

    $coordonnees_telephoniques = null;
    if (!empty($_POST["coordonnees_telephoniques"])) {
        if (!preg_match("/^0[1-9]\d{8}$/", $_POST["coordonnees_telephoniques"])) {
            $erreurs["coordonnees_telephoniques"] = "Veuillez entrer un numéro de téléphone à 10 chiffres commençant par 0.";
        } else {
            $coordonnees_telephoniques = validate_input($_POST["coordonnees_telephoniques"]);
        }
    }

    $resume = validate_input($_POST["resume"] ?? '');
    if (empty($resume)) {
        $erreurs["resume"] = "Veuillez entrer un résumé de votre offre.";
    }

    $description = !empty($_POST["description"]) ? validate_input($_POST["description"]) : null;

    $conditions_accessibilite = validate_input($_POST["conditions_accessibilite"] ?? '');
    if (empty($conditions_accessibilite)) {
        $erreurs["conditions_accessibilite"] = "Veuillez entrer les conditions d'accessibilité.";
    }

    // --- CATEGORY PREPARATION (MODIFIED LOGIC from previous step) ---
    $categorie_form_value = filter_input(INPUT_POST, 'categorie', FILTER_VALIDATE_INT);
    $categorie_type_enum_for_new_row = null; 

    if ($categorie_form_value === false || $categorie_form_value === null || $categorie_form_value < 1 || $categorie_form_value > 5) {
        $erreurs["categorie"] = "Veuillez sélectionner une catégorie valide.";
    } else {
        $map_categorie_to_type = [
            1 => 'activite',
            2 => 'visite',
            3 => 'spectacle',
            4 => 'parc_attractions',
            5 => 'restauration'
        ];
        $categorie_type_enum_for_new_row = $map_categorie_to_type[$categorie_form_value];
    }


    // --- ADRESSE ---
    $ligne_adresse = validate_input($_POST["ligne_adresse"] ?? '');
    if (empty($ligne_adresse)) $erreurs["ligne_adresse"] = "Veuillez entrer la ligne d'adresse.";

    $ville = validate_input($_POST["ville"] ?? '');
    if (empty($ville)) $erreurs["ville"] = "Veuillez entrer la ville.";

    $code_postal = validate_input($_POST["code_postal"] ?? '');
    if (empty($code_postal)) {
        $erreurs["code_postal"] = "Veuillez entrer un code postal.";
    } elseif (!preg_match("/^\d{5}$/", $code_postal)) {
        $erreurs["code_postal"] = "Veuillez entrer un code postal à 5 chiffres.";
    }

    $site_web = null;
    if (!empty($_POST["site"])) {
        $site_input = validate_input($_POST["site"]);
        if (!filter_var($site_input, FILTER_VALIDATE_URL)) {
            $erreurs["site"] = "Veuillez entrer une URL valide pour le site.";
        } else {
            $site_web = $site_input;
        }
    }
    
    $date_offre_principale = null;
    $isDateHandledByCategory = in_array($categorie_form_value, [1, 3, 4]); 
    
    if (!$isDateHandledByCategory && !empty($_POST["date"])) {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $_POST["date"])) {
            $erreurs["date"] = "Format de date invalide pour l'offre. Utilisez YYYY-MM-DD.";
        } else {
            $date_obj = DateTime::createFromFormat('Y-m-d', $_POST["date"]);
            if ($date_obj && $date_obj->format('Y-m-d') === $_POST["date"]) {
                if (new DateTime() > $date_obj && $date_obj->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) { // Regarder si la date est dans le passé
                     $erreurs["date_passee"] = "La date de l'offre ne peut pas être une date passée.";
                } else {
                    $date_offre_principale = $_POST["date"];
                }
            } else {
                 $erreurs["date"] = "Date de l'offre invalide.";
            }
        }
    } elseif (!$isDateHandledByCategory && empty($_POST["date"]) && ($categorie_form_value == 2)) {
        if ($categorie_form_value == 2) { // Visite
            $erreurs["date"] = "Veuillez entrer une date pour la visite.";
        }
    }

    $photo_paths_for_db = [];
    $main_photo_path = null;

    if (isset($_FILES["photos"])) {
        $total_photos_uploaded = 0;
        if (is_array($_FILES["photos"]["name"])) {
            foreach ($_FILES["photos"]["name"] as $filename) {
                if (!empty($filename)) {
                    $total_photos_uploaded++;
                }
            }
        }

        if ($total_photos_uploaded === 0 && empty($erreurs["photos"])) {
            $erreurs["photos_missing"] = "Veuillez ajouter au moins une photo.";
        }
        if ($total_photos_uploaded > 6) {
            $erreurs["photos_count"] = "Vous ne pouvez télécharger que 6 photos maximum.";
        } elseif ($total_photos_uploaded > 0) {
            $target_dir_relative = "uploads/offres/";
            $target_dir_absolute = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . "/" . $target_dir_relative;

            if (!is_dir($target_dir_absolute)) {
                if (!mkdir($target_dir_absolute, 0775, true)) {
                    $erreurs["photos_upload_dir"] = "Échec de la création du dossier de téléchargement sur le serveur.";
                }
            }
            if (!is_writable($target_dir_absolute) && !isset($erreurs["photos_upload_dir"])) {
                 $erreurs["photos_upload_permission"] = "Le dossier de téléchargement n'est pas accessible en écriture sur le serveur.";
            }

            if (!isset($erreurs["photos_upload_dir"]) && !isset($erreurs["photos_upload_permission"])) {
                for ($i = 0; $i < count($_FILES["photos"]["name"]); $i++) {
                    if ($_FILES["photos"]["error"][$i] === UPLOAD_ERR_OK) {
                        $file_tmp_path = $_FILES["photos"]["tmp_name"][$i];
                        $file_name_original = basename($_FILES["photos"]["name"][$i]);
                        $file_size = $_FILES["photos"]["size"][$i];
                        $file_info = new finfo(FILEINFO_MIME_TYPE);
                        $mime_type = $file_info->file($file_tmp_path);

                        $file_extension = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
                        $allowed_mime_types = ["image/jpeg", "image/png", "image/gif"];
                        $allowed_extensions = ["jpg", "jpeg", "png", "gif"];

                        if (in_array($mime_type, $allowed_mime_types) && in_array($file_extension, $allowed_extensions)) {
                            if ($file_size <= 40000000) { 
                                $new_file_name = uniqid('offre_', true) . '.' . $file_extension;
                                $dest_path_absolute = $target_dir_absolute . $new_file_name;
                                if (move_uploaded_file($file_tmp_path, $dest_path_absolute)) {
                                    $relative_path = $target_dir_relative . $new_file_name;
                                    $photo_paths_for_db[] = $relative_path;
                                    if ($main_photo_path === null) {
                                        $main_photo_path = $relative_path;
                                    }
                                } else {
                                    $erreurs["photos_upload_move_" . $i] = "Erreur lors du déplacement du fichier " . htmlspecialchars($file_name_original) . ".";
                                }
                            } else {
                                $erreurs["photos_size_" . $i] = "Le fichier " . htmlspecialchars($file_name_original) . " est trop volumineux (max 4MB).";
                            }
                        } else {
                            $erreurs["photos_type_" . $i] = "Type de fichier non autorisé pour " . htmlspecialchars($file_name_original) . ".";
                        }
                    } elseif ($_FILES["photos"]["error"][$i] !== UPLOAD_ERR_NO_FILE) {
                        $erreurs["photos_upload_error_" . $i] = "Erreur de téléchargement: " . htmlspecialchars($_FILES["photos"]["name"][$i]) . ". Code: " . $_FILES["photos"]["error"][$i];
                    }
                }
            }
        }
    } else {
         $erreurs["photos_missing"] = "Veuillez ajouter au moins une photo (aucun fichier soumis).";
    }
     if (empty($photo_paths_for_db) && empty($erreurs['photos_missing']) && empty($erreurs['photos_count']) && empty($erreurs['photos_upload_dir']) && empty($erreurs['photos_upload_permission']) && !preg_grep('/^photos_upload_move_/', array_keys($erreurs)) && !preg_grep('/^photos_size_/', array_keys($erreurs)) && !preg_grep('/^photos_type_/', array_keys($erreurs)) && !preg_grep('/^photos_upload_error_/', array_keys($erreurs)) ) {
        $erreurs["photos_final_check"] = "Au moins une photo valide est requise et doit être correctement traitée.";
    }

    // --- DATABASE INSERTION ---
    if (empty($erreurs)) {
        $categorie_id_for_offer_and_specific_table = generate_uuid();

        try {
            $pdo->beginTransaction();

            // 0. Insert new Category Instance into 'categories' table
            if (!$categorie_type_enum_for_new_row) {
                throw new PDOException("Type de catégorie non défini pour la nouvelle instance de catégorie.");
            }
            $stmtInsertNewCatInstance = $pdo->prepare("INSERT INTO categories (id, type) VALUES (:id, :type)");
            $stmtInsertNewCatInstance->execute([
                ':id' => $categorie_id_for_offer_and_specific_table,
                ':type' => $categorie_type_enum_for_new_row
            ]);


            // 1. Adresse
            $adresse_id_uuid = null;
            $stmtAdresse = $pdo->prepare("SELECT id FROM adresses WHERE street = :street AND postal_code = :postal_code AND city = :city LIMIT 1");
            $stmtAdresse->execute([
                ':street' => $ligne_adresse,
                ':postal_code' => $code_postal,
                ':city' => $ville
            ]);
            $adresse_row = $stmtAdresse->fetch();

            if ($adresse_row) {
                $adresse_id_uuid = $adresse_row['id'];
            } else {
                $adresse_id_uuid = generate_uuid();
                $stmtInsertAdresse = $pdo->prepare("INSERT INTO adresses (id, street, postal_code, city) VALUES (:id, :street, :postal_code, :city)");
                $stmtInsertAdresse->execute([
                    ':id' => $adresse_id_uuid,
                    ':street' => $ligne_adresse,
                    ':postal_code' => $code_postal,
                    ':city' => $ville
                ]);
            }

            // 2. Offre
            $offre_id_uuid = generate_uuid();
            $sqlOffer = "INSERT INTO offres (
                            id, categorie_id, adresse_id, pro_id, title, summary, description,
                            main_photo, accessibility, website, phone, price,
                            created_at, updated_at, reviews_nb
                         ) VALUES (
                            :id, :categorie_id, :adresse_id, :pro_id, :title, :summary, :description,
                            :main_photo, :accessibility, :website, :phone, :price,
                            NOW(), NOW(), 0
                         )";
            $stmtOffer = $pdo->prepare($sqlOffer);
            $stmtOffer->execute([
                ':id' => $offre_id_uuid,
                ':categorie_id' => $categorie_id_for_offer_and_specific_table, 
                ':adresse_id' => $adresse_id_uuid,
                ':pro_id' => $current_pro_id,
                ':title' => $titre,
                ':summary' => $resume,
                ':description' => $description,
                ':main_photo' => $main_photo_path,
                ':accessibility' => $conditions_accessibilite,
                ':website' => $site_web,
                ':phone' => $coordonnees_telephoniques,
                ':price' => $prix,
            ]);

            // 3. Photos de l'offre (table photos_offres)
            if (!empty($photo_paths_for_db)) {
                $stmtPhotoOffre = $pdo->prepare("INSERT INTO photos_offres (id, offre_id, url) VALUES (:id, :offre_id, :url)");
                foreach ($photo_paths_for_db as $path) {
                    $photo_offre_id_uuid = generate_uuid();
                    $stmtPhotoOffre->execute([
                        ':id' => $photo_offre_id_uuid,
                        ':offre_id' => $offre_id_uuid,
                        ':url' => $path
                    ]);
                }
            }
            
            // 4. Statut initial de l'offre
            $statut_id_uuid = generate_uuid();
            $stmtStatut = $pdo->prepare("INSERT INTO statuts (id, offre_id, status, changed_at) VALUES (:id, :offre_id, :status, NOW())");
            $stmtStatut->execute([
                ':id' => $statut_id_uuid,
                ':offre_id' => $offre_id_uuid,
                ':status' => 1 
            ]);


            // 5. Category-Specific Data
            switch ($categorie_type_enum_for_new_row) {
                case 'activite':
                    $duree_activite = filter_input(INPUT_POST, 'duree', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
                    if ($duree_activite === false || $duree_activite === null) $erreurs["duree_activite"] = "Durée activité (minutes) requise et doit être un nombre positif.";
                    
                    $prix_min_act_str = $_POST['prix_minimum_activite'] ?? '';
                    $prix_min_act = null;
                    if ($prix_min_act_str !== '') {
                        $prix_min_act = filter_var($prix_min_act_str, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
                        if ($prix_min_act === false) $erreurs["prix_minimum_activite"] = "Prix minimum activité invalide.";
                    }

                    $age_req_act_str = $_POST['age_requis_activite'] ?? '';
                    $age_req_act = null;
                    if ($age_req_act_str !== '') {
                         $age_req_act = filter_var($age_req_act_str, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
                         if ($age_req_act === false) $erreurs["age_requis_activite"] = "Âge requis activité invalide.";
                    }
                    
                    if (empty($erreurs["duree_activite"]) && empty($erreurs["prix_minimum_activite"]) && empty($erreurs["age_requis_activite"])) {
                        $stmtAct = $pdo->prepare("INSERT INTO activites (categorie_id, duration, minimum_price, required_age) VALUES (:cat_id, :duration, :min_price, :req_age)");
                        $stmtAct->execute([
                            ':cat_id' => $categorie_id_for_offer_and_specific_table, 
                            ':duration' => $duree_activite,
                            ':min_price' => $prix_min_act ?? $prix,
                            ':req_age' => $age_req_act ?? 0
                        ]);

                        if (isset($_POST['activites'][0]['horaires']) && is_array($_POST['activites'][0]['horaires'])) {
                            $stmtHoraireAct = $pdo->prepare("INSERT INTO horaires_activites (id, activite_id, date, start_time) VALUES (:id, :act_id, :date, :start_time)");
                            foreach ($_POST['activites'][0]['horaires'] as $key => $horaire_data) {
                                $h_date = validate_input($horaire_data['date'] ?? '');
                                $h_debut = validate_input($horaire_data['heure_debut'] ?? '');

                                if (empty($h_date)) $erreurs["horaire_activite_date_".$key] = "Date requise pour l'horaire d'activité " . ($key+1) . ".";
                                elseif (new DateTime() > new DateTime($h_date) && (new DateTime($h_date))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
                                     $erreurs["horaire_activite_date_passee_".$key] = "La date de l'horaire d'activité ".($key+1)." ne peut pas être passée.";
                                }

                                if (empty($h_debut)) $erreurs["horaire_activite_debut_".$key] = "Heure de début requise pour l'horaire d'activité " . ($key+1) . ".";
                                
                                if (!empty($h_date) && !empty($h_debut) && !isset($erreurs["horaire_activite_date_passee_".$key])) {
                                    $horaire_act_id_uuid = generate_uuid();
                                    $stmtHoraireAct->execute([
                                        ':id' => $horaire_act_id_uuid,
                                        ':act_id' => $categorie_id_for_offer_and_specific_table, 
                                        ':date' => $h_date,
                                        ':start_time' => $h_debut
                                    ]);
                                }
                            }
                        } else {
                            $erreurs["horaires_activite_manquants"] = "Au moins un horaire est requis pour l'activité.";
                        }

                        if (isset($_POST['activites'][0]['services']) && is_array($_POST['activites'][0]['services'])) {
                            $stmtPrestaSearch = $pdo->prepare("SELECT id FROM prestations WHERE name = :name LIMIT 1");
                            $stmtPrestaInsert = $pdo->prepare("INSERT INTO prestations (id, name) VALUES (:id, :name)");
                            $stmtActPrestaInclus = $pdo->prepare("INSERT INTO activites_prestations_incluses (activite_id, prestation_id) VALUES (:act_id, :presta_id)");
                            $stmtActPrestaNonInclus = $pdo->prepare("INSERT INTO activites_prestations_non_incluses (activite_id, prestation_id) VALUES (:act_id, :presta_id)");

                            foreach ($_POST['activites'][0]['services'] as $service_data) {
                                $s_nom = validate_input($service_data['nom_service'] ?? '');
                                $s_inclus = isset($service_data['inclusion']);

                                if (!empty($s_nom)) {
                                    $stmtPrestaSearch->execute([':name' => $s_nom]);
                                    $presta_row = $stmtPrestaSearch->fetch();
                                    $prestation_id_uuid = null;

                                    if ($presta_row) {
                                        $prestation_id_uuid = $presta_row['id'];
                                    } else {
                                        $prestation_id_uuid = generate_uuid();
                                        $stmtPrestaInsert->execute([':id' => $prestation_id_uuid, ':name' => $s_nom]);
                                    }

                                    if ($s_inclus) {
                                        $stmtActPrestaInclus->execute([':act_id' => $categorie_id_for_offer_and_specific_table, ':presta_id' => $prestation_id_uuid]);
                                    } else {
                                        $stmtActPrestaNonInclus->execute([':act_id' => $categorie_id_for_offer_and_specific_table, ':presta_id' => $prestation_id_uuid]);
                                    }
                                } else if (isset($service_data['nom_service'])) {
                                     $erreurs["service_activite_nom_manquant"] = "Le nom du service ne peut pas être vide.";
                                }
                            }
                        }
                    }
                    break;

                case 'visite':
                    $duree_visite_heures = filter_input(INPUT_POST, 'duree', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0.5]]);
                    if ($duree_visite_heures === false || $duree_visite_heures === null) $erreurs["duree_visite"] = "Durée visite (en heures, ex: 1.5) requise.";
                    else $duree_visite_minutes = (int)($duree_visite_heures * 60);

                    $prix_min_vis_str = $_POST['prix_minimum_visite'] ?? '';
                    $prix_min_vis = null;
                     if ($prix_min_vis_str !== '') {
                        $prix_min_vis = filter_var($prix_min_vis_str, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
                        if ($prix_min_vis === false) $erreurs["prix_minimum_visite"] = "Prix minimum visite invalide.";
                    }

                    $heure_debut_visite = validate_input($_POST["heure_debut_visite"] ?? '');
                    if (empty($heure_debut_visite)) $erreurs["heure_debut_visite"] = "Heure de début visite requise.";
                    
                    if (empty($date_offre_principale) && !isset($erreurs["date_passee"])) $erreurs["date_visite"] = "Date de la visite requise.";
                    elseif (isset($erreurs["date_passee"])) $erreurs["date_visite_passee"] = "La date de la visite ne peut pas être passée.";


                    $visite_guidee = isset($_POST["visite_guidee"]) ? 1 : 0;
                    
                    if (empty($erreurs["duree_visite"]) && empty($erreurs["prix_minimum_visite"]) && empty($erreurs["heure_debut_visite"]) && empty($erreurs["date_visite"]) && empty($erreurs["date_visite_passee"])) {
                        $stmtVis = $pdo->prepare("INSERT INTO visites (categorie_id, duration, minimum_price, date, start_time, is_guided_tour) VALUES (:cat_id, :duration, :min_price, :date, :start_time, :is_guided)");
                        $stmtVis->execute([
                            ':cat_id' => $categorie_id_for_offer_and_specific_table, 
                            ':duration' => $duree_visite_minutes,
                            ':min_price' => $prix_min_vis ?? $prix,
                            ':date' => $date_offre_principale,
                            ':start_time' => $heure_debut_visite,
                            ':is_guided' => $visite_guidee
                        ]);

                        if ($visite_guidee) {
                            if (empty($_POST["langues"]) || !is_array($_POST["langues"])) {
                                $erreurs["langues_visite"] = "Veuillez sélectionner au moins une langue pour la visite guidée.";
                            } else {
                                $stmtLangSearch = $pdo->prepare("SELECT id FROM langues WHERE language = :lang_code LIMIT 1");
                                $stmtVisLangInsert = $pdo->prepare("INSERT INTO visites_langues (visite_id, langue_id) VALUES (:vis_id, :lang_id)");
                                foreach ($_POST["langues"] as $lang_code) {
                                    $lang_code_valide = validate_input($lang_code);
                                    $stmtLangSearch->execute([':lang_code' => $lang_code_valide]);
                                    $lang_row = $stmtLangSearch->fetch();
                                    if ($lang_row) {
                                        $stmtVisLangInsert->execute([':vis_id' => $categorie_id_for_offer_and_specific_table, ':lang_id' => $lang_row['id']]); 
                                    } else {
                                        $erreurs["langue_inconnue_".$lang_code_valide] = "La langue '".$lang_code_valide."' n'est pas configurée.";
                                    }
                                }
                            }
                        }
                    }
                    break;

                case 'spectacle':
                    $duree_spectacle = filter_input(INPUT_POST, 'duree_spectacle', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
                    if ($duree_spectacle === false || $duree_spectacle === null) $erreurs["duree_spectacle"] = "Durée spectacle (minutes) requise.";

                    $prix_min_spec_str = $_POST['prix_minimum_spectacle'] ?? '';
                    $prix_min_spec = null;
                    if ($prix_min_spec_str !== '') {
                        $prix_min_spec = filter_var($prix_min_spec_str, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
                        if ($prix_min_spec === false) $erreurs["prix_minimum_spectacle"] = "Prix minimum spectacle invalide.";
                    }

                    $date_spectacle_str = validate_input($_POST["date_spectacle"] ?? '');
                    $date_spectacle = null;
                    if (empty($date_spectacle_str) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_spectacle_str)) {
                         $erreurs["date_spectacle"] = "Date spectacle requise (YYYY-MM-DD).";
                    } else {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $date_spectacle_str);
                        if ($date_obj && $date_obj->format('Y-m-d') === $date_spectacle_str) {
                             if (new DateTime() > $date_obj && $date_obj->format('Y-m-d') !== (new DateTime())->format('Y-m-d') ) {
                                $erreurs["date_spectacle_passee"] = "La date du spectacle ne peut pas être une date passée.";
                            } else {
                                $date_spectacle = $date_spectacle_str;
                            }
                        } else {
                             $erreurs["date_spectacle_validite"] = "Date spectacle invalide.";
                        }
                    }

                    $heure_debut_spectacle = validate_input($_POST["heure_debut_spectacle"] ?? '');
                    if (empty($heure_debut_spectacle)) $erreurs["heure_debut_spectacle"] = "Heure de début spectacle requise.";

                    $capacite_spectacle = filter_input(INPUT_POST, 'capacite_spectacle', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
                    if ($capacite_spectacle === false || $capacite_spectacle === null) $erreurs["capacite_spectacle"] = "Capacité spectacle requise.";

                    if (empty($erreurs["duree_spectacle"]) && empty($erreurs["prix_minimum_spectacle"]) && empty($erreurs["date_spectacle"]) && empty($erreurs["heure_debut_spectacle"]) && empty($erreurs["capacite_spectacle"]) && empty($erreurs["date_spectacle_validite"]) && empty($erreurs["date_spectacle_passee"])) {
                        $stmtSpec = $pdo->prepare("INSERT INTO spectacles (categorie_id, duration, minimum_price, date, start_time, capacity) VALUES (:cat_id, :duration, :min_price, :date, :start_time, :capacity)");
                        $stmtSpec->execute([
                            ':cat_id' => $categorie_id_for_offer_and_specific_table, 
                            ':duration' => $duree_spectacle,
                            ':min_price' => $prix_min_spec ?? $prix,
                            ':date' => $date_spectacle,
                            ':start_time' => $heure_debut_spectacle,
                            ':capacity' => $capacite_spectacle
                        ]);
                    }
                    break;

                case 'parc_attractions':
                    $prix_min_parc_str = $_POST['prix_minimum_parc'] ?? '';
                    $prix_min_parc = null;
                    if ($prix_min_parc_str !== '') {
                        $prix_min_parc = filter_var($prix_min_parc_str, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
                        if ($prix_min_parc === false) $erreurs["prix_minimum_parc"] = "Prix minimum parc invalide.";
                    }

                    $age_req_parc_str = $_POST['age_requis_parc'] ?? '';
                    $age_req_parc = null;
                    if ($age_req_parc_str !== '') {
                        $age_req_parc = filter_var($age_req_parc_str, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
                        if ($age_req_parc === false) $erreurs["age_requis_parc"] = "Âge requis parc invalide.";
                    }
                    
                    $nb_attr_parc_str = $_POST['nombre_total_attractions_parc'] ?? '';
                    $nb_attr_parc = null;
                    if ($nb_attr_parc_str !== '') {
                        $nb_attr_parc = filter_var($nb_attr_parc_str, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
                         if ($nb_attr_parc === false) $erreurs["nombre_total_attractions_parc"] = "Nombre d'attractions invalide.";
                    }

                    $maps_url_parc = null;
                    if (!empty($_POST["maps_url_parc"])) {
                        $maps_url_input = validate_input($_POST["maps_url_parc"]);
                        if (!filter_var($maps_url_input, FILTER_VALIDATE_URL)) {
                            $erreurs["maps_url_parc"] = "URL du plan du parc invalide.";
                        } else {
                            $maps_url_parc = $maps_url_input;
                        }
                    }
                    if (empty($maps_url_parc)) $erreurs["maps_url_parc_required"] = "L'URL du plan du parc est requise.";

                    if (empty($erreurs["prix_minimum_parc"]) && empty($erreurs["age_requis_parc"]) && empty($erreurs["nombre_total_attractions_parc"]) && empty($erreurs["maps_url_parc"]) && empty($erreurs["maps_url_parc_required"])) {
                        $stmtParc = $pdo->prepare("INSERT INTO parcs_attractions (categorie_id, minimum_price, required_age, attraction_nb, map_url) VALUES (:cat_id, :min_price, :req_age, :attr_nb, :map_url)");
                        $stmtParc->execute([
                            ':cat_id' => $categorie_id_for_offer_and_specific_table, 
                            ':min_price' => $prix_min_parc ?? $prix,
                            ':req_age' => $age_req_parc ?? 0,
                            ':attr_nb' => $nb_attr_parc ?? 0,
                            ':map_url' => $maps_url_parc
                        ]);

                        if (isset($_POST['attractions']) && is_array($_POST['attractions'])) {
                            $stmtAttractionInsert = $pdo->prepare("INSERT INTO attractions (id, parc_attractions_id, name) VALUES (:id, :parc_id, :name)");
                            $stmtHoraireAttrInsert = $pdo->prepare("INSERT INTO horaires_attractions (id, attraction_id, day, start_time, end_time) VALUES (:id, :attr_id, :day, :start_time, :end_time)");

                            foreach ($_POST['attractions'] as $attr_key => $attr_data) {
                                $attr_nom = validate_input($attr_data['nom_attraction'] ?? '');
                                if (empty($attr_nom)) {
                                    $erreurs["attraction_nom_".$attr_key] = "Nom requis pour l'attraction " . ($attr_key+1) . ".";
                                    continue;
                                }
                                $attraction_id_uuid = generate_uuid();
                                $stmtAttractionInsert->execute([
                                    ':id' => $attraction_id_uuid,
                                    ':parc_id' => $categorie_id_for_offer_and_specific_table, 
                                    ':name' => $attr_nom
                                ]);

                                if (isset($attr_data['horaires']) && is_array($attr_data['horaires'])) {
                                    foreach ($attr_data['horaires'] as $h_key => $h_data) {
                                        $h_date_str = validate_input($h_data['date'] ?? ''); 
                                        $h_debut = validate_input($h_data['heure_debut'] ?? '');
                                        $h_fin = validate_input($h_data['heure_fin'] ?? '');
                                        $day_of_week_for_db = null; 

                                        if (empty($h_date_str)) $erreurs["attraction_".$attr_key."_horaire_date_".$h_key] = "Date requise pour l'horaire de ".$attr_nom.".";
                                        elseif (new DateTime() > new DateTime($h_date_str) && (new DateTime($h_date_str))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
                                            $erreurs["attraction_".$attr_key."_horaire_date_passee_".$h_key] = "La date de l'horaire de l'attraction ".$attr_nom." ne peut pas être passée.";
                                        }

                                        if (empty($h_debut)) $erreurs["attraction_".$attr_key."_horaire_debut_".$h_key] = "Début requis pour l'horaire de ".$attr_nom.".";
                                        if (empty($h_fin)) $erreurs["attraction_".$attr_key."_horaire_fin_".$h_key] = "Fin requise pour l'horaire de ".$attr_nom.".";
                                        
                                        if (!empty($h_date_str) && !empty($h_debut) && !empty($h_fin) && !isset($erreurs["attraction_".$attr_key."_horaire_date_passee_".$h_key])) {
                                            try {
                                                $date_obj_attr = new DateTime($h_date_str);
                                                $day_of_week_php = $date_obj_attr->format('l'); 
                                                switch (strtolower($day_of_week_php)) {
                                                    case 'monday': $day_of_week_for_db = 'lundi'; break;
                                                    case 'tuesday': $day_of_week_for_db = 'mardi'; break;
                                                    case 'wednesday': $day_of_week_for_db = 'mercredi'; break;
                                                    case 'thursday': $day_of_week_for_db = 'jeudi'; break;
                                                    case 'friday': $day_of_week_for_db = 'vendredi'; break;
                                                    case 'saturday': $day_of_week_for_db = 'samedi'; break;
                                                    case 'sunday': $day_of_week_for_db = 'dimanche'; break;
                                                    default:
                                                        $erreurs["attraction_".$attr_key."_horaire_day_invalid_".$h_key] = "Jour de la semaine invalide pour l'horaire de ".$attr_nom.".";
                                                }

                                                if ($day_of_week_for_db) {
                                                    $horaire_attr_id_uuid = generate_uuid();
                                                    $stmtHoraireAttrInsert->execute([
                                                        ':id' => $horaire_attr_id_uuid,
                                                        ':attr_id' => $attraction_id_uuid, 
                                                        ':day' => $day_of_week_for_db,
                                                        ':start_time' => $h_debut,
                                                        ':end_time' => $h_fin
                                                    ]);
                                                }
                                            } catch (Exception $ex) {
                                                $erreurs["attraction_".$attr_key."_horaire_date_parse_".$h_key] = "Format de date invalide pour l'horaire de ".$attr_nom.".";
                                            }
                                        }
                                    }
                                } else {
                                     $erreurs["attraction_".$attr_key."_horaires_manquants"] = "Au moins un horaire complet est requis pour l'attraction " . $attr_nom . ".";
                                }
                            }
                        } else {
                             $erreurs["attractions_parc_manquantes"] = "Au moins une attraction spécifique avec ses horaires doit être ajoutée pour un parc.";
                        }
                    }
                    break;

                case 'restauration':
                    $menu_url_resto = null;
                    if (!empty($_POST["lien_menu_restaurant"])) {
                        $menu_url_input = validate_input($_POST["lien_menu_restaurant"]);
                        if (!filter_var($menu_url_input, FILTER_VALIDATE_URL)) {
                            $erreurs["lien_menu_restaurant"] = "URL du menu invalide.";
                        } else {
                            $menu_url_resto = $menu_url_input;
                        }
                    }
                    if (empty($menu_url_resto)) $erreurs["lien_menu_restaurant_required"] = "L'URL du menu est requise.";

                    $prix_moyen_resto_str = $_POST['prix_moyen_restaurant'] ?? '';
                    $prix_range_enum = null;
                    if ($prix_moyen_resto_str !== '') {
                        $prix_moyen_val = filter_var($prix_moyen_resto_str, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);
                        if ($prix_moyen_val === false) {
                             $erreurs["prix_moyen_restaurant"] = "Prix moyen invalide pour le restaurant.";
                        } else {
                            if ($prix_moyen_val <= 15) $prix_range_enum = '€';
                            elseif ($prix_moyen_val <= 35) $prix_range_enum = '€€';
                            else $prix_range_enum = '€€€';
                        }
                    } else {
                         $erreurs["prix_moyen_restaurant_required"] = "Le prix moyen du restaurant est requis.";
                    }

                    if (empty($erreurs["lien_menu_restaurant"]) && empty($erreurs["lien_menu_restaurant_required"]) && empty($erreurs["prix_moyen_restaurant"]) && empty($erreurs["prix_moyen_restaurant_required"])) {
                        $stmtResto = $pdo->prepare("INSERT INTO restaurations (categorie_id, menu_url, price_range) VALUES (:cat_id, :menu_url, :price_range)");
                        $stmtResto->execute([
                            ':cat_id' => $categorie_id_for_offer_and_specific_table, 
                            ':menu_url' => $menu_url_resto,
                            ':price_range' => $prix_range_enum
                        ]);

                        if (isset($_POST['plats']) && is_array($_POST['plats'])) {
                             if (count($_POST['plats']) > 0 && !empty(array_filter($_POST['plats']))) {
                                $stmtRepasSearch = $pdo->prepare("SELECT id FROM repas WHERE name = :name LIMIT 1");
                                $stmtRepasInsert = $pdo->prepare("INSERT INTO repas (id, name) VALUES (:id, :name)");
                                $stmtRestoRepasInsert = $pdo->prepare("INSERT INTO restaurations_repas (restauration_id, repas_id) VALUES (:resto_id, :repas_id)");

                                foreach ($_POST['plats'] as $idx => $plat_nom_input) {
                                    $plat_nom = validate_input($plat_nom_input);
                                    if (empty($plat_nom)) {
                                        if (count($_POST['plats']) > 1 || !empty(array_filter(array_slice($_POST['plats'], $idx+1)))) {
                                           $erreurs["plat_restaurant_nom_".$idx] = "Le nom du plat " . ($idx+1) . " est requis s'il est ajouté.";
                                        }
                                        continue; 
                                    }

                                    $stmtRepasSearch->execute([':name' => $plat_nom]);
                                    $repas_row = $stmtRepasSearch->fetch();
                                    $repas_id_uuid = null;

                                    if ($repas_row) {
                                        $repas_id_uuid = $repas_row['id'];
                                    } else {
                                        $repas_id_uuid = generate_uuid();
                                        $stmtRepasInsert->execute([':id' => $repas_id_uuid, ':name' => $plat_nom]);
                                    }
                                    $stmtRestoRepasInsert->execute([
                                        ':resto_id' => $categorie_id_for_offer_and_specific_table, 
                                        ':repas_id' => $repas_id_uuid
                                    ]);
                                }
                            }
                        }
                    }
                    break;
            }

            if (!empty($erreurs)) {
                $pdo->rollBack();
            } else {
                $pdo->commit();
                $notification_message = "L'offre \"" . htmlspecialchars($titre) . "\" a été publiée avec succès !";
                header("Location: index.html?publish_status=success&notification_message=" . urlencode($notification_message));
                exit();
            }

        } catch (PDOException $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database Insertion Error: " . $e->getMessage() . " - Data: " . json_encode($_POST));
            if ($e->getCode() == '23000') { 
                 $erreurs["db_general"] = "Une erreur de contrainte de base de données est survenue (ex: duplicata). Détail: " . $e->getMessage();
            } else {
                $erreurs["db_general"] = "Une erreur est survenue lors de la publication de votre offre : " . $e->getMessage() . ". Veuillez réessayer. ";
            }
        }
    }
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        

        /* Style pour les sections du formulaire */
        .form-section {
            padding: var(--espacement-double);
            border-bottom: var(--bordure-standard-interface);
        }
        .form-section:last-of-type {
            border-bottom: none;
        }

        /* Style pour les labels généraux du formulaire */
        label {
            display: block;
            margin-bottom: var(--espacement-standard);
            font-weight: var(--font-weight-medium);
        }

        /* Style généraux pour les champs de saisie du formulaire */
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

        /* Réinitialisation de l'apparence pour le select de catégorie */
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

        /* Styles pour les messages de succès et d'erreur */
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
        .error ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Conteneur du formulaire principal */
        form#offer-form {
            max-width: 660px; /* Consistent with profil.php's form container */
            margin-left: auto; /* Center the form */
            margin-right: auto; /* Center the form */
            margin-top: var(--espacement-double); /* Add top margin for spacing */
            margin-bottom: var(--espacement-double); /* Existing bottom margin */
            background-color: #fff; /* Card-like background */
            padding: var(--espacement-double); /* Existing padding */
            border: 1px solid #ddd; /* Existing border */
            border-radius: var(--border-radius-standard); /* Existing radius */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Card-like shadow, similar to creation-compte.php */
        }

        /* Style pour les champs invalides */
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

        /* bouton de soumission principal */
        form#offer-form button[type="submit"] {
            background-color: var(--couleur-principale); 
            color: var(--couleur-blanche);
            width: 80%;
            padding: var(--espacement-moyen);
            margin: var(--espacement-double) auto;
            display: block;
            border: none;
            border-radius: var(--border-radius-bouton);
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease; /* Transition pour le hover */
        }

        form#offer-form button[type="submit"]:hover {
            background-color: var(--couleur-principale-hover); /* Orange foncé pr le hover */
        }

        /* Section pour la visite guidée */
        #langues-guidees { 
            display: none; /* Caché par défaut, affiché par JS */
        }
        #langues-guidees.show { 
            display: block; 
        }

        /* Style pour les champs spécifiques aux catégorie(dynamiquement ajoutés) */
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
            padding-top: calc(var(--espacement-moyen) + 20px); /* Espace pour la croix*/
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
            padding: var(--espacement-standard) 0 !important; 
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

        /* Icône de suppression pour les éléments dynamiques */
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

        /* Groupe d'inputs pour les horaires (date, début, fin) */
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
            min-width: 120px; /* Empêche les champs d'être trop petits */
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

        /* Titre pour un groupe d'attraction spécifique */
        #categorie-specific-fields .attraction-group h3 {
            margin-top: 0;
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-principale); 
        }

        .data-display {
            margin-top: var(--espacement-double);
            padding: var(--espacement-standard);
            border: 1px solid #ddd;
            border-radius: var(--border-radius-standard);
            background-color: #f9f9f9;
        }
        .data-display h3 { margin-top: 0; margin-bottom: var(--espacement-moyen); color: var(--couleur-principale); }
        .data-display p { margin-bottom: var(--espacement-standard); }
        .data-display ul { list-style-type: none; padding-left: 0; }
        .data-display li { margin-bottom: var(--espacement-petit); } 

        /* Prévisualisation des images */
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
            cursor: pointer; /* Pour indiquer qu'on peut cliquer pour agrandir */
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

        /* Modale pour l'image agrandie */
        #image-modal {
            display: none; 
            position: fixed;
            z-index: 1050; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Permet de scroller si l'image est trop grande */
            background-color: rgba(0,0,0,0.85); 
            justify-content: center; /* Centre l'image horizontalement */
            align-items: center; /* Centre l'image verticalement */
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
                <li><a href="recherche.php">Mes Offres</a></li>
                <li><a href="publier-une-offre.php" class="active">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <a href="profil.php" class="btn btn-secondary">Mon profil</a>
            <a href="" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

    <main>
        <div class="container content-area">
            <h1 style="text-align: center;">Publier votre offre</h1>
            <h2 style="text-align: center;">Dites-nous en plus</h2>

            <?php
            if (!$pdo) {
                 echo "<div class='error message'>Erreur critique: Impossible de se connecter à la base de données. L'application ne peut pas fonctionner. Veuillez contacter l'administrateur.</div>";
            }

            if (isset($_SESSION['success_message'])) {
                echo "<div class='success message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
                unset($_SESSION['success_message']);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($erreurs)) {
                echo "<div class='error message'>";
                echo "<strong>Veuillez corriger les erreurs suivantes :</strong><ul>";
                foreach ($erreurs as $erreur_key => $erreur_msg) {
                    echo "<li>" . htmlspecialchars($erreur_msg) . " (Champ/Info: " . htmlspecialchars($erreur_key) . ")</li>";
                }
                echo "</ul></div>";
            } elseif (isset($erreurs["db_general"]) && !empty($erreurs["db_general"])) {
                 echo "<div class='error message'>" . htmlspecialchars($erreurs["db_general"]) . "</div>";
            }
            ?>

            <form id="offer-form" method="post" enctype="multipart/form-data" novalidate>
                <div class="form-section">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" required value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>">
                    <div class="error-message">Veuillez entrer un titre pour votre offre.</div>

                    <label for="prix">Prix *</label>
                    <input type="text" id="prix" name="prix" pattern="^\d+(\.\d{1,2})?$" required value="<?php echo isset($_POST['prix']) ? htmlspecialchars($_POST['prix']) : ''; ?>">
                    <div class="error-message">Veuillez entrer un prix valide (ex: 10, 10.50).</div>

                    <label for="coordonnees_telephoniques">Coordonnées téléphoniques</label>
                    <input type="text" id="coordonnees_telephoniques" name="coordonnees_telephoniques"
                        pattern="^0[1-9]\d{8}$" value="<?php echo isset($_POST['coordonnees_telephoniques']) ? htmlspecialchars($_POST['coordonnees_telephoniques']) : ''; ?>">
                    <div class="error-message">Veuillez entrer un numéro de téléphone à 10 chiffres commençant par 0.</div>

                    <label for="resume">Résumé *</label>
                    <textarea id="resume" name="resume" required><?php echo isset($_POST['resume']) ? htmlspecialchars($_POST['resume']) : ''; ?></textarea>
                    <div class="error-message">Veuillez entrer un résumé de votre offre.</div>

                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>

                    <label for="conditions_accessibilite">Conditions d'accessibilité *</label>
                    <textarea id="conditions_accessibilite" name="conditions_accessibilite" required><?php echo isset($_POST['conditions_accessibilite']) ? htmlspecialchars($_POST['conditions_accessibilite']) : ''; ?></textarea>
                    <div class="error-message">Veuillez entrer les conditions d'accessibilité.</div>

                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="1" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == '1') ? 'selected' : ''; ?>>Activité</option>
                        <option value="2" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == '2') ? 'selected' : ''; ?>>Visite</option>
                        <option value="3" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == '3') ? 'selected' : ''; ?>>Spectacle</option>
                        <option value="4" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == '4') ? 'selected' : ''; ?>>Parc d'attraction</option>
                        <option value="5" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == '5') ? 'selected' : ''; ?>>Restaurant</option>
                    </select>
                    <div class="error-message">Veuillez sélectionner une catégorie.</div>
                </div>

                <div id="categorie-specific-fields" class="form-section">
                    </div>

                <div class="form-section" id="visite-guidee-section" style="display: none;">
                    <label>
                        <input type="checkbox" id="visite_guidee" name="visite_guidee" style="width:auto; margin-right: 5px;" <?php echo isset($_POST['visite_guidee']) ? 'checked' : ''; ?>> La visite est guidée
                    </label>
                    <div id="langues-guidees" class="form-group" style="<?php echo isset($_POST['visite_guidee']) ? 'display:block;' : 'display:none;'; ?>">
                        <label for="langues">Langues proposées *</label>
                        <select id="langues" name="langues[]" multiple>
                            <?php
                            $selected_langues = isset($_POST['langues']) && is_array($_POST['langues']) ? $_POST['langues'] : [];
                            $all_langues = ['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol', 'de' => 'Allemand', 'it' => 'Italien', 'zh' => 'Chinois', 'ja' => 'Japonais', 'ru' => 'Russe', 'pt' => 'Portugais', 'ar' => 'Arabe'];
                            foreach ($all_langues as $code => $nom) {
                                $selected_attr = in_array($code, $selected_langues) ? 'selected' : '';
                                echo "<option value=\"$code\" $selected_attr>" . htmlspecialchars($nom) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="error-message">Veuillez sélectionner au moins une langue si la visite est guidée.</div>
                    </div>
                </div>

                <div class="form-section">
                    <div id="main-date-input-container" style="<?php echo (isset($_POST['categorie']) && in_array($_POST['categorie'], ['1','3','4'])) ? 'display:none;' : 'display:block;'; ?>">
                        <label for="date">Date de l'offre/visite *</label> <input type="date" id="date" name="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                        <div class="error-message">Veuillez entrer la date de l'offre/visite.</div>
                    </div>

                    <label for="site">Site Web</label>
                    <input type="url" id="site" name="site" placeholder="https://www.example.com" value="<?php echo isset($_POST['site']) ? htmlspecialchars($_POST['site']) : ''; ?>">
                    <div class="error-message">Veuillez entrer une URL valide.</div>

                    <label for="ligne_adresse">Ligne d'adresse *</label>
                    <input type="text" id="ligne_adresse" name="ligne_adresse" required value="<?php echo isset($_POST['ligne_adresse']) ? htmlspecialchars($_POST['ligne_adresse']) : ''; ?>">
                    <div class="error-message">Veuillez entrer la ligne d'adresse.</div>

                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required value="<?php echo isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : ''; ?>">
                    <div class="error-message">Veuillez entrer la ville.</div>

                    <label for="code_postal">Code postal *</label>
                    <input type="text" id="code_postal" name="code_postal" pattern="^\d{5}$" required value="<?php echo isset($_POST['code_postal']) ? htmlspecialchars($_POST['code_postal']) : ''; ?>">
                    <div class="error-message">Veuillez entrer un code postal à 5 chiffres.</div>

                    <label for="photos">Ajoutez jusqu'à 6 photos (minimum 1) *</label>
                    <input type="file" id="photos" name="photos[]" multiple accept="image/*" required>
                    <div class="error-message" id="photos-error-message">Veuillez ajouter au moins une photo (jusqu'à 6).</div>
                    <div id="image-preview-container">
                        </div>

                     <label style="font-weight: normal; font-size: 0.9em; margin-top: var(--espacement-double);">
                        <input type="checkbox" name="mettre_a_la_une" style="width:auto; margin-right: 5px;" <?php echo isset($_POST['mettre_a_la_une']) ? 'checked' : ''; ?>> Je souhaite mettre mon offre à la une
                        (fonctionnalité payante - TODO : à implémenter via table 'souscriptions')
                    </label>
                    <label style="font-weight: normal; font-size: 0.9em;">
                        <input type="checkbox" name="offre_speciale" style="width:auto; margin-right: 5px;" <?php echo isset($_POST['offre_speciale']) ? 'checked' : ''; ?>> Je souhaite mettre mon offre en avant comme "Offre Spéciale"
                        (fonctionnalité payante - TODO : à implémenter via table souscriptions')
                    </label>
                    <button type="submit">Publier mon annonce</button>
                </div>
            </form>

            <div id="image-modal">
                <span id="close-modal" title="Fermer">&times;</span>
                <img id="modal-image-content" src="#" alt="Image agrandie">
            </div>
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

        const serverPostData = <?php echo json_encode($_POST); ?>;
        const today = new Date().toISOString().split('T')[0];
        
        // Met la date du jours comme date minimal (pour le calandrier)
        function setMinDateForInput(inputElement) { 
            if (inputElement) {
                inputElement.setAttribute('min', today);
            }
        }
        // Genere et affiche dynamiquement les champs de formulaire spécifiques à la catégorie sélectionnée (activité, visite, etc.) 
        function fetchCategorieFields(categorieId, postData = {}) {
            let htmlContent = '';
            const isDateHandledByCategoryOrNotApplicable = (categorieId === '1' || categorieId === '3' || categorieId === '4');

            if(mainDateInputContainer) mainDateInputContainer.style.display = isDateHandledByCategoryOrNotApplicable ? 'none' : 'block';
            if(mainDateInput) {
                 mainDateInput.required = !isDateHandledByCategoryOrNotApplicable;
                 if (categorieId === '2') mainDateInput.required = true;
                 else if (categorieId === '5') mainDateInput.required = false;
                 setMinDateForInput(mainDateInput); // Set min date for main date input
            }


            if (categorieId === '1') { // Activité
                 const dureeVal = postData['duree'] || '';
                 const prixMinVal = postData['prix_minimum_activite'] || '';
                 const ageReqVal = postData['age_requis_activite'] || '';
                htmlContent = `
                    <label for="duree_activite">Durée de l'activité (en minutes) *</label>
                    <input type="number" id="duree_activite" name="duree" required min="1" value="${dureeVal}">
                    <div class="error-message">Veuillez entrer la durée de l'activité.</div>
                    <label for="prix_minimum_activite">Prix minimum (optionnel, si différent du prix principal)</label>
                    <input type="number" id="prix_minimum_activite" name="prix_minimum_activite" step="0.01" min="0" placeholder="Ex: 10.50" value="${prixMinVal}">
                    <div class="error-message">Veuillez entrer un prix valide.</div>
                    <label for="age_requis_activite">Âge requis (optionnel)</label>
                    <input type="number" id="age_requis_activite" name="age_requis_activite" min="0" placeholder="Ex: 6" value="${ageReqVal}">
                    <div class="error-message">Veuillez entrer un âge valide.</div>
                    <div id="horaires-container-activite" class="form-group">
                        <h4 class="dynamic-section-subtitle">Horaires de l'activité (au moins un requis) :</h4>
                        </div>
                    <a href="#" role="button" class="text-add-link add-horaire-activite">+ Ajouter un horaire</a>
                    <div id="services-container-activite" class="form-group">
                        <h4 class="dynamic-section-subtitle">Services/Prestations proposés :</h4>
                        </div>
                     <a href="#" role="button" id="add-service-activite" class="text-add-link">+ Ajouter un service/prestation</a>
                `;
            } else if (categorieId === '2') { // Visite
                const dureeVal = postData['duree'] || '';
                const prixMinVal = postData['prix_minimum_visite'] || '';
                const heureDebutVal = postData['heure_debut_visite'] || '';
                htmlContent = `
                    <label for="duree_visite">Durée de la visite (en heures, ex: 1.5 pour 1h30) *</label>
                    <input type="number" id="duree_visite" name="duree" required step="0.1" min="0.1" value="${dureeVal}">
                    <div class="error-message">Veuillez entrer la durée de la visite.</div>

                    <label for="prix_minimum_visite">Prix minimum (optionnel, si différent du prix principal)</label>
                    <input type="number" id="prix_minimum_visite" name="prix_minimum_visite" step="0.01" min="0" placeholder="Ex: 5.00" value="${prixMinVal}">
                    <div class="error-message">Veuillez entrer un prix valide.</div>

                    <label for="heure_debut_visite">Heure de début *</label>
                    <input type="time" id="heure_debut_visite" name="heure_debut_visite" required value="${heureDebutVal}">
                    <div class="error-message">Veuillez entrer une heure de début.</div>
                `;
            } else if (categorieId === '3') { // Spectacle
                const dureeVal = postData['duree_spectacle'] || '';
                const prixMinVal = postData['prix_minimum_spectacle'] || '';
                const dateSpectVal = postData['date_spectacle'] || '';
                const heureDebutVal = postData['heure_debut_spectacle'] || '';
                const capaciteVal = postData['capacite_spectacle'] || '';
                htmlContent = `
                    <label for="duree_spectacle">Durée du spectacle (en minutes) *</label>
                    <input type="number" id="duree_spectacle" name="duree_spectacle" required min="1" value="${dureeVal}">
                    <div class="error-message">Veuillez entrer la durée du spectacle.</div>

                    <label for="prix_minimum_spectacle">Prix minimum (optionnel, si différent du prix principal)</label>
                    <input type="number" id="prix_minimum_spectacle" name="prix_minimum_spectacle" step="0.01" min="0" placeholder="Ex: 15.00" value="${prixMinVal}">
                    <div class="error-message">Veuillez entrer un prix valide.</div>

                    <label for="date_spectacle">Date du spectacle *</label>
                    <input type="date" id="date_spectacle" name="date_spectacle" required value="${dateSpectVal}">
                    <div class="error-message">Veuillez entrer la date du spectacle.</div>

                    <label for="heure_debut_spectacle">Heure de début *</label>
                    <input type="time" id="heure_debut_spectacle" name="heure_debut_spectacle" required value="${heureDebutVal}">
                    <div class="error-message">Veuillez entrer l'heure de début du spectacle.</div>

                    <label for="capacite_spectacle">Capacité (nombre de places) *</label>
                    <input type="number" id="capacite_spectacle" name="capacite_spectacle" required min="1" value="${capaciteVal}">
                    <div class="error-message">Veuillez entrer la capacité du spectacle.</div>
                `;
            } else if (categorieId === '4') { // Parc d'attraction
                const prixMinVal = postData['prix_minimum_parc'] || '';
                const ageReqVal = postData['age_requis_parc'] || '';
                const nbAttrVal = postData['nombre_total_attractions_parc'] || '0';
                const mapsUrlVal = postData['maps_url_parc'] || '';
                htmlContent = `
                    <label for="prix_minimum_parc">Prix minimum d'entrée (optionnel, si différent du prix principal)</label>
                    <input type="number" id="prix_minimum_parc" name="prix_minimum_parc" step="0.01" min="0" placeholder="Ex: 20.00" value="${prixMinVal}">
                    <div class="error-message">Veuillez entrer un prix valide.</div>

                    <label for="age_requis_parc">Âge requis (optionnel)</label>
                    <input type="number" id="age_requis_parc" name="age_requis_parc" min="0" placeholder="Ex: 3" value="${ageReqVal}">
                    <div class="error-message">Veuillez entrer un âge valide.</div>

                    <label for="nombre_total_attractions_parc">Nombre total d'attractions estimé</label>
                    <input type="number" id="nombre_total_attractions_parc" name="nombre_total_attractions_parc" min="0" placeholder="Ex: 25" value="${nbAttrVal}">
                    <div class="error-message">Veuillez entrer un nombre valide.</div>

                    <label for="maps_url_parc">Lien vers le plan du parc (URL) *</label>
                    <input type="url" id="maps_url_parc" name="maps_url_parc" required placeholder="https://example.com/plan-du-parc" value="${mapsUrlVal}">
                    <div class="error-message">Veuillez entrer une URL valide.</div>

                    <div id="attractions-container"> <h4 class="dynamic-section-subtitle" style="margin-top:var(--espacement-double);">Attractions spécifiques (au moins une requise) :</h4>
                        </div>
                    <a href="#" role="button" id="add-attraction-parc" class="text-add-link">+ Ajouter une attraction spécifique</a>
                `;
            } else if (categorieId === '5') { // Restaurant
                const menuUrlVal = postData['lien_menu_restaurant'] || '';
                const prixMoyenVal = postData['prix_moyen_restaurant'] || '';
                htmlContent = `
                    <label for="lien_menu_restaurant">Lien vers le menu (URL) *</label>
                    <input type="url" id="lien_menu_restaurant" name="lien_menu_restaurant" required placeholder="https://example.com/menu" value="${menuUrlVal}">
                    <div class="error-message">Veuillez entrer une URL valide pour le menu.</div>

                    <label for="prix_moyen_restaurant">Prix moyen par personne (€) *</label>
                    <input type="number" id="prix_moyen_restaurant" name="prix_moyen_restaurant" required step="0.01" min="0" placeholder="Ex: 25.50" value="${prixMoyenVal}">
                    <div class="error-message">Veuillez entrer un prix moyen valide.</div>

                    <div id="plats-container-restaurant" class="form-group">
                        <h4 class="dynamic-section-subtitle">Plats principaux proposés (Max 5, optionnel) :</h4>
                        </div>
                     <a href="#" role="button" id="add-plat-restaurant" class="text-add-link">+ Ajouter un plat</a>
                `;
            }
            categorieSpecificFields.innerHTML = htmlContent;
            
            // Set min date for date_spectacle if it exists
            const dateSpectacleInput = document.getElementById('date_spectacle');
            setMinDateForInput(dateSpectacleInput);


            if (categorieId === '1') {
                if (postData.activites && postData.activites[0] && postData.activites[0].horaires && Array.isArray(postData.activites[0].horaires)) {
                    postData.activites[0].horaires.forEach(h => addHoraireActivite(null, h.date, h.heure_debut));
                } else {
                     addHoraireActivite();
                }
                if (postData.activites && postData.activites[0] && postData.activites[0].services && Array.isArray(postData.activites[0].services)) {
                    postData.activites[0].services.forEach(s => addServiceActivite(null, s.nom_service, s.inclusion === 'on' || s.inclusion === true));
                }
            } else if (categorieId === '4') {
                 if (postData.attractions && Array.isArray(postData.attractions)) {
                    postData.attractions.forEach(attr => {
                        addAttractionParc(null, attr.nom_attraction, attr.horaires || []);
                    });
                } else {
                     addAttractionParc();
                }
            } else if (categorieId === '5') {
                 if (postData.plats && Array.isArray(postData.plats)) {
                     postData.plats.forEach(platName => addPlatRestaurant(null, platName));
                 }
            }


            if (categorieId === '2') {
                visiteGuideeSection.style.display = 'block';
                const visiteGuideeCheckbox = document.getElementById('visite_guidee');
                if(visiteGuideeCheckbox) {
                    const isChecked = postData['visite_guidee'] === 'on' || postData['visite_guidee'] === true;
                    visiteGuideeCheckbox.checked = isChecked;
                    languesGuideesDiv.style.display = isChecked ? 'block' : 'none';
                    languesSelect.required = isChecked;
                }
            } else {
                visiteGuideeSection.style.display = 'none';
                if(languesSelect) languesSelect.required = false;
            }
            initializeDynamicEventListeners();
            validateAllFields(categorieSpecificFields);
        }

        // Attache les écouteurs d'événements nécessaires aux éléments de formulaire qui sont ajoutés dynamiquement (comme les boutons "ajouter horaire", "ajouter service", etc.)
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

            categorieSpecificFields.addEventListener('click', function(event) {
                if (event.target.matches('.add-horaire-activite')) {
                    addHoraireActivite(event);
                } else if (event.target.matches('#add-service-activite')) {
                    addServiceActivite(event);
                } else if (event.target.matches('#add-attraction-parc')) {
                    addAttractionParc(event);
                } else if (event.target.matches('.add-horaire-parc-attraction')) {
                     addHoraireToAttraction(event);
                } else if (event.target.matches('#add-plat-restaurant')) {
                    addPlatRestaurant(event);
                } else if (event.target.matches('.remove-element')) {
                    handleRemoveElement(event);
                }
            });
             categorieSpecificFields.querySelectorAll('.item-group').forEach(group => addRemoveCrossIfNeeded(group));
        }

        // Crée et retourne un élément <span> (une croix "×") utilisé comme bouton pour supprimer des éléments dynamiques.
        function createRemoveCross() {
            const cross = document.createElement('span');
            cross.className = 'remove-icon-cross remove-element';
            cross.innerHTML = '&times;';
            cross.setAttribute('role', 'button');
            cross.setAttribute('aria-label', 'Supprimer cet élément');
            return cross;
        }

        // Ajoute une croix de suppression à un groupe d'éléments s'il n'en a pas déjà une, puis met à jour la visibilité des croix.
        function addRemoveCrossIfNeeded(groupElement) {
            if (!groupElement.querySelector('.remove-icon-cross')) {
                const cross = createRemoveCross();
                groupElement.insertBefore(cross, groupElement.firstChild);
            }
            const parentContainer = groupElement.parentNode;
            if (parentContainer.id === 'horaires-container-activite') updateRemoveCrossVisibility(parentContainer, '.horaire-group');
            else if (parentContainer.id === 'services-container-activite') updateRemoveCrossVisibility(parentContainer, '.service-group');
            else if (parentContainer.id === 'attractions-container') updateRemoveCrossVisibility(parentContainer, '.attraction-group');
            else if (parentContainer.classList.contains('horaires-container-attraction')) updateRemoveCrossVisibility(parentContainer, '.horaire-group');
            else if (parentContainer.id === 'plats-container-restaurant') updateRemoveCrossVisibility(parentContainer, '.plat-group');
        }

        // Gère l'affichage (visible/caché) des croix de suppression en fonction du nombre d'éléments restants dans un conteneur (par exemple, on ne peut pas supprimer le dernier horaire s'il en faut au moins un)
        function updateRemoveCrossVisibility(container, itemSelector) {
            const items = (typeof container === 'string') ? document.querySelectorAll(container + " " + itemSelector) : container.querySelectorAll(itemSelector);
            const minItems = (itemSelector === '.service-group' || itemSelector === '.plat-group') ? 0 : 1;

            items.forEach((item, index) => {
                const cross = item.querySelector('.remove-icon-cross');
                if (cross) {
                    if (items.length > minItems) {
                        cross.style.display = 'flex';
                    } else {
                         cross.style.display = 'none';
                    }
                }
            });
        }

        // Ajoute dynamiquement un nouveau groupe de champs pour saisir une date et une heure de début pour un horaire d'activité
        function addHoraireActivite(event, dateVal = '', debutVal = '') {
            if(event) event.preventDefault();
            const container = document.getElementById('horaires-container-activite');
            const count = container.querySelectorAll('.horaire-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group horaire-group';

            const idPrefix = `activites_0_horaires_${count}`;
            newGroup.innerHTML = `
                <div class="horaire-group-inputs">
                    <div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="activites[0][horaires][${count}][date]" required value="${dateVal}"><div class="error-message">Date requise.</div></div>
                    <div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="activites[0][horaires][${count}][heure_debut]" required value="${debutVal}"><div class="error-message">Heure de début requise.</div></div>
                </div>
            `;
            container.appendChild(newGroup);
            setMinDateForInput(newGroup.querySelector('input[type="date"]')); // Set min date for new input
            addRemoveCrossIfNeeded(newGroup);
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        // Ajoute dynamiquement un nouveau groupe de champs pour saisir le nom d'un service/prestation et indiquer s'il est inclus pour une activité
        function addServiceActivite(event, nomVal = '', inclusVal = false) {
            if(event) event.preventDefault();
            const container = document.getElementById('services-container-activite');
            const count = container.querySelectorAll('.service-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group service-group';

            const checkedAttr = inclusVal ? 'checked' : '';
            newGroup.innerHTML = `
                <label for="service_nom_${count}">Nom du service/prestation *</label>
                <input type="text" id="service_nom_${count}" name="activites[0][services][${count}][nom_service]" placeholder="Ex: Wifi gratuit" value="${nomVal}" required>
                <div class="error-message">Le nom du service est requis.</div>
                 <label style="display: inline-flex; align-items: center; margin-top: var(--espacement-petit); margin-bottom: var(--espacement-standard);">
                    <input type="checkbox" name="activites[0][services][${count}][inclusion]" style="width: auto; margin-right: var(--espacement-petit);" ${checkedAttr}> Inclus dans le prix
                </label>
            `;
            container.appendChild(newGroup);
            addRemoveCrossIfNeeded(newGroup);
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        //Ajoute dynamiquement un nouveau groupe de champs pour une attraction de parc, incluant son nom et un espace pour ses propres horaires
        function addAttractionParc(event, nomVal = '', horairesData = []) {
            if(event) event.preventDefault();
            const container = document.getElementById('attractions-container');
            const count = container.querySelectorAll('.attraction-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group attraction-group';

            const idPrefix = `attractions_${count}`;
            newGroup.innerHTML = `
                <h3>Attraction ${count + 1}</h3>
                <label for="${idPrefix}_nom_attraction">Nom de l'attraction *</label>
                <input type="text" id="${idPrefix}_nom_attraction" name="attractions[${count}][nom_attraction]" required value="${nomVal}">
                <div class="error-message">Veuillez entrer le nom de l'attraction.</div>
                <div class="horaires-container-attraction form-group">
                    <h4 class="dynamic-section-subtitle">Horaires de cette attraction (au moins un requis) :</h4>
                    </div>
                <a href="#" role="button" class="text-add-link add-horaire-parc-attraction">+ Ajouter horaire à cette attraction</a>
            `;
            container.appendChild(newGroup);
            addRemoveCrossIfNeeded(newGroup);

            const attractionHorairesContainer = newGroup.querySelector('.horaires-container-attraction');
            if(horairesData && horairesData.length > 0) {
                horairesData.forEach(h => addHoraireToAttraction(null, attractionHorairesContainer, h.date, h.heure_debut, h.heure_fin));
            } else {
                addHoraireToAttraction(null, attractionHorairesContainer);
            }

            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        //Ajoute dynamiquement un nouveau groupe de champs pour saisir une date, une heure de début et une heure de fin pour un horaire spécifique à une attraction de parc
        function addHoraireToAttraction(event, containerElement = null, dateVal = '', debutVal = '', finVal = '') {
            if(event) event.preventDefault();
            const attractionGroup = containerElement ? containerElement.closest('.attraction-group') : event.target.closest('.attraction-group');
            if (!attractionGroup) return;
            const horairesContainer = containerElement || attractionGroup.querySelector('.horaires-container-attraction');

            const attractionIndexInput = attractionGroup.querySelector('[name*="[nom_attraction]"]');
            const nameAttr = attractionIndexInput ? attractionIndexInput.name : `attractions[${document.querySelectorAll('.attraction-group').length -1}]`;
            const attractionIndexMatch = nameAttr.match(/attractions\[(\d+)\]/);
            const attractionIndex = attractionIndexMatch ? attractionIndexMatch[1] : (document.querySelectorAll('.attraction-group').length -1) ;

            const horaireCount = horairesContainer.querySelectorAll('.horaire-group').length;
            const newGroup = document.createElement('div');
            newGroup.className = 'item-group horaire-group';

            const idPrefix = `attractions_${attractionIndex}_horaires_${horaireCount}`;
            newGroup.innerHTML = `
                <div class="horaire-group-inputs">
                    <div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="attractions[${attractionIndex}][horaires][${horaireCount}][date]" required value="${dateVal}"><div class="error-message">Date requise.</div></div>
                    <div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="attractions[${attractionIndex}][horaires][${horaireCount}][heure_debut]" required value="${debutVal}"><div class="error-message">Heure de début requise.</div></div>
                    <div><label for="${idPrefix}_heure_fin">Fin *</label><input type="time" id="${idPrefix}_heure_fin" name="attractions[${attractionIndex}][horaires][${horaireCount}][heure_fin]" required value="${finVal}"><div class="error-message">Heure de fin requise.</div></div>
                </div>
            `;
            horairesContainer.appendChild(newGroup);
            setMinDateForInput(newGroup.querySelector('input[type="date"]')); // Set min date for new input
            addRemoveCrossIfNeeded(newGroup);
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        // Ajoute dynamiquement un champ pour saisir le nom d'un plat pour un restaurant.
        function addPlatRestaurant(event, nomVal = '') {
            if(event) event.preventDefault();
            const container = document.getElementById('plats-container-restaurant');
            const platGroups = container.querySelectorAll('.plat-group');

            if (platGroups.length >= 5) {
                const addPlatButton = document.getElementById('add-plat-restaurant');
                if(addPlatButton) addPlatButton.style.display = 'none';
                return;
            }

            const newGroup = document.createElement('div');
            newGroup.className = 'item-group plat-group';
            newGroup.innerHTML = `
                <label for="plat_nom_${platGroups.length}">Nom du plat ${platGroups.length + 1}</label>
                <input type="text" id="plat_nom_${platGroups.length}" name="plats[]" placeholder="Ex: Pizza Margherita" value="${nomVal}">
                <div class="error-message">Le nom du plat est requis si vous ajoutez une entrée pour celui-ci.</div>
            `;
            container.appendChild(newGroup);
            addRemoveCrossIfNeeded(newGroup);

            if (container.querySelectorAll('.plat-group').length >= 5) {
                 const addPlatButton = document.getElementById('add-plat-restaurant');
                 if(addPlatButton) addPlatButton.style.display = 'none';
            }
            if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
        }

        // Gère la suppression d'un groupe d'éléments dynamiques (comme un horaire, un service, une attraction ou un plat) lorsque sa croix de suppression est cliquée
        function handleRemoveElement(event) {
            event.preventDefault();
            const groupElement = event.target.closest('.item-group');
            if (groupElement) {
                const parentContainer = groupElement.parentNode;
                groupElement.remove();

                if (parentContainer.id === 'plats-container-restaurant') {
                    updateRemoveCrossVisibility(parentContainer, '.plat-group');
                    const addPlatButton = document.getElementById('add-plat-restaurant');
                    if (parentContainer.querySelectorAll('.plat-group').length < 5 && addPlatButton) {
                        addPlatButton.style.display = 'inline-block';
                    }
                } else if (parentContainer.id === 'attractions-container') {
                    updateRemoveCrossVisibility(parentContainer, '.attraction-group');
                } else if (parentContainer.id === 'horaires-container-activite') {
                     updateRemoveCrossVisibility(parentContainer, '.horaire-group');
                } else if (parentContainer.id === 'services-container-activite') {
                     updateRemoveCrossVisibility(parentContainer, '.service-group');
                } else if (parentContainer.classList.contains('horaires-container-attraction')) {
                     updateRemoveCrossVisibility(parentContainer, '.horaire-group');
                }
            }
        }

        // Tente de trouver et de retourner l'élément HTML destiné à afficher un message d'erreur pour un champ de formulaire donné
        function getErrorMessageElementForField(field) {
            let directSibling = field.nextElementSibling;
            if (directSibling && directSibling.classList.contains('error-message')) return directSibling;
            let parentDiv = field.closest('div');
            if(parentDiv) {
                let children = Array.from(parentDiv.children);
                let fieldIndex = children.indexOf(field);
                if (fieldIndex !== -1 && children[fieldIndex + 1] && children[fieldIndex + 1].classList.contains('error-message')) {
                    return children[fieldIndex + 1];
                }
                let parentSibling = parentDiv.nextElementSibling;
                if (parentSibling && parentSibling.classList.contains('error-message')) return parentSibling;
            }
            const label = document.querySelector(`label[for="${field.id}"]`);
            if(label) {
                let nextAfterLabel = label.nextElementSibling;
                if (nextAfterLabel && nextAfterLabel.id === field.id && nextAfterLabel.nextElementSibling && nextAfterLabel.nextElementSibling.classList.contains('error-message')) {
                    return nextAfterLabel.nextElementSibling;
                }
                 if (nextAfterLabel && nextAfterLabel.classList.contains('error-message')) return nextAfterLabel;
            }
            if (field.tagName.toLowerCase() === 'select' && field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                return field.nextElementSibling;
            }
            return null;
        }

        // vérifie la validité d'un champ de formulaire
        function validateField(field) {
            const errorMessageElement = getErrorMessageElementForField(field);
            let isValidField = true;
            const isVisible = field.offsetWidth > 0 || field.offsetHeight > 0 || field.getClientRects().length > 0 || field.type === 'hidden';

            if (isVisible || field.required) {
                 isValidField = field.checkValidity();
                if (field.type === 'file' && field.required && currentSelectedFiles.length === 0) isValidField = false;
                if (field.id === 'photos' && currentSelectedFiles.length > 6) isValidField = false;
                if (field.multiple && field.required && field.selectedOptions.length === 0) isValidField = false;
                
                // Check date field min attribute
                if (field.type === 'date' && field.min && field.value && field.value < field.min) {
                    isValidField = false;
                    if(errorMessageElement) errorMessageElement.textContent = "La date ne peut pas être antérieure à aujourd'hui.";
                }

            }

            if (isValidField) {
                field.classList.remove('invalid');
                if (errorMessageElement) errorMessageElement.style.display = 'none';
            } else {
                if (isVisible || field.required) {
                    field.classList.add('invalid');
                    if (errorMessageElement) {
                        if (field.id === 'photos' && currentSelectedFiles.length === 0 && field.required) {
                             errorMessageElement.textContent = "Veuillez ajouter au moins une photo.";
                        } else if (field.id === 'photos' && currentSelectedFiles.length > 6) {
                            errorMessageElement.textContent = "Vous ne pouvez sélectionner que 6 photos maximum.";
                        } else if (field.type === 'date' && field.min && field.value && field.value < field.min) {
                            // message already set above
                        }
                        errorMessageElement.style.display = 'block';
                    }
                } else {
                    field.classList.remove('invalid');
                    if (errorMessageElement) errorMessageElement.style.display = 'none';
                }
            }
            return isValidField;
        }

        // appelle validateField pour chacun afin de valider l'ensemble.
        function validateAllFields(containerOrForm) {
            let allValid = true;
            containerOrForm.querySelectorAll('input:not([type="button"]):not([type="submit"]), textarea, select').forEach(field => {
                const isVisible = field.offsetWidth > 0 || field.offsetHeight > 0 || field.getClientRects().length > 0 || field.type === 'hidden';
                if ( isVisible || field.required ) {
                    if (field.id === 'photos') {
                        if (!validatePhotosField()) allValid = false;
                    } else {
                        if (!validateField(field)) allValid = false;
                    }
                } else {
                    field.classList.remove('invalid');
                    const errorMessageElement = getErrorMessageElementForField(field);
                    if (errorMessageElement) errorMessageElement.style.display = 'none';
                }
            });
            return allValid;
        }

        // Gère la sélection de fichiers dans le champ de téléchargement de photos, met à jour la liste des fichiers sélectionnés, et déclenche l'affichage des prévisualisations et la validation
        function handlePhotoSelection(event) {
            const files = Array.from(event.target.files);
            const newFilesToAdd = [];
            files.forEach(file => {
                if (file.type.startsWith('image/') && currentSelectedFiles.length < 6) {
                    if (!currentSelectedFiles.some(existingFile => existingFile.name === file.name && existingFile.size === file.size)) {
                         newFilesToAdd.push(file);
                    }
                }
            });
            currentSelectedFiles.push(...newFilesToAdd);
            if (currentSelectedFiles.length > 6) {
                currentSelectedFiles = currentSelectedFiles.slice(0, 6);
            }
            renderPhotoPreviews();
            updateFileInput();
            validatePhotosField();
        }

        // Supprime image
        function removeImage(fileNameToRemove) {
            currentSelectedFiles = currentSelectedFiles.filter(file => file.name !== fileNameToRemove);
            renderPhotoPreviews();
            updateFileInput();
            validatePhotosField();
        }
        
        // Affiche les prévisualisations des images actuellement sélectionnées.
        function renderPhotoPreviews() {
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
        }
        
        // met à jour l'objet FileList du champ de saisie de fichier 
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            currentSelectedFiles.forEach(file => dataTransfer.items.add(file));
            photosInput.files = dataTransfer.files;
        }

        // affiche image dans une modal
        function openImageModal(src) {
            if (modalImageContent && imageModal && closeModalButton) {
                modalImageContent.src = src;
                imageModal.classList.add('show-modal');
            }
        }

        // ferme la modal
        function closeImageModal() {
             if (imageModal) {
                imageModal.classList.remove('show-modal');
             }
        }

        // Valide le champ de photos (nombre minimum/maximum de photos).
        function validatePhotosField() {
            const photosErrorMessage = document.getElementById('photos-error-message');
            let isValid = true;
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
            categorieSpecificFields.innerHTML = '';
            const tempFormData = new FormData(offerForm);
            const currentPostData = {};
             for (const [key, value] of tempFormData.entries()) {
                if (key.endsWith('[]')) {
                    const actualKey = key.slice(0, -2);
                    if (!currentPostData[actualKey]) currentPostData[actualKey] = [];
                    currentPostData[actualKey].push(value);
                } else {
                    currentPostData[key] = value;
                }
            }
            fetchCategorieFields(event.target.value, currentPostData);
        });


        window.onload = function () {
            // Set min attribute for main date input on load
            setMinDateForInput(mainDateInput);

            if (serverPostData.categorie) {
                categorieSelect.value = serverPostData.categorie;
                fetchCategorieFields(serverPostData.categorie, serverPostData);
            } else {
                 if(mainDateInputContainer) mainDateInputContainer.style.display = 'block';
                 if(mainDateInput) mainDateInput.required = true;
            }

            if (serverPostData.categorie === '2') {
                visiteGuideeSection.style.display = 'block';
                const visiteGuideeCheckbox = document.getElementById('visite_guidee');
                if (visiteGuideeCheckbox) {
                    const isChecked = serverPostData['visite_guidee'] === 'on' || serverPostData['visite_guidee'] === true;
                    visiteGuideeCheckbox.checked = isChecked;
                    languesGuideesDiv.style.display = isChecked ? 'block' : 'none';
                    languesSelect.required = isChecked;
                }
            }

            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($erreurs)): ?>
                offerForm.dataset.submitted = 'true';
                const phpErrors = <?php echo json_encode(array_keys($erreurs)); ?>;
                let firstInvalidFieldForFocus = null;

                phpErrors.forEach(fieldName => {
                    let field = document.getElementById(fieldName) ||
                                document.querySelector(`[name="${fieldName}"]`) ||
                                document.querySelector(`[name^="${fieldName}["]`);

                    if (field) {
                        field.classList.add('invalid');
                        const errorMessageElement = getErrorMessageElementForField(field);
                        if (errorMessageElement) {
                             errorMessageElement.style.display = 'block';
                        }
                        if (!firstInvalidFieldForFocus) firstInvalidFieldForFocus = field;
                    } else if (fieldName.startsWith('photos_') || fieldName === 'photos') {
                        const photosErrorMsgEl = document.getElementById('photos-error-message');
                        if (photosErrorMsgEl) {
                            photosErrorMsgEl.textContent = "<?php
                                if(isset($erreurs['photos_final_check'])) echo addslashes($erreurs['photos_final_check']);
                                elseif(isset($erreurs['photos_missing'])) echo addslashes($erreurs['photos_missing']);
                                elseif(isset($erreurs['photos_count'])) echo addslashes($erreurs['photos_count']);
                                elseif(isset($erreurs['photos_upload_dir'])) echo addslashes($erreurs['photos_upload_dir']);
                                elseif(isset($erreurs['photos_upload_permission'])) echo addslashes($erreurs['photos_upload_permission']);
                                else echo 'Erreur avec le téléchargement des photos.';
                            ?>";
                            photosErrorMsgEl.style.display = 'block';
                        }
                        if(photosInput) {
                            photosInput.classList.add('invalid');
                            if (!firstInvalidFieldForFocus) firstInvalidFieldForFocus = photosInput;
                        }
                    }
                });

                 if (firstInvalidFieldForFocus) {
                     firstInvalidFieldForFocus.scrollIntoView({ behavior: 'smooth', block: 'center' });
                 } else {
                     const generalErrorDisplay = document.querySelector('.error.message ul');
                     if (generalErrorDisplay) {
                         generalErrorDisplay.scrollIntoView({ behavior: 'smooth', block: 'center' });
                     }
                 }
            <?php endif; ?>

            offerForm.addEventListener('submit', (event) => {
                offerForm.dataset.submitted = 'true';
                if (!validateAllFields(offerForm)) {
                    event.preventDefault();
                    const firstInvalidField = offerForm.querySelector('.invalid');
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            if (offerForm.dataset.submitted === 'true') {
                validateAllFields(offerForm);
            }
        }
    </script>
</body>
</html>
