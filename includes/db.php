<?php
// Charger les variables d'environnement
$env = parse_ini_file(__DIR__ . '/../.env');

//Extraire les infos de connexion
$host =     $env['HOST'];
$user =     $env['DB_USER'];
$password = $env['MARIADB_PASSWORD'];
$port =     $env['MARIADB_PORT'] ?? 3306;
$database = $env['DB_NAME'];

// echo "Données du .env chargées !\n";
//print_r($GLOBALS);
try {
    // Connexion à MariaDB avec PDO
    // $dsn = "mysql:host=127.0.0.1;dbname=$database;port=$port;charset=utf8mb4";
    $dsn = "mysql:host=$host;dbname=$database;port=$port;charset=utf8mb4";
    // var_dump($dsn, true);
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // echo "Connexion à l'hôte $host réussie !\n";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

/*
    Exemple de requête

    <?php
    require_once 'db.php'; / Connexion à la BDD

    $stmt = $pdo->query("SELECT * FROM utilisateurs");

    while ($row = $stmt->fetch()) {
        echo $row['nom'] . '<br>';
    }
    ?>
*/
