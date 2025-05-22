<?php
// Démarrer la session (doit être au TOUT DÉBUT du fichier)
session_start();

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
    header('Location: connexion-compte.php'); // Assurez-vous que le nom du fichier est correct
    exit; // Important pour arrêter l'exécution du script ici
}

// 2. Récupérer l'identifiant de l'utilisateur connecté

$userId = $_SESSION['user_id'];

$userLoggedIn = true; // Variable pour gérer l'affichage des liens dans le header

// 3. Interroger la base de données
// REMPLACEZ AVEC VOS INFORMATIONS DE CONNEXION À LA BASE DE DONNÉES
$dsn = 'mysql:host=localhost;dbname=sae;charset=utf8'; // Ex: dbname=pact_project
$username_db = 'root'; // Ex: root
$password_db = ''; // Ex: "" ou votre mot de passe

$membre = null; // Initialiser $membre à null

try {
    $pdo = new PDO($dsn, $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Pour afficher les erreurs SQL

    // Préparez la requête SQL pour éviter les injections SQL
    // Adaptez le nom de la table 'utilisateurs' et les noms des colonnes si besoin
    $sql = "SELECT cm.alias AS pseudonyme,
                   cm.firstname AS prenom, 
                   cm.lastname AS nom, 
                    a.street AS adresse_postale,
                    a.city AS ville, 
                    a.postal_code AS code_postal, 
                    cm.email,
                    cm.phone AS telephone
            FROM comptes_membre cm
            JOIN adresses a ON cm.adresse_id = a.id
            WHERE cm.id = :userId"; // Supposons que la clé primaire est 'id'
    $stmt = $pdo->prepare($sql);

    // Liez la valeur de l'ID de l'utilisateur au paramètre de la requête
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);

    // Exécutez la requête
    $stmt->execute();

    // Récupérez les informations de l'utilisateur
    $membre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$membre) {
        // Aucun utilisateur trouvé avec cet ID, cela peut indiquer un problème.
        // Vous pourriez déconnecter l'utilisateur ici ou afficher un message d'erreur plus spécifique.
        // Pour l'instant, on laisse $membre à null, les champs resteront vides ou avec les placeholders.
        error_log("Erreur : Utilisateur non trouvé dans la base de données pour l'ID de session : " . $userId);
        // Optionnel: Déconnexion et redirection
        // unset($_SESSION['user_id']);
        // session_destroy();
        // header('Location: connexion-compte.php?erreur=profil_introuvable');
        // exit;
    }

} catch (PDOException $e) {
    // En cas d'erreur de connexion ou d'exécution de la requête
    // Il est préférable de logguer cette erreur plutôt que de l'afficher directement à l'utilisateur en production
    error_log("Erreur de base de données sur profil.php : " . $e->getMessage());
    // Afficher un message générique à l'utilisateur
    // die("Une erreur est survenue lors de la récupération de vos informations. Veuillez réessayer plus tard.");
    // Pour le développement, vous pouvez laisser die() pour voir l'erreur :
    // die("Erreur de base de données : " . $e->getMessage());
    // Pour cet exemple, on va juste s'assurer que $membre reste null ou vide
    $membre = []; // ou $membre = null; pour que les champs soient vides
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
        /* Votre CSS existant ici - aucune modification nécessaire dans la section style pour cette demande */
        .container.content-area {
            padding: 32px 0px;
            text-align: center; /* centre le texte à l’intérieur */
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
            /* height: 286px; */ /* Hauteur auto pour s'adapter au contenu */
            flex-wrap: wrap;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }
        .input_pseudo {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 86px;
            gap: 8px;
            margin: 10px;
        }
        .img_profil { /* Style non utilisé dans le HTML fourni, mais conservé */
            width: 110px;
            height: 110px;
        }
        #pseudo {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }
        .input_prenom {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
            margin: 10px;
        }
        #prenom {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }
        .input_nom {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
            margin: 10px;
        }
        #nom {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }
        .nom_prenom {
            display: flex;
            gap: 40px;
        }
        .adresse_postal {
            display: flex;
            flex-wrap: wrap;
            width: 580px;
            height: 86px;
            gap: 8px;
            margin-left: 20px;
        }
        #adresse {
            box-sizing: border-box;
            width: 580px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }        
        .ville {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
            margin-left: 20px;
        }
        #ville {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        } 
        .code_postal {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
            margin-left: 20px;
        }
        #code_postal {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }  
        .mail {
            display: flex;
            flex-wrap: wrap;
            width: 580px;
            height: 70px;
            gap: 8px;
            margin-left: 20px;
        }
        #email {
            box-sizing: border-box;
            width: 580px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        } 
        .telephone {
            display: flex;
            flex-wrap: wrap;
            width: 270px;
            height: 70px;
            gap: 8px;
            margin-left: 20px;
        }
        #telephone {
            box-sizing: border-box;
            width: 270px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            padding: 12px 16px;
        }  
        
        @media (max-width: 768px) {
            .container.content-area {
                padding: 16px 0px;
            }
            .card_section {
                width: 90%; /* Ajusté pour un meilleur affichage sur mobile */
                height: auto;
                padding: 16px;
                flex-direction: column; /* Empiler les éléments verticalement */
                align-items: center; /* Centrer les éléments enfants */
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
                flex-direction: column; /* Empiler prénom et nom verticalement */
                width: 100%;
                gap: 16px; /* Espace entre prénom et nom */
            }
             .header-right .desktop-only {
                display: none; /* Cacher les liens desktop sur mobile */
            }
            .mobile-nav-links ul { /* Assurer que les liens mobiles s'affichent correctement */
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .mobile-nav-links ul li {
                padding: 10px 0;
                text-align: center;
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
                        <?php if ($userLoggedIn && $membre && isset($membre['prenom'])): ?>
                            <?php endif; ?>
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
            <?php if ($membre): // S'assurer que les informations du membre ont été chargées ?>
            <div class="grid-card">

                <div class="card_section Pseudonyme">
                    <div class="input_pseudo">
                        <label for="pseudo">Pseudonyme</label>
                        <input type="text" id="pseudo" placeholder="Non défini" readonly="readonly" 
                               value="<?php echo isset($membre['pseudonyme']) ? htmlspecialchars($membre['pseudonyme']) : ''; ?>">
                    </div>
                    <div class="nom_prenom">
                        <div class="input_prenom">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" placeholder="Non défini" readonly="readonly"
                                   value="<?php echo isset($membre['prenom']) ? htmlspecialchars($membre['prenom']) : ''; ?>">   
                        </div>
                        <div class="input_nom">   
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" placeholder="Non défini" readonly="readonly"
                                   value="<?php echo isset($membre['nom']) ? htmlspecialchars($membre['nom']) : ''; ?>">
                        </div>
                    </div>    
                </div>

                <div class="card_section Adresse">
                    <div class="adresse_postal">
                        <label for="adresse">Adresse postale</label>
                        <input type="text" id="adresse" placeholder="Non définie" readonly="readonly"
                               value="<?php echo isset($membre['adresse_postale']) ? htmlspecialchars($membre['adresse_postale']) : ''; ?>">
                    </div>
                    <div class="ville">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" placeholder="Non définie" readonly="readonly"
                               value="<?php echo isset($membre['ville']) ? htmlspecialchars($membre['ville']) : ''; ?>">   
                    </div> 
                    <div class="code_postal">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" placeholder="Non défini" readonly="readonly"
                               value="<?php echo isset($membre['code_postal']) ? htmlspecialchars($membre['code_postal']) : ''; ?>"> 
                    </div>
                </div>

                <div class="card_section Contact">
                    <div class="mail">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Non défini" readonly="readonly" value="<?php echo isset($membre['email']) ? htmlspecialchars($membre['email']) : ''; ?>">
                    </div>
                    <div class="telephone">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" placeholder="Non défini" readonly="readonly" value="<?php echo isset($membre['telephone']) ? htmlspecialchars($membre['telephone']) : ''; ?>">   
                    </div>
                </div>
                </div>
            <?php else: ?>
                <p>Vos informations de profil n'ont pas pu être chargées. Veuillez contacter le support si le problème persiste.</p>
                <?php if (isset($e)) : // Si une exception PDO a été attrapée et que vous êtes en développement ?>
                    <p style="color:red; font-family:monospace;">Erreur DÉVELOPPEMENT: <?php echo htmlspecialchars($e->getMessage()); ?></p>
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
    <script src="script.js" defer></script>
</body>
</html>