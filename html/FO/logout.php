<?php
    // 1. On démarre la session pour pouvoir y accéder
    session_start();

    // 2. On supprime toutes les variables de session
    $_SESSION = array();

    // 3. On détruit la session elle-même
    session_destroy();

    // 4. On redirige l'utilisateur vers la page d'accueil
    header('Location: ../index.html'); // Remonte d'un dossier pour atteindre index.html
    exit();
?>