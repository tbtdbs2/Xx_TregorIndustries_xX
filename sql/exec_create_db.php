<?php
// Charger les variables d'environnement
$env = parse_ini_file(__DIR__ . '/../.env');

// Extraire les infos de connexion
$host =     $env['HOST'];
$user =     $env['DB_USER'];
$password = $env['MARIADB_PASSWORD'];
$port =     $env['MARIADB_PORT'] ?? 3306;
$database = $env['DB_NAME'];

echo "Données du .env chargées !\n";

// Lire le contenu du script SQL
$sqlFileName = 'create_db.sql';
$sqlFilePath = __DIR__ . '/' . $sqlFileName;
$sql = file_get_contents($sqlFilePath);

if ($sql === false) {
    die("❌ Impossible de lire le fichier SQL '$sqlFileName'.");
} else {
    echo "$sqlFileName chargé avec succès !\n";
}

// Connexion a MariaDB
$conn = new mysqli($host, $user, $password, '', $port);
if ($conn->connect_error) {
    die("❌ Échec de la connexion : " . $conn->connect_error);
} else {
    echo "Connexion à l'hôte $host réussie !\n";
}

// Creer la base de donnees si elle n'existe pas
$conn->query("CREATE DATABASE IF NOT EXISTS `$database`");
$conn->select_db($database);

// Executer les requetes multiples du fichier SQL
if ($conn->multi_query($sql)) {
    do {
        // Stocker les resultats et les ignorer
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "✅ Script exécuté avec succès.\n";
} else {
    echo "❌ Erreur lors de l'exécution du script : " . $conn->error;
}

$conn->close();
