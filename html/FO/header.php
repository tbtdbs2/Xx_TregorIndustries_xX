<?php
// On vérifie la présence et la valeur du cookie 'user_type' pour savoir si l'utilisateur est un membre connecté.
$is_member_logged_in = isset($_COOKIE['user_type']) && $_COOKIE['user_type'] === 'membre';
?>
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
            <?php if ($is_member_logged_in): ?>
                <a href="profil.php" class="btn btn-secondary desktop-only">Mon profil</a>
                <a href="../deconnexion.php" class="btn btn-primary desktop-only">Déconnexion</a>
            <?php else: ?>
                <a href="../BO/index.php" class="pro-link desktop-only">Je suis professionnel</a>
                <a href="creation-compte.php" class="btn btn-secondary desktop-only">S'enregistrer</a>
                <a href="connexion-compte.php" class="btn btn-primary desktop-only active">Se connecter</a>
            <?php endif; ?>

            <div class="mobile-icons">
                <a href="index.php" class="mobile-icon" aria-label="Accueil"><i class="fas fa-home"></i></a>
                <?php if ($is_member_logged_in): ?>
                    <a href="profil.php" class="mobile-icon" aria-label="Profil"><i class="fas fa-user"></i></a>
                <?php endif; ?>
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
            <?php if ($is_member_logged_in): ?>
                <li><a href="profil.php">Mon profil</a></li>
                <li><a href="../deconnexion.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="/BO/index.php">Je suis professionnel</a></li>
                <li><a href="creation-compte.php">S'enregistrer</a></li>
                <li><a href="connexion-compte.php" class="active">Se connecter</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>