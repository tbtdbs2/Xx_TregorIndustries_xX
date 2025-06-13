<?php

session_start(); // Démarre la session PHP (doit être la première chose)

require_once '../composants/generate_uuid.php';
require_once __DIR__ . '/../../includes/db.php';

$login_error = ''; // Variable pour stocker les messages d'erreur de connexion
// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $email = $_POST['email'] ?? '';
    $raison_sociale = $_POST['cp-name'] ?? ''; // correspond à "Dénomination / Raison sociale"
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $siren = $_POST['siren'] ?? '';
    $secteur = $_POST['secteur'] ?? '';
    $phone = $_POST['tel'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $iban = $_POST['iban'] ?? '';
    $bic = $_POST['bic'] ?? '';


    // Validation des données
    if (empty($email) || empty($raison_sociale) || empty($adresse) || empty($ville) || empty($code_postal) || empty($siren) || empty($secteur) || empty($phone) || empty($password) || empty($password_confirm)) {
        $login_error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = 'Adresse e-mail invalide.';
    } elseif ($password !== $password_confirm) {
        $login_error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérification si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT * FROM comptes_pro WHERE email = :email OR siren = :siren");
        $stmt->execute(['email' => $email, 'siren' => $siren]);
        if ($stmt->rowCount() > 0) {
            $login_error = 'Un compte avec cet e-mail ou ce SIREN existe déjà.';
        } else {
            // Insertion de l'utilisateur dans la base de données
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // 1. Insertion de l'adresse
            $stmtAdresse = $pdo->prepare("
                INSERT INTO adresses (id, street, postal_code, city)
                VALUES (:id_adresse, :adresse, :code_postal, :ville)
            ");
            $adresse_id = generate_uuid(); // Génération d'un UUID pour l'adresse
            $stmtAdresse->execute([
                'id_adresse' => $adresse_id,
                'adresse' => $adresse,
                'code_postal' => $code_postal,
                'ville' => $ville
            ]);

            // 3. Insertion du compte membre
            $stmtMembre = $pdo->prepare("
                INSERT INTO comptes_pro (id, adresse_id, email, password, phone, company_name, is_private, siren, iban, bic)
                VALUES (:id_compte_pro, :adresse_id, :email, :password, :phone, :company_name, :is_private, :siren, :iban, :bic)
            ");
            $id_compte_pro = generate_uuid(); // Génération d'un UUID pour le compte membre
            $is_private = ($secteur === "privé") ? 1 : 0;
            if ($stmtMembre->execute(['id_compte_pro' => $id_compte_pro, 'adresse_id' => $adresse_id, 'email' => $email, 'password' => $password, 'phone' => $phone, 'company_name' => $raison_sociale, 'is_private' => $is_private, 'siren' => $siren, 'iban' => $iban, 'bic' => $bic])) {
                // Redirection vers la page de connexion ou une autre page après l'inscription réussie
                header('Location: connexion-compte.php');
                exit();
            } else {
                $login_error = 'Erreur lors de la création du compte. Veuillez réessayer.';
            }
        }
    }
} else {
    // Si le formulaire n'a pas été soumis, on initialise les variables
    $email = '';
    $raison_sociale = '';
    $adresse = '';
    $ville = '';
    $code_postal = '';
    $siren = '';
    $secteur = '';
    $phone = '';
    $password = '';
    $password_confirm = '';
    $iban = '';
    $bic = '';
    $login_error = '';// Réinitialise l'erreur de connexion
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT Pro - Création</title><link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Vos styles CSS restent inchangés */
        .content-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
        }

        .register-container {
            background-color: #fff;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 600px; /* Ajuster la largeur pour mieux correspondre à l'image */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .content-area h1 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 36px;
        }

        .content-area p{
            color: #000000;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }

        .register-form {
            display: grid; /* Utilisation de grid pour un placement plus précis */
            grid-template-columns: repeat(2, 1fr); /* Création de deux colonnes de largeur égale */
            gap: 15px 20px; /* Espacement vertical et horizontal entre les éléments */
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        /* Occuper deux colonnes pour les éléments sur une seule ligne */
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: #333;
            margin-bottom: 5px; /* Réduction de la marge pour un aspect plus compact */
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input[type="name"],
        .form-group input[type="text"],
        .form-group input[type="last-name"],
        .form-group input[type="tel"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        #secteur{
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .register-button-container {
            grid-column: 1 / -1; /* Le bouton occupe toute la largeur */
            display: flex;
            flex-direction: row;
            justify-content: space-between; /* Alignement à droite du bouton */
            margin-top: 20px;
        }

        .register-button {
            background-color:var(--couleur-principale);
            color: #fff;
            border: none;
            padding: 15px 30px; /* Réduction du padding pour un bouton moins grand */
            border-radius: 16px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .register-button:hover {
            background-color: var(--couleur-principale-hover);
        }

        .already-registered-container {
            grid-column: 1 / -1; /* "J'ai déjà un compte" occupe toute la largeur */
            text-align: left;
            margin-top: 20px;
        }

        .already-registered {
            text-decoration: underline;
            color: #000;
        }

        .already-registered:hover {
            text-decoration: underline;
            color: #000;
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
                <li><a href="publier-une-offre.php">Publier une offre</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <a href="profil.php" class="btn btn-secondary">Mon profil</a>
            <a href="connexion-compte.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

    <main>
        <div class="container content-area">
            <h1>Inscription</h1>
            <p>Le début d'une grande collaboration !</p>
            <?php
            // Affichage du message d'erreur pour l'utilisateur
            if (!empty($login_error)) {
                echo '<p style="color: red; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($login_error) . '</p>';
            }
            ?>
            <div class="register-container">
                <form class="register-form" action="creation-compte.php" method="POST">
                    <div class="form-group full-width">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" placeholder="adressemail@exemple.com">
                    </div>
                    <div class="form-group full-width">
                        <label for="cp-name">Dénomination / Raison sociale *</label>
                        <input type="text" id="cp-name" name="cp-name" placeholder="Toto Enterprise">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresse">Adresse postale *</label>
                        <input type="text" id="adresse" name="adresse" placeholder="1 impasse Victor Hugo">
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville *</label>
                        <input type="text" id="ville" name="ville" placeholder="Lannion">
                    </div>
                    <div class="form-group">
                        <label for="code_postal">Code Postal *</label>
                        <input type="text" id="code_postal" name="code_postal" placeholder="22300">
                    </div>
                    <div class="form-group full-width">
                        <label for="siren">Numéro de SIREN</label>
                        <input type="text" id="siren" name="siren" placeholder="123 456 789">
                    </div>
                    <div class="form-group">
                        <label for="secteur">Secteur d'activité *</label>
                        <select id="secteur" name="secteur">
                            <option value="particulier">Privé</option>
                            <option value="professionnel">Public</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tel">N° Téléphone *</label>
                        <input type="tel" id="tel" name="tel" placeholder="+33701020304">
                    </div>
                    <div class="form-group full-width">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" placeholder="Mot de passe">
                    </div>
                    <div class="form-group full-width">
                        <label for="password_confirm">Confirmation de mot de passe *</label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Mot de passe">
                    </div>
                    <div class="form-group full-width">
                        <label for="iban">IBAN</label>
                        <input type="text" id="iban" name="iban" placeholder="FROO 1234 5678 9123 4567 8912 345">
                    </div>
                    <div class="form-group full-width">
                        <label for="bic">BIC</label>
                        <input type="text" id="bic" name="bic" placeholder="CEPAFRPP751">
                    </div>
                    <div class="register-button-container">
                        <a href="../BO/connexion-compte.php" class="already-registered">J'ai déjà un compte professionnel</a>
                        <button type="submit" class="register-button">S'inscrire</button>
                    </div>
                </form>
            </div>
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
    <script src="script.js" defer></script>
</body>
</html>