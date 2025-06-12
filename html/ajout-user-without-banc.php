<?php

/**
 * Script pour ajouter un nouveau compte professionnel sans informations bancaires.
 *
 * Ce script est destiné à être exécuté manuellement pour des tests ou des insertions administratives.
 * Assurez-vous que les chemins vers `db.php` et `generate_uuid.php` sont corrects.
 */

// --- 1. Inclusion des dépendances ---

// Ajustez le chemin si nécessaire. Ici, on suppose que le script est dans un dossier à la racine.
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../composants/generate_uuid.php';


// --- 2. Définition des données du nouveau compte ---

// Modifiez ces valeurs pour le compte que vous souhaitez créer.
$email = 'magie.des.arbres@example.com';
$mot_de_passe = 'motdepasseSolide456!';
$telephone = '0655443322';
$nom_entreprise = 'La Magie des Arbres';
$siren = '987654321'; // 9 chiffres
$secteur = 'privé'; // 'privé' ou 'public'

// Adresse de l'entreprise
$rue = '3 Allée des Soupirs';
$ville = 'Lannion';
$code_postal = '22300';


// --- 3. Validation et préparation ---

// On vérifie que la connexion PDO est bien établie
if (!isset($pdo)) {
    die("Erreur critique: La connexion à la base de données n'a pas pu être établie. Vérifiez votre fichier 'db.php'.");
}


// --- 4. Logique d'insertion dans la base de données ---

try {
    // On commence une transaction pour s'assurer que les deux insertions (adresse et compte)
    // sont effectuées ensemble. Si l'une échoue, l'autre est annulée.
    $pdo->beginTransaction();

    // Étape A : Insérer l'adresse dans la table `adresses`
    $adresse_id = generate_uuid();
    
    // CORRECTION : La colonne 'country' a été retirée de la requête car
    // elle n'existe pas dans votre schéma de base de données.
    $stmtAdresse = $pdo->prepare(
        "INSERT INTO adresses (id, street, city, postal_code) VALUES (:id, :street, :city, :postal_code)"
    );
    $stmtAdresse->execute([
        ':id' => $adresse_id,
        ':street' => $rue,
        ':city' => $ville,
        ':postal_code' => $code_postal
    ]);

    // Étape B : Insérer le compte professionnel dans la table `comptes_pro`
    // Notez que `iban` et `bic` ne sont pas inclus. La base de données leur assignera la valeur NULL par défaut.
    $pro_id = generate_uuid();
    $stmtPro = $pdo->prepare(
        "INSERT INTO comptes_pro (id, adresse_id, email, password, phone, company_name, is_private, siren) 
         VALUES (:id, :adresse_id, :email, :password, :phone, :company_name, :is_private, :siren)"
    );
    $stmtPro->execute([
        ':id' => $pro_id,
        ':adresse_id' => $adresse_id,
        ':email' => $email,
        ':password' => $mot_de_passe,
        ':phone' => $telephone,
        ':company_name' => $nom_entreprise,
        ':is_private' => ($secteur === 'privé' ? 1 : 0),
        ':siren' => $siren,
    ]);

    // Si tout s'est bien passé, on valide la transaction
    $pdo->commit();

    echo "Le compte professionnel pour '" . htmlspecialchars($nom_entreprise) . "' a été créé avec succès !";
    echo "<br>ID du compte : " . htmlspecialchars($pro_id);

} catch (PDOException $e) {
    // En cas d'erreur, on annule la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // On affiche un message d'erreur
    // Le code '23000' correspond à une violation de contrainte d'unicité (par exemple, email déjà existant)
    if ($e->getCode() == '23000') {
        die("Erreur lors de la création du compte : L'adresse email '" . htmlspecialchars($email) . "' existe déjà.");
    } else {
        die("Erreur lors de la création du compte : " . $e->getMessage());
    }
}
?>