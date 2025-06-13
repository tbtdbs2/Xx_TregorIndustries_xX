<?php

session_start(); // Démarre la session PHP (doit être la première chose)

require_once '../composants/generate_uuid.php';
require_once __DIR__ . '/../../includes/db.php';

$login_error = ''; // Variable pour stocker les messages d'erreur de connexion
// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $firstname = $_POST['name'] ?? '';
    $lastname = $_POST['last-name'] ?? '';
    $email = $_POST['email'] ?? '';
    $alias = $_POST['pseudo'] ?? '';
    $phone = $_POST['tel'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!preg_match('/^[\p{L}0-9 \-\'"]{2,100}$/u', $ville)) {
        $error_tab[] = 'Ville invalide';
    }

    if (!preg_match('/^\d{5}$/', $code_postal)) {
        $error_tab[] = 'Code postal invalide';
    }

    if (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $error_tab[] = 'Téléphone  invalide.';
    }

    if ($password !== $password_confirm) {
        $error_tab[] = 'Les mots de passe ne correspondent pas.';
    }

    // Validation des données
    if (empty($firstname) || empty($lastname) || empty($email) || empty($alias) || empty($phone) || empty($adresse) || empty($ville) || empty($code_postal) || empty($password) || empty($password_confirm)) {
        $login_error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = 'Adresse e-mail invalide.';
    } elseif (!empty($error_tab)) {
        foreach ($error_tab as $error) {
            print_r($error);
            $login_error .= $error . " ";
        }
    } else {
        // Vérification si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT * FROM comptes_membre WHERE email = :email OR alias = :alias");
        $stmt->execute(['email' => $email, 'alias' => $alias]);
        if ($stmt->rowCount() > 0) {
            $login_error = 'Un compte avec cet e-mail ou ce pseudonyme existe déjà.';
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
                INSERT INTO comptes_membre (id, adresse_id, email, password, phone, lastname, firstname, alias)
                VALUES (:id_compte_membre, :adresse_id, :email, :password, :phone, :lastname, :firstname, :alias)
            ");
            $id_compte_membre = generate_uuid(); // Génération d'un UUID pour le compte membre
            if ($stmtMembre->execute(['id_compte_membre' => $id_compte_membre, 'adresse_id' => $adresse_id, 'email' => $email, 'password' => $password, 'phone' => $phone, 'lastname' => $lastname, 'firstname' => $firstname, 'alias' => $alias])) {
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
    $firstname = '';
    $lastname = '';
    $email = '';
    $alias = '';
    $phone = '';
    $adresse = '';
    $ville = '';
    $code_postal = '';
    $password = '';
    $password_confirm = '';
    $login_error = ''; // Réinitialise l'erreur de connexion
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PACT - Création de compte</title><link rel="icon" href="images/Logo2withoutbg.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            width: 600px; 
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
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 15px 20px;
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input[type="name"],
        .form-group input[type="text"],
        .form-group input[type="last-name"],
        .form-group input[type="tel"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .register-button-container {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: row; 
            justify-content: space-between; 
            margin-top: 20px;
        }

        .register-button {
            background-color:var(--couleur-principale);
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .register-button:hover {
            background-color: var(--couleur-principale-hover);
        }

        .already-registered {
            text-decoration: underline;
            color: #000;
            align-self: center; /
        }

        .already-registered:hover {
            text-decoration: underline;
            color: #000;
        }

        /* Styles responsives pour le formulaire */
        @media (max-width: 768px) {
            .content-area {
                padding: 20px; 
            }

            .register-container {
                width: 90%; 
                padding: 30px 20px; 
            }

            .content-area h1 {
                font-size: 28px; 
            }

            .content-area p {
                font-size: 18px; 
                margin-bottom: 25px;
            }

            .register-form {
                grid-template-columns: 1fr; 
                gap: 15px 0; 
            }

            .form-group label {
                font-size: 13px;
            }

            .form-group input[type="name"],
            .form-group input[type="text"],
            .form-group input[type="last-name"],
            .form-group input[type="tel"],
            .form-group input[type="email"],
            .form-group input[type="password"] {
                padding: 12px;
                font-size: 15px;
            }

            .register-button-container {
                flex-direction: column-reverse; 
                align-items: stretch; 
                gap: 15px; 
                margin-top: 25px;
            }

            .register-button-container .already-registered {
                text-align: center; 
                margin-bottom: 5px; 
            }

            .register-button {
                width: 100%; 
                padding: 14px 20px;
            }
        }

        @media (max-width: 480px) { 
            .content-area {
                padding: 15px;
            }
            .register-container {
                padding: 25px 15px;
            }
            .content-area h1 {
                font-size: 24px;
            }
            .content-area p {
                font-size: 16px;
            }
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
                <a href="creation-compte.php" class="btn btn-secondary desktop-only active">S'enregistrer</a>
                <a href="connexion-compte.php" class="btn btn-primary desktop-only">Se connecter</a>
                <div class="mobile-icons">
                    <a href="index.php" class="mobile-icon" aria-label="Accueil"><i class="fas fa-home"></i></a>
                    <a href="profil.php" class="mobile-icon" aria-label="Profil"><i class="fas fa-user"></i></a>
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
                <li><a href="creation-compte.php" class="active">S'enregistrer</a></li>
                <li><a href="connexion-compte.php">Se connecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container content-area">
            <h1>Créer un compte</h1>
            <p>Rejoignez nous !</p>
            <?php
            // Affichage du message d'erreur pour l'utilisateur
            if (!empty($login_error)) {
                echo '<p style="color: red; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($login_error) . '</p>';
            }
            ?>
            <div class="register-container">
                <form class="register-form" action="creation-compte.php" method="POST">
                    <div class="form-group">
                        <label for="name">Prénom</label>
                        <input type="name" id="name" name="name" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label for="last-name">Nom</label>
                        <input type="last-name" id="last-name" name="last-name" placeholder="Doe">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="adressemail@exemple.com">
                    </div>
                    <div class="form-group">
                        <label for="pseudo">Pseudonyme</label>
                        <input type="text" id="pseudo" name="pseudo" placeholder="john_doe">
                    </div>
                    <div class="form-group">
                        <label for="tel">N° Téléphone</label>
                        <input type="tel" id="tel" name="tel" placeholder="+33701020304">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresse">Adresse postale</label>
                        <input type="text" id="adresse" name="adresse" placeholder="1 impasse Victor Hugo">
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" placeholder="Lannion">
                    </div>
                    <div class="form-group">
                        <label for="code_postal">Code Postal</label>
                        <input type="text" id="code_postal" name="code_postal" placeholder="22300">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Mot de passe">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmation de mot de passe</label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Mot de passe">
                    </div>
                    <div class="register-button-container">
                        <a href="#" class="already-registered">J'ai déjà un compte</a>
                        <button type="submit" class="register-button">S'inscrire</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        </footer>
    <script src="script.js" defer></script>
</body>
</html>