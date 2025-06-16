<?php
// 1. SÉCURISATION ET INITIALISATION
$pro_id = require_once __DIR__ . '/../../includes/auth_check_pro.php';
require_once __DIR__ . '/../../includes/db.php';

// 2. RÉCUPÉRATION DES DONNÉES
$offer_id = $_GET['id'] ?? null;
if (!$offer_id) {
    header("Location: recherche.php");
    exit;
}

try {
    // Requête pour les détails de l'offre et son statut le plus récent
    $sql_offer = "
        SELECT 
            o.*, 
            a.street, a.postal_code, a.city, c.type as category_type,
            (SELECT s.status FROM statuts s WHERE s.offre_id = o.id ORDER BY s.changed_at DESC LIMIT 1) as current_status
        FROM offres o
        JOIN adresses a ON o.adresse_id = a.id
        JOIN categories c ON o.categorie_id = c.id
        WHERE o.id = :offer_id
    ";
    $stmt_offer = $pdo->prepare($sql_offer);
    $stmt_offer->execute([':offer_id' => $offer_id]);
    $offer = $stmt_offer->fetch(PDO::FETCH_ASSOC);

    if (!$offer || $offer['pro_id'] !== $pro_id) {
        header("Location: recherche.php?error=unauthorized");
        exit;
    }

    // Photos de l'offre (la photo principale en premier)
    $photo_stmt = $pdo->prepare("SELECT url FROM photos_offres WHERE offre_id = :offer_id ORDER BY url = :main_photo DESC, id");
    $photo_stmt->execute([':offer_id' => $offer_id, ':main_photo' => $offer['main_photo']]);
    $photos = $photo_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Avis et réponses
    $reviews_sql = "
        SELECT a.*, m.alias as user_alias, rp.id as response_id, rp.content as pro_response, rp.published_at as pro_response_date
        FROM avis a
        JOIN comptes_membre m ON a.membre_id = m.id
        LEFT JOIN reponses_pro rp ON a.id = rp.avis_id
        WHERE a.offre_id = :offer_id
        ORDER BY a.published_at DESC
    ";
    $reviews_stmt = $pdo->prepare($reviews_sql);
    $reviews_stmt->execute([':offer_id' => $offer_id]);
    $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

function display_stars($rating) {
    $html = '';
    $full = floor($rating);
    $half = ceil($rating) - $full;
    $empty = 5 - $full - $half;
    for ($i = 0; $i < $full; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($half) $html .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = 0; $i < $empty; $i++) $html .= '<i class="far fa-star"></i>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'offre - PACT Pro</title>
    <link rel="icon" href="images/Logo2withoutbgorange.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --bo-danger-bg: #f8d7da; --bo-danger-color: #721c24; }
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .main-content-offre { padding: 20px 0 40px 0; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .breadcrumb-bar a { color: var(--couleur-texte); text-decoration: none; font-size: 1.1em; display: flex; align-items: center; transition: color 0.2s; }
        .breadcrumb-bar a:hover { color: var(--couleur-principale); }
        .breadcrumb-bar i { margin-right: 10px; }
        .header-actions .btn { margin-left: 10px; }
        .btn-danger { background-color: var(--bo-danger-bg); color: var(--bo-danger-color); border-color: var(--bo-danger-bg); }
        .btn-danger:hover { background-color: #f1b0b7; border-color: #f1b0b7; }

        .offre-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .card-panel { background-color: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card-panel h3 { font-size: 1.3em; margin-top:0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        
        .gallery-container .main-image { width: 100%; height: auto; aspect-ratio: 16/10; border-radius: 8px; overflow: hidden; margin-bottom: 15px; background-color: #f0f0f0; }
        .gallery-container .main-image img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-thumbnails { display: flex; gap: 10px; flex-wrap: wrap; }
        .gallery-thumbnails .thumb { width: 80px; height: 80px; border-radius: 6px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: border-color 0.2s; }
        .gallery-thumbnails .thumb img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-thumbnails .thumb.active { border-color: var(--couleur-principale); }

        .main-info .title { font-size: 2.2em; font-weight: 600; margin-top:0; margin-bottom: 5px; line-height: 1.2; }
        .main-info .status-badge { display: inline-block; padding: 5px 12px; border-radius: 15px; font-size: 0.85em; font-weight: 500; margin-bottom: 20px; }
        .main-info .status-badge.actif { background-color: #d4edda; color: #155724; }
        .main-info .status-badge.inactif { background-color: #f8d7da; color: #721c24; }
        .main-info .price { font-size: 2em; font-weight: 600; color: var(--couleur-principale); margin-bottom: 25px; }
        
        .details-list ul { list-style: none; padding: 0; margin: 0; }
        .details-list li { display: flex; align-items: flex-start; margin-bottom: 15px; font-size: 0.95em; color: #555; }
        .details-list i { margin-right: 15px; color: var(--couleur-principale); width: 20px; text-align: center; padding-top: 3px; }
        
        .avis-card { border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .avis-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .avis-user { font-weight: 600; }
        .avis-rating { color: var(--couleur-principale); }
        .avis-card > p { margin: 5px 0 10px 0; }
        
        .pro-response { background-color: #f0f8ff; border-left: 4px solid #85c1e9; padding: 15px; margin-top: 15px; border-radius: 4px; }
        .pro-response p { margin: 0; font-size: 0.95em; }
        
        .pro-response-form { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd; }
        .pro-response-form textarea { width: 100%; min-height: 80px; padding: 10px; border-radius: 6px; border: 1px solid #ddd; font-family: inherit; font-size: 0.95em; }
        .pro-response-form button { margin-top: 10px; }
        
        @media (max-width: 992px) {
            .offre-layout { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .header-actions { width: 100%; display: flex; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="header-left">
                <a href="index.php"><img src="images/Logowithoutbgorange.png" alt="Logo PACT Pro" class="logo"></a>
                <span class="pro-text">Professionnel</span>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="recherche.php" class="active">Mes Offres</a></li>
                    <li><a href="publier-une-offre.php">Publier une offre</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="profil.php" class="btn btn-secondary">Mon profil</a>
                <a href="../deconnexion.php" class="btn btn-primary">Se déconnecter</a>
            </div>
        </div>
    </header>

    <main class="main-content-offre container">
        <div class="page-header">
            <div class="breadcrumb-bar">
                <a href="recherche.php"><i class="fas fa-arrow-left"></i> Retour à mes offres</a>
            </div>
            <div class="header-actions">
                <a href="publier-une-offre.php?edit=<?= $offer['id'] ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Modifier</a>
                <a href="supprimer-offre.php?id=<?= $offer['id'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?');"><i class="fas fa-trash"></i> Supprimer</a>
            </div>
        </div>
        
        <div class="offre-layout">
            <div class="left-column">
                <div class="card-panel gallery-container">
                    <div class="main-image">
                        <img id="main-gallery-image" src="../<?php echo htmlspecialchars($photos[0]['url'] ?? 'FO/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>">
                    </div>
                    <?php if(count($photos) > 1): ?>
                    <div class="gallery-thumbnails">
                        <?php foreach($photos as $photo): ?>
                        <div class="thumb <?php if ($photo['url'] === $photos[0]['url']) echo 'active'; ?>">
                            <img src="../<?php echo htmlspecialchars($photo['url']); ?>" alt="Miniature" onclick="changeMainImage(this)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-panel">
                    <h3>Description complète</h3>
                    <p><?php echo nl2br(htmlspecialchars($offer['description'])); ?></p>
                </div>
            </div>

            <div class="right-column">
                <div class="card-panel main-info">
                    <h1 class="title"><?php echo htmlspecialchars($offer['title']); ?></h1>
                    <?php if (isset($offer['current_status'])): ?>
                        <span class="status-badge <?php echo $offer['current_status'] ? 'actif' : 'inactif'; ?>">
                            Statut : <?php echo $offer['current_status'] ? 'Actif' : 'Inactif'; ?>
                        </span>
                    <?php endif; ?>
                    
                    <p class="price"><?php echo htmlspecialchars(number_format($offer['price'], 2, ',', ' ')); ?> €</p>
                    
                    <div class="details-list">
                        <h3>Détails</h3>
                        <ul>
                            <li><i class="fas fa-tag"></i><div><strong>Catégorie :</strong> <?php echo htmlspecialchars(ucfirst($offer['category_type'])); ?></div></li>
                            <li><i class="fas fa-map-marker-alt"></i><div><strong>Adresse :</strong> <?php echo htmlspecialchars($offer['street'] . ', ' . $offer['postal_code'] . ' ' . $offer['city']); ?></div></li>
                            <?php if(!empty($offer['phone'])): ?>
                                <li><i class="fas fa-phone"></i><div><strong>Téléphone :</strong> <?php echo htmlspecialchars($offer['phone']); ?></div></li>
                            <?php endif; ?>
                            <?php if(!empty($offer['website'])): ?>
                                <li><i class="fas fa-globe"></i><div><strong>Site Web :</strong> <a href="<?php echo htmlspecialchars($offer['website']); ?>" target="_blank">Visiter le site</a></div></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card-panel">
                    <h3>Résumé & Accessibilité</h3>
                    <p><strong>Résumé :</strong><br><?php echo htmlspecialchars($offer['summary']); ?></p>
                    <p><strong>Conditions d'accessibilité :</strong><br><?php echo htmlspecialchars($offer['accessibility']); ?></p>
                </div>
            </div>
        </div>

        <div class="card-panel">
            <h3>Gestion des avis (<?php echo count($reviews); ?>)</h3>
            <?php if (empty($reviews)): ?>
                <p>Cette offre n'a pas encore reçu d'avis.</p>
            <?php else: ?>
                <?php foreach($reviews as $review): ?>
                    <div class="avis-card">
                        <div class="avis-header">
                            <span class="avis-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars($review['user_alias']); ?></span>
                            <span class="avis-rating"><?php echo display_stars($review['rating']); ?></span>
                        </div>
                        <p><strong><?php echo htmlspecialchars($review['title']); ?></strong></p>
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <small>Publié le <?php echo date('d/m/Y', $review['published_at']); ?></small>

                        <?php if ($review['pro_response']): ?>
                            <div class="pro-response">
                                <p><strong><i class="fas fa-reply"></i> Votre réponse :</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($review['pro_response'])); ?></p>
                                <small>Le <?php echo date('d/m/Y', strtotime($review['pro_response_date'])); ?></small>
                            </div>
                        <?php else: ?>
                            <form action="repondre-avis.php" method="POST" class="pro-response-form">
                                <input type="hidden" name="avis_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="offre_id" value="<?php echo $offer['id']; ?>">
                                <textarea name="content" placeholder="Répondre publiquement à cet avis..." required></textarea>
                                <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
    
    <script>
        function changeMainImage(thumbElement) {
            const mainImage = document.getElementById('main-gallery-image');
            if (mainImage) {
                mainImage.src = thumbElement.src;
            }
            document.querySelectorAll('.gallery-thumbnails .thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbElement.parentElement.classList.add('active');
        }
    </script>
</body>
</html>