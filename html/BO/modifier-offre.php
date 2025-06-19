<?php
// 1. SÉCURISATION ET INITIALISATION
$current_pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
require_once __DIR__ . '/../composants/generate_uuid.php';
require_once __DIR__ . '/../../includes/db.php';

$offre_id = $_GET['id'] ?? null;
$edit_mode = !is_null($offre_id);
$offre_data_from_db = [];
$erreurs = [];

if (!$edit_mode) {
    header('Location: recherche.php');
    exit();
}

// 2. PRÉPARATION DES DONNÉES STATIQUES POUR LE FORMULAIRE (ex: langues)
$langues_options_html = '';
$all_langues_db = [];
try {
    $all_langues_db = $pdo->query("SELECT language, language FROM langues ORDER BY language ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($all_langues_db as $code => $nom) {
        $lang_html_code = htmlspecialchars($code);
        // Ici, on pourrait avoir un mapping pour des noms plus conviviaux
        $lang_html_nom = htmlspecialchars(ucfirst($code));
        $langues_options_html .= "<option value=\"$lang_html_code\">$lang_html_nom</option>";
    }
} catch (Exception $e) {
    $langues_options_html = '<option value="">Erreur de chargement</option>';
    $erreurs['db_static'] = "Impossible de charger les options de langue.";
}

// 3. LOGIQUE DE TRAITEMENT DU FORMULAIRE (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($pdo)) {

    $posted_offre_id = $_POST['offre_id'] ?? null;
    if ($posted_offre_id !== $offre_id) {
        die("Erreur : Incohérence des ID d'offre.");
    }

    // Reprise de la validation complète de publier-une-offre.php
    function validate_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // --- VALIDATION COMPLÈTE DES CHAMPS ---
    $titre = validate_input($_POST["titre"] ?? '');
    if (empty($titre)) {
        $erreurs["titre"] = "Veuillez entrer un titre.";
    }
    $prix_str = $_POST["prix"] ?? '';
    $prix = null;
    if (empty($prix_str)) {
        $erreurs["prix"] = "Veuillez entrer un prix.";
    } elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $prix_str)) {
        $erreurs["prix"] = "Prix invalide (ex: 10, 10.50).";
    } else {
        $prix = floatval($prix_str);
    }
    $coordonnees_telephoniques = null;
    if (!empty($_POST["coordonnees_telephoniques"])) {
        if (!preg_match("/^0[1-9]\d{8}$/", $_POST["coordonnees_telephoniques"])) {
            $erreurs["coordonnees_telephoniques"] = "Numéro de téléphone invalide.";
        } else {
            $coordonnees_telephoniques = validate_input($_POST["coordonnees_telephoniques"]);
        }
    }
    $resume = validate_input($_POST["resume"] ?? '');
    if (empty($resume)) {
        $erreurs["resume"] = "Veuillez entrer un résumé.";
    }
    $description = !empty($_POST["description"]) ? validate_input($_POST["description"]) : null;
    $conditions_accessibilite = validate_input($_POST["conditions_accessibilite"] ?? '');
    if (empty($conditions_accessibilite)) {
        $erreurs["conditions_accessibilite"] = "Veuillez entrer les conditions d'accessibilité.";
    }
    $ligne_adresse = validate_input($_POST["ligne_adresse"] ?? '');
    if (empty($ligne_adresse)) $erreurs["ligne_adresse"] = "Veuillez entrer la ligne d'adresse.";
    $ville = validate_input($_POST["ville"] ?? '');
    if (empty($ville)) $erreurs["ville"] = "Veuillez entrer la ville.";
    $code_postal = validate_input($_POST["code_postal"] ?? '');
    if (empty($code_postal)) {
        $erreurs["code_postal"] = "Veuillez entrer un code postal.";
    } elseif (!preg_match("/^\d{5}$/", $code_postal)) {
        $erreurs["code_postal"] = "Code postal invalide (5 chiffres).";
    }
    $site_web = null;
    if (!empty($_POST["site"])) {
        $site_input = validate_input($_POST["site"]);
        if (!filter_var($site_input, FILTER_VALIDATE_URL)) {
            $erreurs["site"] = "URL de site web invalide.";
        } else {
            $site_web = $site_input;
        }
    }

    // Définir $categorie_type_enum_for_new_row pour la logique de mise à jour spécifique
    $categorie_type_enum_for_new_row = $_POST['categorie_type_enum'] ?? null;
    $date_offre_principale = null;
    // La gestion de la date principale est basée sur la catégorie, comme dans publier-une-offre.php
    $isDateHandledByCategory = in_array($categorie_type_enum_for_new_row, ['activite', 'spectacle', 'parc_attractions']);

    if (!$isDateHandledByCategory && !empty($_POST["date"])) {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $_POST["date"])) {
            $erreurs["date"] = "Format de date invalide pour l'offre. Utilisez YYYY-MM-DD.";
        } else {
            $date_obj = DateTime::createFromFormat('Y-m-d', $_POST["date"]);
            if ($date_obj && $date_obj->format('Y-m-d') === $_POST["date"]) {
                // Si la date est dans le passé et n'est pas aujourd'hui
                if (new DateTime() > $date_obj && $date_obj->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
                    $erreurs["date_passee"] = "La date de l'offre ne peut pas être une date passée.";
                } else {
                    $date_offre_principale = $_POST["date"];
                }
            } else {
                $erreurs["date"] = "Date de l'offre invalide.";
            }
        }
    } elseif (!$isDateHandledByCategory && empty($_POST["date"]) && ($categorie_type_enum_for_new_row == 'visite')) {
        // Pour une visite, la date est requise
        if ($categorie_type_enum_for_new_row == 'visite') {
            $erreurs["date"] = "Veuillez entrer une date pour la visite.";
        }
    }

    // --- GESTION COMPLÈTE DES PHOTOS ---
    $photo_paths_for_db = [];
    $deleted_photos_ids = !empty($_POST['deleted_photos']) ? explode(',', $_POST['deleted_photos']) : [];

    // Récupérer le nombre de photos existantes (avant suppression et ajout)
    $stmt_existing_photos_count = $pdo->prepare("SELECT COUNT(id) FROM photos_offres WHERE offre_id = ?");
    $stmt_existing_photos_count->execute([$posted_offre_id]);
    $existing_photos_count = $stmt_existing_photos_count->fetchColumn();

    // Calculer le nombre de photos restantes après suppressions
    $photos_after_delete = $existing_photos_count - count($deleted_photos_ids);

    $total_photos_uploaded = 0;
    if (isset($_FILES["photos"]["name"]) && is_array($_FILES["photos"]["name"])) {
        foreach ($_FILES["photos"]["name"] as $filename) {
            if (!empty($filename)) {
                $total_photos_uploaded++;
            }
        }
    }

    // Vérification des contraintes de nombre de photos
    if (($photos_after_delete + $total_photos_uploaded) === 0) {
        $erreurs["photos_missing"] = "Une offre doit avoir au moins une photo.";
    } elseif (($photos_after_delete + $total_photos_uploaded) > 6) {
        $erreurs["photos_count"] = "Vous ne pouvez avoir que 6 photos maximum au total.";
    }

    // Traitement des nouvelles photos téléchargées
    if ($total_photos_uploaded > 0 && empty($erreurs)) {
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
                        if ($file_size <= 40000000) { // 40MB max
                            $new_file_name = uniqid('offre_', true) . '.' . $file_extension;
                            $dest_path_absolute = $target_dir_absolute . $new_file_name;
                            if (move_uploaded_file($file_tmp_path, $dest_path_absolute)) {
                                $relative_path = $target_dir_relative . $new_file_name;
                                $photo_paths_for_db[] = $relative_path;
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
    // Vérification finale si des erreurs de photos ont été rencontrées
    if (empty($photo_paths_for_db) && empty($erreurs['photos_missing']) && empty($erreurs['photos_count']) && empty($erreurs['photos_upload_dir']) && empty($erreurs['photos_upload_permission']) && !preg_grep('/^photos_upload_move_/', array_keys($erreurs)) && !preg_grep('/^photos_size_/', array_keys($erreurs)) && !preg_grep('/^photos_type_/', array_keys($erreurs)) && !preg_grep('/^photos_upload_error_/', array_keys($erreurs)) && ($photos_after_delete + $total_photos_uploaded) === 0) {
        $erreurs["photos_final_check"] = "Au moins une photo valide est requise et doit être correctement traitée.";
    }


    // --- DATABASE UPDATE ---
    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare("SELECT id, adresse_id, categorie_id FROM offres WHERE id = :id AND pro_id = :pro_id");
            $stmtCheck->execute([':id' => $posted_offre_id, ':pro_id' => $current_pro_id]);
            $existing_offer = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$existing_offer) {
                throw new Exception("Modification non autorisée.");
            }

            $pdo->prepare("UPDATE adresses SET street = ?, postal_code = ?, city = ? WHERE id = ?")->execute([$ligne_adresse, $code_postal, $ville, $existing_offer['adresse_id']]);

            // Suppression des photos marquées pour suppression
            if (!empty($deleted_photos_ids)) {
                $placeholders = implode(',', array_fill(0, count($deleted_photos_ids), '?'));
                $stmt_get_urls = $pdo->prepare("SELECT url FROM photos_offres WHERE id IN ($placeholders) AND offre_id = ?");
                $stmt_get_urls->execute(array_merge($deleted_photos_ids, [$posted_offre_id]));
                $urls_to_delete = $stmt_get_urls->fetchAll(PDO::FETCH_COLUMN);
                foreach ($urls_to_delete as $url_to_delete) {
                    $full_path = realpath(__DIR__ . '/../../') . '/' . $url_to_delete;
                    if (file_exists($full_path)) {
                        @unlink($full_path);
                    } // Supprimer le fichier physique
                }
                $pdo->prepare("DELETE FROM photos_offres WHERE id IN ($placeholders) AND offre_id = ?")->execute(array_merge($deleted_photos_ids, [$posted_offre_id]));
            }

            // Insertion des nouvelles photos téléchargées
            if (!empty($photo_paths_for_db)) {
                $stmtPhoto = $pdo->prepare("INSERT INTO photos_offres (id, offre_id, url) VALUES (?, ?, ?)");
                foreach ($photo_paths_for_db as $path) {
                    $stmtPhoto->execute([generate_uuid(), $posted_offre_id, $path]);
                }
            }

            // Déterminer la nouvelle photo principale après les ajouts/suppressions
            // (La première photo par ordre d'ID dans la table devient la principale)
            $stmt_main_photo = $pdo->prepare("SELECT url FROM photos_offres WHERE offre_id = ? ORDER BY id ASC LIMIT 1");
            $stmt_main_photo->execute([$posted_offre_id]);
            $main_photo_to_set = $stmt_main_photo->fetchColumn();

            $pdo->prepare("UPDATE offres SET title=?, summary=?, description=?, main_photo=?, accessibility=?, website=?, phone=?, price=?, updated_at=NOW() WHERE id=?")
                ->execute([$titre, $resume, $description, $main_photo_to_set, $conditions_accessibilite, $site_web, $coordonnees_telephoniques, $prix, $posted_offre_id]);

            // --- LOGIQUE DE MISE À JOUR SPÉCIFIQUE À LA CATÉGORIE ---
            $categorie_id_for_joins = $existing_offer['categorie_id'];
            $categorie_type = $_POST['categorie_type_enum'];

            switch ($categorie_type) {
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
                        // Mise à jour de la table activites
                        $stmtUpdateAct = $pdo->prepare("UPDATE activites SET duration = :duration, minimum_price = :min_price, required_age = :req_age WHERE categorie_id = :cat_id");
                        $stmtUpdateAct->execute([
                            ':cat_id' => $categorie_id_for_joins,
                            ':duration' => $duree_activite,
                            ':min_price' => $prix_min_act ?? $prix,
                            ':req_age' => $age_req_act ?? 0
                        ]);

                        // Horaires : supprimer tout et réinsérer
                        $pdo->prepare("DELETE FROM horaires_activites WHERE activite_id = ?")->execute([$categorie_id_for_joins]);
                        if (isset($_POST['activites'][0]['horaires']) && is_array($_POST['activites'][0]['horaires'])) {
                            $stmtHoraireAct = $pdo->prepare("INSERT INTO horaires_activites (id, activite_id, date, start_time) VALUES (:id, :act_id, :date, :start_time)");
                            foreach ($_POST['activites'][0]['horaires'] as $key => $horaire_data) {
                                $h_date = validate_input($horaire_data['date'] ?? '');
                                $h_debut = validate_input($horaire_data['heure_debut'] ?? '');

                                if (empty($h_date)) $erreurs["horaire_activite_date_" . $key] = "Date requise pour l'horaire d'activité " . ($key + 1) . ".";
                                elseif (new DateTime() > new DateTime($h_date) && (new DateTime($h_date))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
                                    $erreurs["horaire_activite_date_passee_" . $key] = "La date de l'horaire d'activité " . ($key + 1) . " ne peut pas être passée.";
                                }

                                if (empty($h_debut)) $erreurs["horaire_activite_debut_" . $key] = "Heure de début requise pour l'horaire d'activité " . ($key + 1) . ".";

                                if (!empty($h_date) && !empty($h_debut) && !isset($erreurs["horaire_activite_date_passee_" . $key])) {
                                    $horaire_act_id_uuid = generate_uuid();
                                    $stmtHoraireAct->execute([
                                        ':id' => $horaire_act_id_uuid,
                                        ':act_id' => $categorie_id_for_joins,
                                        ':date' => $h_date,
                                        ':start_time' => $h_debut
                                    ]);
                                }
                            }
                        } else {
                            $erreurs["horaires_activite_manquants"] = "Au moins un horaire est requis pour l'activité.";
                        }

                        // Services : supprimer tout et réinsérer
                        $pdo->prepare("DELETE FROM activites_prestations_incluses WHERE activite_id = ?")->execute([$categorie_id_for_joins]);
                        $pdo->prepare("DELETE FROM activites_prestations_non_incluses WHERE activite_id = ?")->execute([$categorie_id_for_joins]);
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
                                        $stmtActPrestaInclus->execute([':act_id' => $categorie_id_for_joins, ':presta_id' => $prestation_id_uuid]);
                                    } else {
                                        $stmtActPrestaNonInclus->execute([':act_id' => $categorie_id_for_joins, ':presta_id' => $prestation_id_uuid]);
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
                        $stmtVis = $pdo->prepare("UPDATE visites SET duration=?, minimum_price=?, date=?, start_time=?, is_guided_tour=? WHERE categorie_id=?");
                        $stmtVis->execute([
                            $duree_visite_minutes,
                            $prix_min_vis ?? $prix,
                            $date_offre_principale,
                            $heure_debut_visite,
                            $visite_guidee,
                            $categorie_id_for_joins
                        ]);

                        $pdo->prepare("DELETE FROM visites_langues WHERE visite_id = ?")->execute([$categorie_id_for_joins]);
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
                                        $stmtVisLangInsert->execute([':vis_id' => $categorie_id_for_joins, ':lang_id' => $lang_row['id']]);
                                    } else {
                                        $erreurs["langue_inconnue_" . $lang_code_valide] = "La langue '" . $lang_code_valide . "' n'est pas configurée.";
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
                            if (new DateTime() > $date_obj && $date_obj->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
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
                        $stmtSpec = $pdo->prepare("UPDATE spectacles SET duration = :duration, minimum_price = :min_price, date = :date, start_time = :start_time, capacity = :capacity WHERE categorie_id = :cat_id");
                        $stmtSpec->execute([
                            ':cat_id' => $categorie_id_for_joins,
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
                        $stmtParc = $pdo->prepare("UPDATE parcs_attractions SET minimum_price = :min_price, required_age = :req_age, attraction_nb = :attr_nb, map_url = :map_url WHERE categorie_id = :cat_id");
                        $stmtParc->execute([
                            ':cat_id' => $categorie_id_for_joins,
                            ':min_price' => $prix_min_parc ?? $prix,
                            ':req_age' => $age_req_parc ?? 0,
                            ':attr_nb' => $nb_attr_parc ?? 0,
                            ':map_url' => $maps_url_parc
                        ]);

                        // Attractions : supprimer tout et réinsérer
                        // Récupérer les IDs des attractions liées à ce parc
                        $stmt_get_attraction_ids = $pdo->prepare("SELECT id FROM attractions WHERE parc_attractions_id = ?");
                        $stmt_get_attraction_ids->execute([$categorie_id_for_joins]);
                        $attraction_ids_to_delete = $stmt_get_attraction_ids->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($attraction_ids_to_delete)) {
                            $placeholders_attr = implode(',', array_fill(0, count($attraction_ids_to_delete), '?'));
                            $pdo->prepare("DELETE FROM horaires_attractions WHERE attraction_id IN ($placeholders_attr)")->execute($attraction_ids_to_delete);
                        }
                        $pdo->prepare("DELETE FROM attractions WHERE parc_attractions_id = ?")->execute([$categorie_id_for_joins]);

                        if (isset($_POST['attractions']) && is_array($_POST['attractions'])) {
                            $stmtAttractionInsert = $pdo->prepare("INSERT INTO attractions (id, parc_attractions_id, name) VALUES (:id, :parc_id, :name)");
                            $stmtHoraireAttrInsert = $pdo->prepare("INSERT INTO horaires_attractions (id, attraction_id, day, start_time, end_time) VALUES (:id, :attr_id, :day, :start_time, :end_time)");

                            foreach ($_POST['attractions'] as $attr_key => $attr_data) {
                                $attr_nom = validate_input($attr_data['nom_attraction'] ?? '');
                                if (empty($attr_nom)) {
                                    $erreurs["attraction_nom_" . $attr_key] = "Nom requis pour l'attraction " . ($attr_key + 1) . ".";
                                    continue;
                                }
                                $attraction_id_uuid = generate_uuid();
                                $stmtAttractionInsert->execute([
                                    ':id' => $attraction_id_uuid,
                                    ':parc_id' => $categorie_id_for_joins,
                                    ':name' => $attr_nom
                                ]);

                                if (isset($attr_data['horaires']) && is_array($attr_data['horaires'])) {
                                    foreach ($attr_data['horaires'] as $h_key => $h_data) {
                                        $h_date_str = validate_input($h_data['date'] ?? '');
                                        $h_debut = validate_input($h_data['heure_debut'] ?? '');
                                        $h_fin = validate_input($h_data['heure_fin'] ?? '');
                                        $day_of_week_for_db = null;

                                        if (empty($h_date_str)) $erreurs["attraction_" . $attr_key . "_horaire_date_" . $h_key] = "Date requise pour l'horaire de " . $attr_nom . ".";
                                        elseif (new DateTime() > new DateTime($h_date_str) && (new DateTime($h_date_str))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')) {
                                            $erreurs["attraction_" . $attr_key . "_horaire_date_passee_" . $h_key] = "La date de l'horaire de l'attraction " . $attr_nom . " ne peut pas être passée.";
                                        }

                                        if (empty($h_debut)) $erreurs["attraction_" . $attr_key . "_horaire_debut_" . $h_key] = "Début requis pour l'horaire de " . $attr_nom . ".";
                                        if (empty($h_fin)) $erreurs["attraction_" . $attr_key . "_horaire_fin_" . $h_key] = "Fin requise pour l'horaire de " . $attr_nom . ".";

                                        if (!empty($h_date_str) && !empty($h_debut) && !empty($h_fin) && !isset($erreurs["attraction_" . $attr_key . "_horaire_date_passee_" . $h_key])) {
                                            try {
                                                $date_obj_attr = new DateTime($h_date_str);
                                                $day_of_week_php = $date_obj_attr->format('l');
                                                switch (strtolower($day_of_week_php)) {
                                                    case 'monday':
                                                        $day_of_week_for_db = 'lundi';
                                                        break;
                                                    case 'tuesday':
                                                        $day_of_week_for_db = 'mardi';
                                                        break;
                                                    case 'wednesday':
                                                        $day_of_week_for_db = 'mercredi';
                                                        break;
                                                    case 'thursday':
                                                        $day_of_week_for_db = 'jeudi';
                                                        break;
                                                    case 'friday':
                                                        $day_of_week_for_db = 'vendredi';
                                                        break;
                                                    case 'saturday':
                                                        $day_of_week_for_db = 'samedi';
                                                        break;
                                                    case 'sunday':
                                                        $day_of_week_for_db = 'dimanche';
                                                        break;
                                                    default:
                                                        $erreurs["attraction_" . $attr_key . "_horaire_day_invalid_" . $h_key] = "Jour de la semaine invalide pour l'horaire de " . $attr_nom . ".";
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
                                                $erreurs["attraction_" . $attr_key . "_horaire_date_parse_" . $h_key] = "Format de date invalide pour l'horaire de " . $attr_nom . ".";
                                            }
                                        }
                                    }
                                } else {
                                    $erreurs["attraction_" . $attr_key . "_horaires_manquants"] = "Au moins un horaire complet est requis pour l'attraction " . $attr_nom . ".";
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
                        $stmtResto = $pdo->prepare("UPDATE restaurations SET menu_url = :menu_url, price_range = :price_range WHERE categorie_id = :cat_id");
                        $stmtResto->execute([
                            ':cat_id' => $categorie_id_for_joins,
                            ':menu_url' => $menu_url_resto,
                            ':price_range' => $prix_range_enum
                        ]);

                        // Plats : supprimer tout et réinsérer
                        $pdo->prepare("DELETE FROM restaurations_repas WHERE restauration_id = ?")->execute([$categorie_id_for_joins]);
                        if (isset($_POST['plats']) && is_array($_POST['plats'])) {
                            if (count($_POST['plats']) > 0 && !empty(array_filter($_POST['plats']))) {
                                $stmtRepasSearch = $pdo->prepare("SELECT id FROM repas WHERE name = :name LIMIT 1");
                                $stmtRepasInsert = $pdo->prepare("INSERT INTO repas (id, name) VALUES (:id, :name)");
                                $stmtRestoRepasInsert = $pdo->prepare("INSERT INTO restaurations_repas (restauration_id, repas_id) VALUES (:resto_id, :repas_id)");

                                foreach ($_POST['plats'] as $idx => $plat_nom_input) {
                                    $plat_nom = validate_input($plat_nom_input);
                                    if (empty($plat_nom)) {
                                        if (count($_POST['plats']) > 1 || !empty(array_filter(array_slice($_POST['plats'], $idx + 1)))) {
                                            $erreurs["plat_restaurant_nom_" . $idx] = "Le nom du plat " . ($idx + 1) . " est requis s'il est ajouté.";
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
                                        ':resto_id' => $categorie_id_for_joins,
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
                header("Location: offre.php?id=" . $offre_id . "&update=success");
                exit();
            }
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database Update Error: " . $e->getMessage() . " - Data: " . json_encode($_POST));
            if ($e->getCode() == '23000') {
                $erreurs["db_general"] = "Une erreur de contrainte de base de données est survenue (ex: duplicata). Détail: " . $e->getMessage();
            } else {
                $erreurs["db_general"] = "Une erreur est survenue lors de la mise à jour de votre offre : " . $e->getMessage() . ". Veuillez réessayer. ";
            }
        }
    }
}

// 5. CHARGEMENT DES DONNÉES POUR L'AFFICHAGE (GET)
if ($edit_mode && empty($_POST)) { // Si c'est un chargement initial (pas après une soumission POST avec erreurs)
    try {
        $stmt = $pdo->prepare("SELECT o.*, a.street, a.postal_code, a.city, c.type as categorie_type_enum, c.id as categorie_id_for_joins FROM offres o JOIN adresses a ON o.adresse_id = a.id JOIN categories c ON o.categorie_id = c.id WHERE o.id = :offre_id AND o.pro_id = :current_pro_id");
        $stmt->execute([':offre_id' => $offre_id, ':current_pro_id' => $current_pro_id]);
        $offre_data_from_db['main'] = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offre_data_from_db['main']) {
            header('Location: recherche.php?error=not_found');
            exit();
        }

        $categorie_id = $offre_data_from_db['main']['categorie_id_for_joins'];
        $type = $offre_data_from_db['main']['categorie_type_enum'];

        $specific_table_map = ['activite' => 'activites', 'visite' => 'visites', 'spectacle' => 'spectacles', 'parc_attractions' => 'parcs_attractions', 'restauration' => 'restaurations'];
        if (array_key_exists($type, $specific_table_map)) {
            $stmt_specific = $pdo->prepare("SELECT * FROM `{$specific_table_map[$type]}` WHERE categorie_id = :cat_id");
            $stmt_specific->execute([':cat_id' => $categorie_id]);
            $offre_data_from_db['specific'] = $stmt_specific->fetch(PDO::FETCH_ASSOC);
        }

        $stmt_photos = $pdo->prepare("SELECT id, url FROM photos_offres WHERE offre_id = :offre_id ORDER BY id");
        $stmt_photos->execute([':offre_id' => $offre_id]);
        $offre_data_from_db['photos'] = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);

        // --- CHARGEMENT COMPLET DES DONNÉES SPÉCIFIQUES ---
        switch ($type) {
            case 'activite':
                // Horaires
                $stmt_h = $pdo->prepare("SELECT date, start_time as heure_debut FROM horaires_activites WHERE activite_id = ? ORDER BY date, start_time");
                $stmt_h->execute([$categorie_id]);
                $offre_data_from_db['specific']['horaires'] = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
                // Services
                $stmt_s_inclus = $pdo->prepare("SELECT p.name as nom_service FROM activites_prestations_incluses api JOIN prestations p ON api.prestation_id = p.id WHERE api.activite_id = ?");
                $stmt_s_inclus->execute([$categorie_id]);
                $services_inclus = $stmt_s_inclus->fetchAll(PDO::FETCH_ASSOC);

                $stmt_s_non_inclus = $pdo->prepare("SELECT p.name as nom_service FROM activites_prestations_non_incluses apni JOIN prestations p ON apni.prestation_id = p.id WHERE apni.activite_id = ?");
                $stmt_s_non_inclus->execute([$categorie_id]);
                $services_non_inclus = $stmt_s_non_inclus->fetchAll(PDO::FETCH_ASSOC);

                $offre_data_from_db['specific']['services'] = [];
                foreach ($services_inclus as $s) {
                    $offre_data_from_db['specific']['services'][] = ['nom_service' => $s['nom_service'], 'inclusion' => 'on'];
                }
                foreach ($services_non_inclus as $s) {
                    $offre_data_from_db['specific']['services'][] = ['nom_service' => $s['nom_service'], 'inclusion' => null];
                }
                break;
            case 'visite':
                $stmt_l = $pdo->prepare("SELECT l.language FROM visites_langues vl JOIN langues l ON vl.langue_id = l.id WHERE vl.visite_id = ?");
                $stmt_l->execute([$categorie_id]);
                $offre_data_from_db['specific']['langues'] = $stmt_l->fetchAll(PDO::FETCH_COLUMN, 0);
                break;
            case 'parc_attractions':
                $stmt_a = $pdo->prepare("SELECT id, name as nom_attraction FROM attractions WHERE parc_attractions_id = ?");
                $stmt_a->execute([$categorie_id]);
                $attractions = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
                $stmt_h_a = $pdo->prepare("SELECT day, start_time as heure_debut, end_time as heure_fin FROM horaires_attractions WHERE attraction_id = ?");
                foreach ($attractions as $key => $attraction) {
                    $stmt_h_a->execute([$attraction['id']]);
                    $attractions[$key]['horaires'] = $stmt_h_a->fetchAll(PDO::FETCH_ASSOC);
                }
                $offre_data_from_db['specific']['attractions'] = $attractions;
                break;
            case 'restauration':
                $stmt_p = $pdo->prepare("SELECT rep.name FROM restaurations_repas rr JOIN repas rep ON rr.repas_id = rep.id WHERE rr.restauration_id = ?");
                $stmt_p->execute([$categorie_id]);
                $offre_data_from_db['specific']['plats'] = $stmt_p->fetchAll(PDO::FETCH_COLUMN, 0);
                break;
        }
    } catch (Exception $e) {
        die("Erreur critique lors de la récupération des données : " . $e->getMessage());
    }
}

// 6. PRÉPARATION FINALE DES DONNÉES POUR JAVASCRIPT
$js_data = [];
if ($edit_mode && empty($_POST)) { // Si c'est la première fois qu'on charge la page (GET)
    $main = $offre_data_from_db['main'] ?? [];
    $specific = $offre_data_from_db['specific'] ?? [];
    $js_data = [
        'titre' => $main['title'] ?? '',
        'prix' => $main['price'] ?? '',
        'coordonnees_telephoniques' => $main['phone'] ?? '',
        'resume' => $main['summary'] ?? '',
        'description' => $main['description'] ?? '',
        'conditions_accessibilite' => $main['accessibility'] ?? '',
        'site' => $main['website'] ?? '',
        'ligne_adresse' => $main['street'] ?? '',
        'ville' => $main['city'] ?? '',
        'code_postal' => $main['postal_code'] ?? '',
        'categorie_type_enum' => $main['categorie_type_enum'] ?? '', // Cette valeur est cruciale pour le JS
        'date' => $main['categorie_type_enum'] === 'visite' ? ($specific['date'] ?? null) : null, // Seule la visite utilise la date principale de l'offre

        // Activité
        'duree' => $specific['duration'] ?? null,
        'prix_minimum_activite' => $specific['minimum_price'] ?? null,
        'age_requis_activite' => $specific['required_age'] ?? null,
        'activites' => [['horaires' => $specific['horaires'] ?? [], 'services' => $specific['services'] ?? []]],

        // Visite
        'duree_visite' => isset($specific['duration']) && $main['categorie_type_enum'] === 'visite' ? $specific['duration'] / 60 : null,
        'heure_debut_visite' => $specific['start_time'] ?? null,
        'visite_guidee' => ($specific['is_guided_tour'] ?? 0) == 1 ? 'on' : null,
        'langues' => $specific['langues'] ?? [],

        // Spectacle
        'duree_spectacle' => $specific['duration'] ?? null,
        'prix_minimum_spectacle' => $specific['minimum_price'] ?? null,
        'date_spectacle' => $specific['date'] ?? null,
        'heure_debut_spectacle' => $specific['start_time'] ?? null,
        'capacite_spectacle' => $specific['capacity'] ?? null,

        // Parc
        'prix_minimum_parc' => $specific['minimum_price'] ?? null,
        'age_requis_parc' => $specific['required_age'] ?? null,
        'nombre_total_attractions_parc' => $specific['attraction_nb'] ?? null,
        'maps_url_parc' => $specific['map_url'] ?? null,
        'attractions' => $specific['attractions'] ?? [],

        // Restauration
        'lien_menu_restaurant' => $specific['menu_url'] ?? null,
        'prix_moyen_restaurant' => null, // La BD stocke la range, pas le prix moyen. Pour l'affichage, on ne peut pas le reconstituer directement.
        'plats' => $specific['plats'] ?? [],
    ];
} elseif (!empty($_POST)) {
    // Si c'est une soumission POST qui a échoué, on utilise les données postées
    $js_data = $_POST;
    // La catégorie type enum est cruciale pour que le JS puisse reconstruire la page
    $js_data['categorie_type_enum'] = $_POST['categorie_type_enum'] ?? null;
    // Reconstruire les structures complexes comme activites[0][horaires]
    if (isset($_POST['activites'])) {
        $js_data['activites'] = $_POST['activites'];
    }
    if (isset($_POST['attractions'])) {
        $js_data['attractions'] = $_POST['attractions'];
    }
    if (isset($_POST['plats'])) {
        $js_data['plats'] = $_POST['plats'];
    }
    if (isset($_POST['langues'])) {
        $js_data['langues'] = $_POST['langues'];
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier votre offre</title>
    <link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-section {
            padding: var(--espacement-double);
            border-bottom: var(--bordure-standard-interface);
        }

        .form-section:last-of-type {
            border-bottom: none;
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

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group {
            margin-bottom: var(--espacement-double);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: var(--espacement-standard);
            margin-bottom: var(--espacement-double);
            border-radius: var(--border-radius-bouton);
        }

        .error ul {
            margin: 0;
            padding-left: 20px;
        }

        form#offer-form {
            max-width: 660px;
            margin: var(--espacement-double) auto;
            background-color: #fff;
            padding: var(--espacement-double);
            border: 1px solid #ddd;
            border-radius: var(--border-radius-standard);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }

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
            transition: background-color 0.3s ease;
        }

        form#offer-form button[type="submit"]:hover {
            background-color: var(--couleur-principale-hover);
        }

        #image-preview-container,
        #existing-image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: var(--espacement-standard);
            margin-top: var(--espacement-standard);
            margin-bottom: var(--espacement-double);
        }

        .preview-item {
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

        .preview-item img {
            max-width: 100%;
            max-height: 100%;
            display: block;
        }

        .delete-image-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 22px;
            height: 22px;
            background-color: rgba(0, 0, 0, 0.6);
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

        .delete-image-btn:hover {
            background-color: rgba(220, 53, 69, 0.9);
        }

        #categorie-specific-fields .dynamic-section-subtitle {
            margin-top: var(--espacement-moyen);
            margin-bottom: var(--espacement-standard);
            color: var(--couleur-texte);
            font-size: 1.1em;
            font-weight: var(--font-weight-semibold);
            padding-bottom: var(--espacement-petit)
        }

        #categorie-specific-fields .item-group {
            position: relative;
            border: 1px solid var(--couleur-bordure);
            padding: var(--espacement-moyen);
            padding-top: calc(var(--espacement-moyen) + 20px);
            margin-bottom: var(--espacement-moyen);
            border-radius: var(--border-radius-standard);
            background-color: #f9f9f9
        }

        .text-add-link {
            background: none !important;
            border: none !important;
            padding: var(--espacement-standard) 0 !important;
            color: var(--couleur-principale);
            cursor: pointer;
            text-decoration: none;
            font-size: .9em;
            font-weight: var(--font-weight-medium);
            margin-top: var(--espacement-moyen);
            display: inline-block
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
            transition: color .2s ease, background-color .2s ease
        }

        #categorie-specific-fields .horaire-group-inputs {
            display: flex;
            flex-wrap: wrap;
            gap: var(--espacement-standard);
            align-items: flex-end;
            margin-bottom: var(--espacement-petit)
        }

        #categorie-specific-fields .horaire-group-inputs>div {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-width: 120px
        }

        /* --- STYLES POUR LA NOTIFICATION PROFIL --- */

        .main-nav ul li.nav-item-with-notification {
            position: relative;
            /* Contexte pour le positionnement absolu de la bulle */
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .header-right .profile-link-container+.btn-primary {
            margin-left: 1rem;
        }

        .nav-item-with-notification .notification-bubble {
            position: absolute;
            top: -15px;
            /* Ajustez pour la position verticale */
            right: 80px;
            /* Ajustez pour la position horizontale */
            width: 20px;
            height: 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75em;
            /* Police un peu plus petite pour la nav */
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
        <div class="container">
            <form id="offer-form" method="post" action="modifier-offre.php?id=<?php echo htmlspecialchars($offre_id); ?>" enctype="multipart/form-data" novalidate>
                <h1 style="text-align: center; padding: 2rem 1rem 0;">Modifier votre offre</h1>

                <?php if (!empty($erreurs)): ?>
                    <div class='error form-section'><strong>Veuillez corriger les erreurs suivantes :</strong>
                        <ul>
                            <?php foreach ($erreurs as $erreur_key => $erreur_msg): // Afficher les clés d'erreur pour le débogage 
                            ?>
                                <li><?php echo htmlspecialchars($erreur_msg); ?> (Champ/Info: <?php echo htmlspecialchars($erreur_key); ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="offre_id" value="<?php echo htmlspecialchars($offre_id); ?>">
                <input type="hidden" name="categorie_type_enum" value="<?php echo htmlspecialchars($js_data['categorie_type_enum'] ?? ''); ?>">


                <div class="form-section">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" required value="<?php echo htmlspecialchars($js_data['titre'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer un titre pour votre offre.</div>

                    <label for="prix">Prix *</label>
                    <input type="text" id="prix" name="prix" pattern="^\d+(\.\d{1,2})?$" required value="<?php echo htmlspecialchars($js_data['prix'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer un prix valide (ex: 10, 10.50).</div>

                    <label for="coordonnees_telephoniques">Coordonnées téléphoniques</label>
                    <input type="text" id="coordonnees_telephoniques" name="coordonnees_telephoniques"
                        pattern="^0[1-9]\d{8}$" value="<?php echo htmlspecialchars($js_data['coordonnees_telephoniques'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer un numéro de téléphone à 10 chiffres commençant par 0.</div>

                    <label for="resume">Résumé *</label>
                    <textarea id="resume" name="resume" required><?php echo htmlspecialchars($js_data['resume'] ?? ''); ?></textarea>
                    <div class="error-message">Veuillez entrer un résumé de votre offre.</div>

                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($js_data['description'] ?? ''); ?></textarea>

                    <label for="conditions_accessibilite">Conditions d'accessibilité *</label>
                    <textarea id="conditions_accessibilite" name="conditions_accessibilite" required><?php echo htmlspecialchars($js_data['conditions_accessibilite'] ?? ''); ?></textarea>
                    <div class="error-message">Veuillez entrer les conditions d'accessibilité.</div>

                    <label for="categorie">Catégorie (non modifiable)</label>
                    <select id="categorie" name="categorie" disabled>
                        <?php
                        $categories_map = ['activite' => '1', 'visite' => '2', 'spectacle' => '3', 'parc_attractions' => '4', 'restauration' => '5'];
                        $selected_cat_type = $offre_data_from_db['main']['categorie_type_enum'] ?? ($js_data['categorie_type_enum'] ?? null);
                        $selected_cat_value = $selected_cat_type ? $categories_map[$selected_cat_type] : '';
                        ?>
                        <option value="1" <?php echo ($selected_cat_value == '1') ? 'selected' : ''; ?>>Activité</option>
                        <option value="2" <?php echo ($selected_cat_value == '2') ? 'selected' : ''; ?>>Visite</option>
                        <option value="3" <?php echo ($selected_cat_value == '3') ? 'selected' : ''; ?>>Spectacle</option>
                        <option value="4" <?php echo ($selected_cat_value == '4') ? 'selected' : ''; ?>>Parc d'attraction</option>
                        <option value="5" <?php echo ($selected_cat_value == '5') ? 'selected' : ''; ?>>Restaurant</option>
                    </select>
                </div>

                <div id="categorie-specific-fields" class="form-section"></div>

                <?php
                // Logique pour la section visite guidée, qui n'apparaît que pour la catégorie 'visite'
                $display_visite_guidee_section = (isset($js_data['categorie_type_enum']) && $js_data['categorie_type_enum'] === 'visite') ? 'block' : 'none';
                ?>
                <div class="form-section" id="visite-guidee-section" style="display: <?php echo $display_visite_guidee_section; ?>;">
                    <label>
                        <input type="checkbox" id="visite_guidee" name="visite_guidee" style="width:auto; margin-right: 5px;" <?php echo (isset($js_data['visite_guidee']) && $js_data['visite_guidee'] === 'on') ? 'checked' : ''; ?>> La visite est guidée
                    </label>
                    <div id="langues-guidees" class="form-group" style="<?php echo (isset($js_data['visite_guidee']) && $js_data['visite_guidee'] === 'on') ? 'display:block;' : 'display:none;'; ?>">
                        <label for="langues">Langues proposées *</label>
                        <select id="langues" name="langues[]" multiple>
                            <?php
                            $selected_langues = isset($js_data['langues']) && is_array($js_data['langues']) ? $js_data['langues'] : [];
                            foreach ($all_langues_db as $code => $nom) { // Utilisation des langues chargées depuis la DB
                                $selected_attr = in_array($code, $selected_langues) ? 'selected' : '';
                                echo "<option value=\"$code\" $selected_attr>" . htmlspecialchars($nom) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="error-message">Veuillez sélectionner au moins une langue si la visite est guidée.</div>
                    </div>
                </div>

                <div class="form-section">
                    <?php
                    $display_main_date_input = (isset($js_data['categorie_type_enum']) && in_array($js_data['categorie_type_enum'], ['activite', 'spectacle', 'parc_attractions'])) ? 'none' : 'block';
                    ?>
                    <div id="main-date-input-container" style="display: <?php echo $display_main_date_input; ?>;">
                        <label for="date">Date de l'offre/visite *</label>
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($js_data['date'] ?? ''); ?>">
                        <div class="error-message">Veuillez entrer la date de l'offre/visite.</div>
                    </div>

                    <label for="site">Site Web</label>
                    <input type="url" id="site" name="site" placeholder="https://www.example.com" value="<?php echo htmlspecialchars($js_data['site'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer une URL valide.</div>

                    <label for="ligne_adresse">Ligne d'adresse *</label>
                    <input type="text" id="ligne_adresse" name="ligne_adresse" required value="<?php echo htmlspecialchars($js_data['ligne_adresse'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer la ligne d'adresse.</div>

                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required value="<?php echo htmlspecialchars($js_data['ville'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer la ville.</div>

                    <label for="code_postal">Code postal *</label>
                    <input type="text" id="code_postal" name="code_postal" pattern="^\d{5}$" required value="<?php echo htmlspecialchars($js_data['code_postal'] ?? ''); ?>">
                    <div class="error-message">Veuillez entrer un code postal à 5 chiffres.</div>

                    <label for="photos">Ajoutez jusqu'à 6 photos (minimum 1 au total) *</label>
                    <?php if ($edit_mode && !empty($offre_data_from_db['photos'])): ?>
                        <p style="font-size: 0.9em; color: #555;">Photos actuelles (cliquez sur la croix pour supprimer) :</p>
                        <div id="existing-image-preview-container">
                            <?php foreach ($offre_data_from_db['photos'] as $photo): ?>
                                <div class="preview-item" id="photo-<?php echo htmlspecialchars($photo['id']); ?>">
                                    <img src="../<?php echo htmlspecialchars($photo['url']); ?>" alt="Aperçu">
                                    <span class="delete-image-btn" data-photo-id="<?php echo htmlspecialchars($photo['id']); ?>">&times;</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="deleted_photos" id="deleted_photos_input" value="">
                    <?php endif; ?>

                    <p style="font-size: 0.9em; color: #555; margin-top: 1rem;">Ajouter de nouvelles photos :</p>
                    <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                    <div class="error-message" id="photos-error-message">Veuillez ajouter au moins une photo (jusqu'à 6).</div>
                    <div id="image-preview-container"></div>

                    <button type="submit">Mettre à jour l'annonce</button>
                </div>
            </form>
        </div>
    </main>
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
                    <li><a href="../index.php">Accueil</a></li>
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
        document.addEventListener('DOMContentLoaded', function() {
            const categorieSelect = document.getElementById('categorie');
            const categorieSpecificFields = document.getElementById('categorie-specific-fields');
            const serverData = <?php echo json_encode($js_data); ?>;
            const offerForm = document.getElementById('offer-form');
            const today = new Date().toISOString().split('T')[0];

            const visiteGuideeSection = document.getElementById('visite-guidee-section');
            const languesGuideesDiv = document.getElementById('langues-guidees');
            const languesSelect = document.getElementById('langues');
            const mainDateInputContainer = document.getElementById('main-date-input-container');
            const mainDateInput = document.getElementById('date');

            const photosInput = document.getElementById('photos');
            const imagePreviewContainer = document.getElementById('image-preview-container');
            const existingImagePreviewContainer = document.getElementById('existing-image-preview-container'); // Nouveau
            const deletedPhotosInput = document.getElementById('deleted_photos_input'); // Nouveau
            let currentSelectedFiles = []; // Pour les nouvelles photos
            let existingPhotoElements = []; // Pour suivre les photos existantes dans le DOM

            // Récupérer les photos existantes si présentes au chargement de la page
            <?php if (!empty($offre_data_from_db['photos'])): ?>
                existingPhotoElements = <?php echo json_encode($offre_data_from_db['photos']); ?>;
            <?php endif; ?>

            function setMinDateForInput(inputElement) {
                if (inputElement) {
                    inputElement.setAttribute('min', today);
                }
            }

            // Genere et affiche dynamiquement les champs de formulaire spécifiques à la catégorie sélectionnée (activité, visite, etc.)
            function fetchCategorieFields(categorieTypeEnum, data) {
                let htmlContent = '';
                const isDateHandledByCategoryOrNotApplicable = (categorieTypeEnum === 'activite' || categorieTypeEnum === 'spectacle' || categorieTypeEnum === 'parc_attractions');

                if (mainDateInputContainer) mainDateInputContainer.style.display = isDateHandledByCategoryOrNotApplicable ? 'none' : 'block';
                if (mainDateInput) {
                    mainDateInput.required = !isDateHandledByCategoryOrNotApplicable;
                    if (categorieTypeEnum === 'visite') mainDateInput.required = true;
                    else if (categorieTypeEnum === 'restauration') mainDateInput.required = false; // Restauration n'a pas de date principale
                    setMinDateForInput(mainDateInput); // Set min date for main date input
                }

                switch (categorieTypeEnum) {
                    case 'activite':
                        htmlContent = `
                        <label for="duree_activite">Durée de l'activité (en minutes) *</label>
                        <input type="number" id="duree_activite" name="duree" required min="1" value="${data.duree || ''}">
                        <div class="error-message">Veuillez entrer la durée de l'activité.</div>
                        <label for="prix_minimum_activite">Prix minimum (optionnel, si différent du prix principal)</label>
                        <input type="number" id="prix_minimum_activite" name="prix_minimum_activite" step="0.01" min="0" placeholder="Ex: 10.50" value="${data.prix_minimum_activite || ''}">
                        <div class="error-message">Veuillez entrer un prix valide.</div>
                        <label for="age_requis_activite">Âge requis (optionnel)</label>
                        <input type="number" id="age_requis_activite" name="age_requis_activite" min="0" placeholder="Ex: 6" value="${data.age_requis_activite || ''}">
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
                        break;
                    case 'visite':
                        htmlContent = `
                        <label for="duree_visite">Durée de la visite (en heures, ex: 1.5 pour 1h30) *</label>
                        <input type="number" id="duree_visite" name="duree" required step="0.1" min="0.1" value="${data.duree_visite || ''}">
                        <div class="error-message">Veuillez entrer la durée de la visite.</div>

                        <label for="prix_minimum_visite">Prix minimum (optionnel, si différent du prix principal)</label>
                        <input type="number" id="prix_minimum_visite" name="prix_minimum_visite" step="0.01" min="0" placeholder="Ex: 5.00" value="${data.prix_minimum_visite || ''}">
                        <div class="error-message">Veuillez entrer un prix valide.</div>

                        <label for="heure_debut_visite">Heure de début *</label>
                        <input type="time" id="heure_debut_visite" name="heure_debut_visite" required value="${data.heure_debut_visite || ''}">
                        <div class="error-message">Veuillez entrer une heure de début.</div>
                    `;
                        break;
                    case 'spectacle':
                        const dateSpectVal = data.date_spectacle || ''; // Date for spectacle
                        htmlContent = `
                        <label for="duree_spectacle">Durée du spectacle (en minutes) *</label>
                        <input type="number" id="duree_spectacle" name="duree_spectacle" required min="1" value="${data.duree_spectacle || ''}">
                        <div class="error-message">Veuillez entrer la durée du spectacle.</div>

                        <label for="prix_minimum_spectacle">Prix minimum (optionnel, si différent du prix principal)</label>
                        <input type="number" id="prix_minimum_spectacle" name="prix_minimum_spectacle" step="0.01" min="0" placeholder="Ex: 15.00" value="${data.prix_minimum_spectacle || ''}">
                        <div class="error-message">Veuillez entrer un prix valide.</div>

                        <label for="date_spectacle">Date du spectacle *</label>
                        <input type="date" id="date_spectacle" name="date_spectacle" required value="${dateSpectVal}">
                        <div class="error-message">Veuillez entrer la date du spectacle.</div>

                        <label for="heure_debut_spectacle">Heure de début *</label>
                        <input type="time" id="heure_debut_spectacle" name="heure_debut_spectacle" required value="${data.heure_debut_spectacle || ''}">
                        <div class="error-message">Veuillez entrer l'heure de début du spectacle.</div>

                        <label for="capacite_spectacle">Capacité (nombre de places) *</label>
                        <input type="number" id="capacite_spectacle" name="capacite_spectacle" required min="1" value="${data.capacite_spectacle || ''}">
                        <div class="error-message">Veuillez entrer la capacité du spectacle.</div>
                    `;
                        break;
                    case 'parc_attractions':
                        htmlContent = `
                        <label for="prix_minimum_parc">Prix minimum d'entrée (optionnel, si différent du prix principal)</label>
                        <input type="number" id="prix_minimum_parc" name="prix_minimum_parc" step="0.01" min="0" placeholder="Ex: 20.00" value="${data.prix_minimum_parc || ''}">
                        <div class="error-message">Veuillez entrer un prix valide.</div>

                        <label for="age_requis_parc">Âge requis (optionnel)</label>
                        <input type="number" id="age_requis_parc" name="age_requis_parc" min="0" placeholder="Ex: 3" value="${data.age_requis_parc || ''}">
                        <div class="error-message">Veuillez entrer un âge valide.</div>

                        <label for="nombre_total_attractions_parc">Nombre total d'attractions estimé</label>
                        <input type="number" id="nombre_total_attractions_parc" name="nombre_total_attractions_parc" min="0" placeholder="Ex: 25" value="${data.nombre_total_attractions_parc || '0'}">
                        <div class="error-message">Veuillez entrer un nombre valide.</div>

                        <label for="maps_url_parc">Lien vers le plan du parc (URL) *</label>
                        <input type="url" id="maps_url_parc" name="maps_url_parc" required placeholder="https://example.com/plan-du-parc" value="${data.maps_url_parc || ''}">
                        <div class="error-message">Veuillez entrer une URL valide.</div>

                        <div id="attractions-container"> <h4 class="dynamic-section-subtitle" style="margin-top:var(--espacement-double);">Attractions spécifiques (au moins une requise) :</h4>
                        </div>
                        <a href="#" role="button" id="add-attraction-parc" class="text-add-link">+ Ajouter une attraction spécifique</a>
                    `;
                        break;
                    case 'restauration':
                        htmlContent = `
                        <label for="lien_menu_restaurant">Lien vers le menu (URL) *</label>
                        <input type="url" id="lien_menu_restaurant" name="lien_menu_restaurant" required placeholder="https://example.com/menu" value="${data.lien_menu_restaurant || ''}">
                        <div class="error-message">Veuillez entrer une URL valide pour le menu.</div>

                        <label for="prix_moyen_restaurant">Prix moyen par personne (€) *</label>
                        <input type="number" id="prix_moyen_restaurant" name="prix_moyen_restaurant" required step="0.01" min="0" placeholder="Ex: 25.50" value="${data.prix_moyen_restaurant || ''}">
                        <div class="error-message">Veuillez entrer un prix moyen valide.</div>

                        <div id="plats-container-restaurant" class="form-group">
                            <h4 class="dynamic-section-subtitle">Plats principaux proposés (Max 5, optionnel) :</h4>
                        </div>
                        <a href="#" role="button" id="add-plat-restaurant" class="text-add-link">+ Ajouter un plat</a>
                    `;
                        break;
                }
                categorieSpecificFields.innerHTML = htmlContent;

                // Set min date for category-specific date inputs
                setMinDateForInput(document.getElementById('date_spectacle'));

                // Repopulate dynamic fields based on fetched data
                if (categorieTypeEnum === 'activite') {
                    if (data.activites && data.activites[0] && data.activites[0].horaires && Array.isArray(data.activites[0].horaires)) {
                        data.activites[0].horaires.forEach(h => addHoraireActivite(null, h.date, h.heure_debut));
                    } else {
                        addHoraireActivite(); // Ensure at least one is present for required validation
                    }
                    if (data.activites && data.activites[0] && data.activites[0].services && Array.isArray(data.activites[0].services)) {
                        data.activites[0].services.forEach(s => addServiceActivite(null, s.nom_service, s.inclusion === 'on'));
                    }
                } else if (categorieTypeEnum === 'parc_attractions') {
                    if (data.attractions && Array.isArray(data.attractions)) {
                        data.attractions.forEach(attr => {
                            addAttractionParc(null, attr.nom_attraction, attr.horaires || []);
                        });
                    } else {
                        addAttractionParc(); // Ensure at least one is present for required validation
                    }
                } else if (categorieTypeEnum === 'restauration') {
                    if (data.plats && Array.isArray(data.plats)) {
                        data.plats.forEach(platName => addPlatRestaurant(null, platName));
                    }
                }

                // Visite guidée section visibility
                if (categorieTypeEnum === 'visite') {
                    visiteGuideeSection.style.display = 'block';
                    const visiteGuideeCheckbox = document.getElementById('visite_guidee');
                    if (visiteGuideeCheckbox) {
                        const isChecked = data.visite_guidee === 'on';
                        visiteGuideeCheckbox.checked = isChecked;
                        languesGuideesDiv.style.display = isChecked ? 'block' : 'none';
                        languesSelect.required = isChecked;
                        if (isChecked && Array.isArray(data.langues)) {
                            Array.from(languesSelect.options).forEach(option => {
                                option.selected = data.langues.includes(option.value);
                            });
                        }
                    }
                } else {
                    visiteGuideeSection.style.display = 'none';
                    if (languesSelect) languesSelect.required = false;
                }

                attachDynamicEventListeners();
                // Validate fields immediately if there were server-side errors on POST
                if (offerForm.dataset.submitted === 'true') {
                    validateAllFields(categorieSpecificFields);
                }
            }

            function attachDynamicEventListeners() {
                const visiteGuideeCheckbox = document.getElementById('visite_guidee');
                if (visiteGuideeCheckbox) {
                    visiteGuideeCheckbox.addEventListener('change', (e) => {
                        languesGuideesDiv.style.display = e.target.checked ? 'block' : 'none';
                        languesSelect.required = e.target.checked;
                        if (!e.target.checked) {
                            languesSelect.classList.remove('invalid');
                            const errorMsgContainer = languesSelect.closest('.form-group');
                            if (errorMsgContainer) {
                                const errorMsg = errorMsgContainer.querySelector('.error-message');
                                if (errorMsg) errorMsg.style.display = 'none';
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
                // Re-apply cross logic to dynamically added groups if any
                categorieSpecificFields.querySelectorAll('.item-group').forEach(group => addRemoveCrossIfNeeded(group));
            }

            // --- Fonctions de création de champs (copiées de publier-une-offre.php) ---
            function createRemoveCross() {
                const cross = document.createElement('span');
                cross.className = 'remove-icon-cross remove-element';
                cross.innerHTML = '&times;';
                cross.setAttribute('role', 'button');
                cross.setAttribute('aria-label', 'Supprimer cet élément');
                return cross
            }

            function addRemoveCrossIfNeeded(groupElement) {
                if (!groupElement.querySelector('.remove-icon-cross')) {
                    const cross = createRemoveCross();
                    groupElement.insertBefore(cross, groupElement.firstChild)
                }
                const parentContainer = groupElement.parentNode;
                if (parentContainer.id === 'horaires-container-activite') updateRemoveCrossVisibility(parentContainer, '.horaire-group');
                else if (parentContainer.id === 'services-container-activite') updateRemoveCrossVisibility(parentContainer, '.service-group');
                else if (parentContainer.id === 'attractions-container') updateRemoveCrossVisibility(parentContainer, '.attraction-group');
                else if (parentContainer.classList.contains('horaires-container-attraction')) updateRemoveCrossVisibility(parentContainer, '.horaire-group');
                else if (parentContainer.id === 'plats-container-restaurant') updateRemoveCrossVisibility(parentContainer, '.plat-group')
            }

            function updateRemoveCrossVisibility(container, itemSelector) {
                const items = typeof container === 'string' ? document.querySelectorAll(container + " " + itemSelector) : container.querySelectorAll(itemSelector);
                const minItems = (itemSelector === '.service-group' || itemSelector === '.plat-group') ? 0 : 1;
                items.forEach((item, index) => {
                    const cross = item.querySelector('.remove-icon-cross');
                    if (cross) {
                        if (items.length > minItems) {
                            cross.style.display = 'flex'
                        } else {
                            cross.style.display = 'none'
                        }
                    }
                })
            }

            function addHoraireActivite(event, dateVal = '', debutVal = '') {
                if (event) event.preventDefault();
                const container = document.getElementById('horaires-container-activite');
                const count = container.querySelectorAll('.horaire-group').length;
                const newGroup = document.createElement('div');
                newGroup.className = 'item-group horaire-group';
                const idPrefix = `activites_0_horaires_${count}`;
                newGroup.innerHTML = `<div class="horaire-group-inputs"><div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="activites[0][horaires][${count}][date]" required value="${dateVal}"><div class="error-message">Date requise.</div></div><div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="activites[0][horaires][${count}][heure_debut]" required value="${debutVal}"><div class="error-message">Heure de début requise.</div></div></div>`;
                container.appendChild(newGroup);
                setMinDateForInput(newGroup.querySelector('input[type="date"]'));
                addRemoveCrossIfNeeded(newGroup);
                if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
            }

            function addServiceActivite(event, nomVal = '', inclusVal = false) {
                if (event) event.preventDefault();
                const container = document.getElementById('services-container-activite');
                const count = container.querySelectorAll('.service-group').length;
                const newGroup = document.createElement('div');
                newGroup.className = 'item-group service-group';
                const checkedAttr = inclusVal ? 'checked' : '';
                newGroup.innerHTML = `<label for="service_nom_${count}">Nom du service/prestation *</label><input type="text" id="service_nom_${count}" name="activites[0][services][${count}][nom_service]" placeholder="Ex: Wifi gratuit" value="${nomVal}" required><div class="error-message">Le nom du service est requis.</div><label style="display:inline-flex;align-items:center;margin-top:var(--espacement-petit);margin-bottom:var(--espacement-standard);"><input type="checkbox" name="activites[0][services][${count}][inclusion]" style="width:auto;margin-right:var(--espacement-petit);"${checkedAttr}> Inclus dans le prix</label>`;
                container.appendChild(newGroup);
                addRemoveCrossIfNeeded(newGroup);
                if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
            }

            function addAttractionParc(event, nomVal = '', horairesData = []) {
                if (event) event.preventDefault();
                const container = document.getElementById('attractions-container');
                const count = container.querySelectorAll('.attraction-group').length;
                const newGroup = document.createElement('div');
                newGroup.className = 'item-group attraction-group';
                const idPrefix = `attractions_${count}`;
                newGroup.innerHTML = `<h3>Attraction ${count+1}</h3><label for="${idPrefix}_nom_attraction">Nom de l'attraction *</label><input type="text" id="${idPrefix}_nom_attraction" name="attractions[${count}][nom_attraction]" required value="${nomVal}"><div class="error-message">Veuillez entrer le nom de l'attraction.</div><div class="horaires-container-attraction form-group"><h4 class="dynamic-section-subtitle">Horaires de cette attraction (au moins un requis) :</h4></div><a href="#" role="button" class="text-add-link add-horaire-parc-attraction">+ Ajouter horaire à cette attraction</a>`;
                container.appendChild(newGroup);
                addRemoveCrossIfNeeded(newGroup);
                const attractionHorairesContainer = newGroup.querySelector('.horaires-container-attraction');
                if (horairesData && horairesData.length > 0) {
                    horairesData.forEach(h => addHoraireToAttraction(null, attractionHorairesContainer, h.date, h.heure_debut, h.heure_fin))
                } else {
                    addHoraireToAttraction(null, attractionHorairesContainer);
                }
                if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
            }

            function addHoraireToAttraction(event, containerElement = null, dateVal = '', debutVal = '', finVal = '') {
                if (event) event.preventDefault();
                const attractionGroup = containerElement ? containerElement.closest('.attraction-group') : event.target.closest('.attraction-group');
                if (!attractionGroup) return;
                const horairesContainer = containerElement || attractionGroup.querySelector('.horaires-container-attraction');
                const attractionIndexInput = attractionGroup.querySelector('[name*="[nom_attraction]"]');
                const nameAttr = attractionIndexInput ? attractionIndexInput.name : `attractions[${document.querySelectorAll('.attraction-group').length-1}]`;
                const attractionIndexMatch = nameAttr.match(/attractions\[(\d+)\]/);
                const attractionIndex = attractionIndexMatch ? attractionIndexMatch[1] : document.querySelectorAll('.attraction-group').length - 1;
                const horaireCount = horairesContainer.querySelectorAll('.horaire-group').length;
                const newGroup = document.createElement('div');
                newGroup.className = 'item-group horaire-group';
                const idPrefix = `attractions_${attractionIndex}_horaires_${horaireCount}`;
                newGroup.innerHTML = `<div class="horaire-group-inputs"><div><label for="${idPrefix}_date">Date *</label><input type="date" id="${idPrefix}_date" name="attractions[${attractionIndex}][horaires][${horaireCount}][date]" required value="${dateVal}"><div class="error-message">Date requise.</div></div><div><label for="${idPrefix}_heure_debut">Début *</label><input type="time" id="${idPrefix}_heure_debut" name="attractions[${attractionIndex}][horaires][${horaireCount}][heure_debut]" required value="${debutVal}"><div class="error-message">Heure de début requise.</div></div><div><label for="${idPrefix}_heure_fin">Fin *</label><input type="time" id="${idPrefix}_heure_fin" name="attractions[${attractionIndex}][horaires][${horaire_count}][heure_fin]" required value="${finVal}"><div class="error-message">Heure de fin requise.</div></div></div>`;
                horairesContainer.appendChild(newGroup);
                setMinDateForInput(newGroup.querySelector('input[type="date"]'));
                addRemoveCrossIfNeeded(newGroup);
                if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
            }

            function addPlatRestaurant(event, nomVal = '') {
                if (event) event.preventDefault();
                const container = document.getElementById('plats-container-restaurant');
                const platGroups = container.querySelectorAll('.plat-group');
                if (platGroups.length >= 5) {
                    const addPlatButton = document.getElementById('add-plat-restaurant');
                    if (addPlatButton) addPlatButton.style.display = 'none';
                    return
                }
                const newGroup = document.createElement('div');
                newGroup.className = 'item-group plat-group';
                newGroup.innerHTML = `<label for="plat_nom_${platGroups.length}">Nom du plat ${platGroups.length+1}</label><input type="text" id="plat_nom_${platGroups.length}" name="plats[]" placeholder="Ex: Pizza Margherita" value="${nomVal}"><div class="error-message">Le nom du plat est requis si vous ajoutez une entrée pour celui-ci.</div>`;
                container.appendChild(newGroup);
                addRemoveCrossIfNeeded(newGroup);
                if (container.querySelectorAll('.plat-group').length >= 5) {
                    const addPlatButton = document.getElementById('add-plat-restaurant');
                    if (addPlatButton) addPlatButton.style.display = 'none'
                }
                if (offerForm.dataset.submitted === 'true') validateAllFields(newGroup);
            }

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
                    // Ré-exécuter la validation des photos si nécessaire après suppression d'une image existante
                    validatePhotosField();
                }
            }

            // Tente de trouver et de retourner l'élément HTML destiné à afficher un message d'erreur pour un champ de formulaire donné
            function getErrorMessageElementForField(field) {
                let directSibling = field.nextElementSibling;
                if (directSibling && directSibling.classList.contains('error-message')) return directSibling;
                let parentDiv = field.closest('div');
                if (parentDiv) {
                    let children = Array.from(parentDiv.children);
                    let fieldIndex = children.indexOf(field);
                    if (fieldIndex !== -1 && children[fieldIndex + 1] && children[fieldIndex + 1].classList.contains('error-message')) {
                        return children[fieldIndex + 1];
                    }
                    let parentSibling = parentDiv.nextElementSibling;
                    if (parentSibling && parentSibling.classList.contains('error-message')) return parentSibling;
                }
                const label = document.querySelector(`label[for="${field.id}"]`);
                if (label) {
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
                    // Photos field has special validation
                    if (field.id === 'photos') return validatePhotosField();
                    // Multi-select required check
                    if (field.multiple && field.required && field.selectedOptions.length === 0) isValidField = false;

                    // Check date field min attribute
                    if (field.type === 'date' && field.min && field.value && field.value < field.min) {
                        isValidField = false;
                        if (errorMessageElement) errorMessageElement.textContent = "La date ne peut pas être antérieure à aujourd'hui.";
                    }

                }

                if (isValidField) {
                    field.classList.remove('invalid');
                    if (errorMessageElement) errorMessageElement.style.display = 'none';
                } else {
                    if (isVisible || field.required) {
                        field.classList.add('invalid');
                        if (errorMessageElement) {
                            // Custom messages for specific validation failures
                            if (field.type === 'date' && field.min && field.value && field.value < field.min) {
                                // message already set above
                            } else if (field.pattern && !field.value.match(new RegExp(field.pattern))) {
                                // Default message for pattern mismatch if specific not set
                                if (field.id === 'prix') errorMessageElement.textContent = "Veuillez entrer un prix valide (ex: 10, 10.50).";
                                else if (field.id === 'coordonnees_telephoniques') errorMessageElement.textContent = "Veuillez entrer un numéro de téléphone à 10 chiffres commençant par 0.";
                                else if (field.id === 'code_postal') errorMessageElement.textContent = "Veuillez entrer un code postal à 5 chiffres.";
                                else errorMessageElement.textContent = field.validationMessage || "Format invalide.";
                            } else {
                                errorMessageElement.textContent = field.validationMessage; // Use browser's default message if custom not provided
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
                containerOrForm.querySelectorAll('input:not([type="button"]):not([type="submit"]):not([disabled]), textarea, select:not([disabled])').forEach(field => {
                    const isVisible = field.offsetWidth > 0 || field.offsetHeight > 0 || field.getClientRects().length > 0 || field.type === 'hidden';
                    if (isVisible || field.required) {
                        if (field.id === 'photos') { // Special handling for photos field
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
                const currentTotalPhotos = currentSelectedFiles.length + existingPhotoElements.filter(p => p.visible).length; // Nouvelle logique de comptage

                files.forEach(file => {
                    // Vérifier si le fichier est une image et si le nombre total de photos (existantes + nouvelles) ne dépasse pas 6
                    if (file.type.startsWith('image/') && (currentTotalPhotos + newFilesToAdd.length) < 6) {
                        // Vérifier si le fichier n'est pas déjà dans la liste des nouvelles photos sélectionnées
                        if (!currentSelectedFiles.some(existingFile => existingFile.name === file.name && existingFile.size === file.size)) {
                            newFilesToAdd.push(file);
                        }
                    }
                });
                currentSelectedFiles.push(...newFilesToAdd);
                renderPhotoPreviews();
                // Pas besoin d'appeler updateFileInput() ici car les photos nouvelles sont gérées via `currentSelectedFiles`
                // et le champ `photosInput` est juste pour la sélection, pas pour l'état des fichiers
                validatePhotosField();
            }

            // Supprime image
            function removeImage(fileNameToRemove) {
                currentSelectedFiles = currentSelectedFiles.filter(file => file.name !== fileNameToRemove);
                renderPhotoPreviews();
                validatePhotosField();
            }

            // Affiche les prévisualisations des images actuellement sélectionnées.
            function renderPhotoPreviews() {
                imagePreviewContainer.innerHTML = ''; // Clear only the new previews container
                currentSelectedFiles.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        img.alt = file.name;
                        // Pas de modal pour modifier-offre, on supprime l'événement click pour agrandir
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

            // Valide le champ de photos (nombre minimum/maximum de photos).
            function validatePhotosField() {
                const photosErrorMessage = document.getElementById('photos-error-message');
                // Compter les photos visibles (existantes non supprimées + nouvelles)
                const visibleExistingPhotosCount = existingPhotoElements.filter(p => p.visible).length;
                const totalCurrentPhotos = visibleExistingPhotosCount + currentSelectedFiles.length;

                let isValid = true;
                if (photosInput.required && totalCurrentPhotos === 0) {
                    photosErrorMessage.textContent = "Veuillez ajouter au moins une photo.";
                    photosErrorMessage.style.display = 'block';
                    photosInput.classList.add('invalid');
                    isValid = false;
                } else if (totalCurrentPhotos > 6) {
                    photosErrorMessage.textContent = `Vous avez ${totalCurrentPhotos} photos. Vous ne pouvez avoir que 6 photos maximum au total.`;
                    photosErrorMessage.style.display = 'block';
                    photosInput.classList.add('invalid');
                    isValid = false;
                } else {
                    photosErrorMessage.style.display = 'none';
                    photosInput.classList.remove('invalid');
                }
                return isValid;
            }


            if (photosInput) photosInput.addEventListener('change', handlePhotoSelection);

            // Initialisation des données pour les photos existantes au chargement de la page
            // Marquer toutes les photos existantes comme visibles par défaut
            existingPhotoElements.forEach(photo => photo.visible = true);
            // Attacher les écouteurs pour les boutons de suppression des photos existantes
            document.querySelectorAll('#existing-image-preview-container .delete-image-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const photoId = this.dataset.photoId;
                    this.closest('.preview-item').style.display = 'none';
                    // Mettre à jour l'état de visibilité de la photo dans existingPhotoElements
                    const photoIndex = existingPhotoElements.findIndex(p => p.id == photoId);
                    if (photoIndex !== -1) {
                        existingPhotoElements[photoIndex].visible = false;
                    }

                    let currentDeleted = deletedPhotosInput.value ? deletedPhotosInput.value.split(',') : [];
                    if (!currentDeleted.includes(photoId)) {
                        currentDeleted.push(photoId);
                        deletedPhotosInput.value = currentDeleted.join(',');
                    }
                    validatePhotosField(); // Re-validate after deleting an existing photo
                });
            });

            // Catégorie select est disabled dans modifier-offre.php, mais nous utilisons sa valeur initiale
            // pour charger les champs spécifiques.
            if (serverData.categorie_type_enum) {
                fetchCategorieFields(serverData.categorie_type_enum, serverData);
            } else {
                // Si pas de catégorie dans serverData (ex: page chargée pour la première fois sans offre spécifique),
                // afficher le champ de date principal par défaut.
                if (mainDateInputContainer) mainDateInputContainer.style.display = 'block';
                if (mainDateInput) mainDateInput.required = true;
            }

            // Validation au submit
            offerForm.addEventListener('submit', (event) => {
                offerForm.dataset.submitted = 'true';
                if (!validateAllFields(offerForm)) {
                    event.preventDefault();
                    const firstInvalidField = offerForm.querySelector('.invalid');
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });

            // Valider tous les champs au chargement si le formulaire a été soumis et qu'il y a des erreurs PHP
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($erreurs)): ?>
                offerForm.dataset.submitted = 'true';
                const phpErrors = <?php echo json_encode(array_keys($erreurs)); ?>;
                let firstInvalidFieldForFocus = null;

                phpErrors.forEach(fieldName => {
                    let field = document.getElementById(fieldName) ||
                        document.querySelector(`[name="${fieldName}"]`) ||
                        document.querySelector(`[name^="${fieldName}["]`); // Pour gérer les tableaux d'inputs

                    if (field && !field.classList.contains('invalid')) { // Éviter de traiter deux fois
                        field.classList.add('invalid');
                        const errorMessageElement = getErrorMessageElementForField(field);
                        if (errorMessageElement) {
                            errorMessageElement.style.display = 'block';
                            // Afficher le message spécifique si l'erreur PHP correspond à ce champ
                            <?php foreach ($erreurs as $err_key => $err_msg): ?>
                                if (fieldName === '<?php echo $err_key; ?>') {
                                    errorMessageElement.textContent = '<?php echo addslashes($err_msg); ?>';
                                }
                            <?php endforeach; ?>
                        }
                        if (!firstInvalidFieldForFocus) firstInvalidFieldForFocus = field;
                    } else if (fieldName.startsWith('photos_') || fieldName === 'photos' || fieldName === 'photos_missing' || fieldName === 'photos_count' || fieldName === 'photos_upload_dir' || fieldName === 'photos_upload_permission' || fieldName === 'photos_final_check') {
                        // Gestion spécifique des erreurs de photos
                        const photosErrorMsgEl = document.getElementById('photos-error-message');
                        if (photosErrorMsgEl) {
                            // Utiliser le message d'erreur PHP directement si disponible
                            photosErrorMsgEl.textContent = "<?php
                                                            if (isset($erreurs['photos_final_check'])) echo addslashes($erreurs['photos_final_check']);
                                                            elseif (isset($erreurs['photos_missing'])) echo addslashes($erreurs['photos_missing']);
                                                            elseif (isset($erreurs['photos_count'])) echo addslashes($erreurs['photos_count']);
                                                            elseif (isset($erreurs['photos_upload_dir'])) echo addslashes($erreurs['photos_upload_dir']);
                                                            elseif (isset($erreurs['photos_upload_permission'])) echo addslashes($erreurs['photos_upload_permission']);
                                                            elseif (preg_grep('/^photos_upload_move_/', array_keys($erreurs))) echo addslashes("Erreur lors du déplacement d'un fichier photo.");
                                                            elseif (preg_grep('/^photos_size_/', array_keys($erreurs))) echo addslashes("Un fichier photo est trop volumineux.");
                                                            elseif (preg_grep('/^photos_type_/', array_keys($erreurs))) echo addslashes("Type de fichier photo non autorisé.");
                                                            elseif (preg_grep('/^photos_upload_error_/', array_keys($erreurs))) echo addslashes("Une erreur de téléchargement est survenue pour une photo.");
                                                            else echo 'Erreur avec le téléchargement des photos.';
                                                            ?>";
                            photosErrorMsgEl.style.display = 'block';
                        }
                        if (photosInput) {
                            photosInput.classList.add('invalid');
                            if (!firstInvalidFieldForFocus) firstInvalidFieldForFocus = photosInput;
                        }
                    } else if (fieldName.includes('horaire_activite_date_')) {
                        // Logic for dynamic fields like horaires_activites dates
                        const match = fieldName.match(/horaire_activite_date_(\d+)/);
                        if (match) {
                            const index = match[1];
                            const dateField = document.querySelector(`[name="activites[0][horaires][${index}][date]"]`);
                            if (dateField) {
                                dateField.classList.add('invalid');
                                const errorEl = getErrorMessageElementForField(dateField);
                                if (errorEl) errorEl.style.display = 'block';
                                if (!firstInvalidFieldForFocus) firstInvalidFieldForFocus = dateField;
                            }
                        }
                    }
                    // Ajouter d'autres cas pour les champs dynamiques comme attractions, services, etc. si nécessaire
                    // pour s'assurer qu'ils sont marqués 'invalid' si les erreurs PHP les concernent.
                });

                if (firstInvalidFieldForFocus) {
                    firstInvalidFieldForFocus.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    const generalErrorDisplay = document.querySelector('.error.form-section');
                    if (generalErrorDisplay) {
                        generalErrorDisplay.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            <?php endif; ?>

            // Appliquer la validation au chargement si le formulaire a été soumis
            if (offerForm.dataset.submitted === 'true') {
                validateAllFields(offerForm);
            }
        });
    </script>
</body>

</html>