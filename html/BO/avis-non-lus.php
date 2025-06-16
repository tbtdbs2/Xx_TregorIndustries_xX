<?php
// html/BO/avis-non-lus.php

require_once '../../includes/auth_check_pro.php';

if (!isset($_SESSION['pro_id'])) {
    die("Erreur: Impossible de récupérer l'identifiant du professionnel.");
}
$current_pro_id = $_SESSION['pro_id'];

$query = "
    SELECT DISTINCT o.id, o.title, o.created_at FROM offres o
    JOIN avis a ON o.id = a.offre_id
    WHERE o.pro_id = :pro_id 
    AND NOT EXISTS (SELECT 1 FROM reponses_pro rp WHERE rp.avis_id = a.id)
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['pro_id' => $current_pro_id]);
$offres_avec_avis_non_lus = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PACT Pro - Avis non lus</title>
    <link rel="icon" href="images/Logo2withoutbgorange.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Conteneur principal pour la liste des avis */
        .avis-list-container {
            margin-top: 2rem;
            background-color: var(--couleur-blanche);
            border: var(--bordure-standard-interface);
            border-radius: var(--border-radius-standard);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Chaque élément de la liste */
        .avis-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--espacement-moyen) var(--espacement-double);
            border-bottom: var(--bordure-standard-interface);
        }

        /* Ne pas mettre de bordure sur le dernier élément */
        .avis-list-item:last-child {
            border-bottom: none;
        }

        /* Contenu de gauche (titre, date) */
        .avis-item-content h3 {
            font-size: 1.1em;
            font-weight: var(--font-weight-medium);
            margin: 0 0 5px 0;
            color: var(--couleur-texte);
        }

        .avis-item-content p {
            font-size: 0.9em;
            color: var(--couleur-texte-footer);
            margin: 0;
        }

        /* Section de droite (bouton) */
        .avis-item-action .btn {
            white-space: nowrap; /* Empêche le texte du bouton de passer à la ligne */
        }

        /* Message quand la liste est vide */
        .avis-list-empty {
            padding: var(--espacement-triple);
            text-align: center;
            color: var(--couleur-texte-footer);
        }
        .header-right .profile-link-container + .btn-primary {
        margin-left: 1rem; 
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
            <a href="../deconnexion.php" class="btn btn-primary">Se déconnecter</a>
        </div>
    </div>
    </header>

<main class="container">
    <h1>Offres avec de nouveaux avis</h1>
    <p>Voici la liste de vos offres qui ont reçu des avis auxquels vous n'avez pas encore répondu.</p>

    <div class="avis-list-container">
        <?php if (count($offres_avec_avis_non_lus) > 0): ?>
            <?php foreach ($offres_avec_avis_non_lus as $offre): ?>
                <div class="avis-list-item">
                    <div class="avis-item-content">
                        <h3><?php echo htmlspecialchars($offre['title']); ?></h3>
                        <p>Offre publiée le <?php echo date('d/m/Y', strtotime($offre['created_at'])); ?></p>
                    </div>
                    <div class="avis-item-action">
                        <a href="offre.php?id=<?php echo $offre['id']; ?>" class="btn btn-primary">Gérer les avis</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="avis-list-empty">
                <p>Félicitations ! Vous avez répondu à tous les nouveaux avis.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="container" style="margin-top: 4rem;">
   <p style="text-align: center; color: var(--couleur-texte-footer);">&copy; <?php echo date('Y'); ?> PACT. Tous droits réservés.</p>
</footer>

</body>
</html>