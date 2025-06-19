-- Désactiver temporairement les vérifications de clés étrangères pour faciliter l'insertion
SET FOREIGN_KEY_CHECKS=0;

-- =================================================================================
-- VIDAGE DES TABLES
-- =================================================================================
DELETE FROM `photos_avis`;
DELETE FROM `reponses_pro`;
DELETE FROM `avis`;
DELETE FROM `visites_langues`;
DELETE FROM `souscriptions`;
DELETE FROM `horaires_restaurants`;
DELETE FROM `restaurations_repas`;
DELETE FROM `horaires_attractions`;
DELETE FROM `attractions`;
DELETE FROM `horaires_activites`;
DELETE FROM `activites_prestations_non_incluses`;
DELETE FROM `activites_prestations_incluses`;
DELETE FROM `photos_offres`;
DELETE FROM `statuts`;
DELETE FROM `offres_tags`;
DELETE FROM `offres`;
DELETE FROM `parcs_attractions`;
DELETE FROM `spectacles`;
DELETE FROM `visites`;
DELETE FROM `restaurations`;
DELETE FROM `activites`;
DELETE FROM `categories`;
DELETE FROM `comptes_membre`;
DELETE FROM `comptes_pro`;
DELETE FROM `adresses`;
DELETE FROM `tags`;
DELETE FROM `prestations`;
DELETE FROM `repas`;
DELETE FROM `langues`;
DELETE FROM `options`;
DELETE FROM `auth_tokens`;


-- =================================================================================
-- PEUPLEMENT DES DONNÉES DE BASE
-- =================================================================================

-- -------------------------------------
-- Adresses
-- -------------------------------------
-- Membres
SET @adresse_id_1 = UUID();
SET @adresse_id_2 = UUID();
SET @adresse_id_3 = UUID();
-- Pros
SET @adresse_id_4 = UUID();
SET @adresse_id_5 = UUID();
SET @adresse_id_6 = UUID();
SET @adresse_id_7 = UUID();
SET @adresse_id_8 = UUID();
SET @adresse_id_9 = UUID();
SET @adresse_id_10 = UUID();
SET @adresse_id_11 = UUID();
-- Offres
SET @adresse_offre_1 = UUID();
SET @adresse_offre_2 = UUID();
SET @adresse_offre_3 = UUID();
SET @adresse_offre_4 = UUID();
SET @adresse_offre_5 = UUID();
SET @adresse_offre_6 = UUID();
SET @adresse_offre_7 = UUID();
SET @adresse_offre_8 = UUID();
SET @adresse_offre_9 = UUID();
SET @adresse_offre_10 = UUID();

-- Insertion des adresses
INSERT INTO `adresses` (`id`, `street`, `postal_code`, `city`) VALUES
-- Adresses Membres (Bretagne)
(@adresse_id_1, '8 Rue de Siam', '29200', 'Brest'),
(@adresse_id_2, '3 Place du Général Leclerc', '22300', 'Lannion'),
(@adresse_id_3, '15 Rue Saint-Malo', '35000', 'Rennes'),
-- Adresses Pros (PACA)
(@adresse_id_4, '21 Rue de la République', '84000', 'Avignon'),
(@adresse_id_5, '3 Place Masséna', '06000', 'Nice'),
(@adresse_id_6, '7 Boulevard de Strasbourg', '83000', 'Toulon'),
(@adresse_id_7, 'Plage de l''Almanarre', '83400', 'Hyères'),
(@adresse_id_8, '42 Cours Mirabeau', '13100', 'Aix-en-Provence'),
(@adresse_id_9, '8 Place du Palais', '06600', 'Antibes'),
(@adresse_id_10, 'Port de Toulon', '83000', 'Toulon'),
(@adresse_id_11, 'Rue d''Italie', '13100', 'Aix-en-Provence'),
-- Adresses Offres (PACA)
(@adresse_offre_1, '12 Quai de Rive Neuve', '13007', 'Marseille'),
(@adresse_offre_2, '25 Rue d''Italie', '13100', 'Aix-en-Provence'),
(@adresse_offre_3, 'Promenade des Anglais', '06000', 'Nice'),
(@adresse_offre_4, 'J4 Esplanade', '13002', 'Marseille'),
(@adresse_offre_5, 'Plage de l''Almanarre', '83400', 'Hyères'),
(@adresse_offre_6, '15 Rue des Pinceaux', '13100', 'Aix-en-Provence'),
(@adresse_offre_7, 'Route de l''Aéroport', '83400', 'Hyères'),
(@adresse_offre_8, 'Boulevard du Littoral', '06600', 'Antibes'),
(@adresse_offre_9, 'Palais des Papes', '84000', 'Avignon'),
(@adresse_offre_10, 'Théâtre du Jeu de Paume', '13100', 'Aix-en-Provence');


-- -------------------------------------
-- Comptes Membre
-- -------------------------------------
SET @compte_membre_id_1 = UUID();
SET @compte_membre_id_2 = UUID();
SET @compte_membre_id_3 = UUID();

INSERT INTO `comptes_membre` (`id`, `adresse_id`, `email`, `password`, `phone`, `lastname`, `firstname`, `alias`, `otp_enabled`, `otp_secret`) VALUES
(@compte_membre_id_1, @adresse_id_1, 'iwan.cochet@etudiant.univ-rennes.fr', 'mdp_123', '0610203040', 'Cochet', 'Iwan', 'iwanc', '0', NULL),
(@compte_membre_id_2, @adresse_id_2, 'evan.collet@etudiant.univ-rennes.fr', 'mdp_123', '0650607080', 'Collet', 'Evan', 'evanc', '0', NULL),
(@compte_membre_id_3, @adresse_id_3, 'louis.milin@etudiant.univ-rennes.fr', 'mdp_123', '0780900010', 'Milin', 'Louis', 'louism', '0', NULL);

-- -------------------------------------
-- Comptes Professionnels
-- -------------------------------------
SET @compte_pro_id_1 = UUID();
SET @compte_pro_id_2 = UUID();
SET @compte_pro_id_3 = UUID();
SET @compte_pro_id_4 = UUID();
SET @compte_pro_id_5 = UUID();
SET @compte_pro_id_6 = UUID();
SET @compte_pro_id_7 = UUID();
SET @compte_pro_id_8 = UUID();

INSERT INTO `comptes_pro` (`id`, `adresse_id`, `email`, `password`, `phone`, `company_name`, `is_private`, `siren`, `iban`, `bic`) VALUES
-- Privé
(@compte_pro_id_1, @adresse_id_4, 'contact@gout-des-calanques.fr', 'mdp_123', '0491876543', 'SARL Le Goût des Calanques', 1, '111222333', 'FR7611122233344455566677889', 'BNPAFRPPXXX'),
(@compte_pro_id_2, @adresse_id_5, 'hello@escape-provence.com', 'mdp_123', '0492345678', 'Escape Game Provence', 1, '444555666', 'FR7644455566677788899900011', 'SOGEFRPPXXX'),
(@compte_pro_id_3, @adresse_id_6, 'info@velosriviera.com', 'mdp_123', '0493987654', 'Vélos Bleus de la Riviera', 1, '777888999', 'FR7677788899900011122233344', 'CEPAFRPPXXX'),
-- Associatif
(@compte_pro_id_4, @adresse_id_7, 'contact@patrimoine-var.org', 'mdp_123', '0494112233', 'Les Amis du Patrimoine Varois', 0, '121212121', NULL, NULL),
(@compte_pro_id_5, @adresse_id_8, 'info@voile-saint-tropez.fr', 'mdp_123', '0494543210', 'Club de Voile de Saint-Tropez', 0, '343434343', NULL, NULL),
-- Public
(@compte_pro_id_6, @adresse_id_9, 'evenementiel@cannes.fr', 'mdp_123', '0493392424', 'Mairie de Cannes - Événements', 0, '565656565', NULL, NULL),
(@compte_pro_id_7, @adresse_id_10, 'accueil@mercantour.fr', 'mdp_123', '0493032315', 'Parc National du Mercantour', 0, '787878787', NULL, NULL),
-- Privé
(@compte_pro_id_8, @adresse_id_11, 'manager@la-cantina-nice.fr', 'mdp_123', '0493887766', 'La Cantina', 1, '909090909', 'FR7690909090989898987654321', 'CMCIFRPPXXX');


-- -------------------------------------
-- Tags, Prestations, etc.
-- -------------------------------------
SET @tag_id_1 = UUID();
SET @tag_id_2 = UUID();
SET @tag_id_3 = UUID();
SET @tag_id_4 = UUID();
SET @tag_id_5 = UUID();
SET @tag_id_6 = UUID();
SET @tag_id_7 = UUID();
SET @tag_id_8 = UUID();
SET @tag_id_9 = UUID();
SET @tag_id_10 = UUID();
SET @tag_id_11 = UUID();
SET @tag_id_12 = UUID();
SET @tag_id_13 = UUID();
SET @tag_id_14 = UUID();
SET @tag_id_15 = UUID();
SET @tag_id_16 = UUID();
SET @tag_id_17 = UUID();
SET @tag_id_18 = UUID();
SET @tag_id_19 = UUID();
SET @tag_id_20 = UUID();
SET @tag_id_21 = UUID();
SET @tag_id_22 = UUID();
SET @tag_id_23 = UUID();
SET @tag_id_24 = UUID();
SET @tag_id_25 = UUID();

INSERT INTO `tags` (`id`, `name`) VALUES
(@tag_id_1, 'Culturel'),
(@tag_id_2, 'Patrimoine'),
(@tag_id_3, 'Histoire'),
(@tag_id_4, 'Urbain'),
(@tag_id_5, 'Nature'),
(@tag_id_6, 'Plein air'),
(@tag_id_7, 'Sport'),
(@tag_id_8, 'Nautique'),
(@tag_id_9, 'Gastronomie'),
(@tag_id_10, 'Musée'),
(@tag_id_11, 'Atelier'),
(@tag_id_12, 'Musique'),
(@tag_id_13, 'Famille'),
(@tag_id_14, 'Cinéma'),
(@tag_id_15, 'Cirque'),
(@tag_id_16, 'Son et lumière'),
(@tag_id_17, 'Humour'),
(@tag_id_18, 'Française'),
(@tag_id_19, 'Fruits de mer'),
(@tag_id_20, 'Asiatique'),
(@tag_id_21, 'Indienne'),
(@tag_id_22, 'Italienne'),
(@tag_id_23, 'Gastronomique'),
(@tag_id_24, 'Restauration rapide'),
(@tag_id_25, 'Crêperie');

SET @prestation_id_1 = UUID();
SET @prestation_id_2 = UUID();
SET @prestation_id_3 = UUID();

INSERT INTO `prestations` (`id`, `name`) VALUES
(@prestation_id_1, 'Équipement de sécurité'),
(@prestation_id_2, 'Moniteur diplômé'),
(@prestation_id_3, 'Assurance');

SET @repas_id_1 = UUID();
SET @repas_id_2 = UUID();
SET @repas_id_3 = UUID();

INSERT INTO `repas` (`id`, `name`) VALUES
(@repas_id_1, 'Déjeuner'),
(@repas_id_2, 'Dîner'),
(@repas_id_3, 'Boissons');

SET @langue_id_1 = UUID();
SET @langue_id_2 = UUID();
SET @langue_id_3 = UUID();

INSERT INTO `langues` (`id`, `language`) VALUES
(@langue_id_1, 'Français'),
(@langue_id_2, 'Anglais'),
(@langue_id_3, 'Italien');

SET @option_id_1 = UUID();
SET @option_id_2 = UUID();

INSERT INTO `options` (`id`, `name`, `price`) VALUES
(@option_id_1, 'En relief', 10.00),
(@option_id_2, 'A la une', 20.00);


-- =================================================================================
-- PEUPLEMENT DES OFFRES
-- =================================================================================

-- -------------------------------------
-- Catégories
-- -------------------------------------
SET @categorie_id_1 = UUID();
SET @categorie_id_2 = UUID();
SET @categorie_id_3 = UUID();
SET @categorie_id_4 = UUID();
SET @categorie_id_5 = UUID();
SET @categorie_id_6 = UUID();
SET @categorie_id_7 = UUID();
SET @categorie_id_8 = UUID();
SET @categorie_id_9 = UUID();
SET @categorie_id_10 = UUID();

INSERT INTO `categories` (`id`, `type`) VALUES
(@categorie_id_1, 'restauration'),
(@categorie_id_2, 'restauration'),
(@categorie_id_3, 'visite'),
(@categorie_id_4, 'visite'),
(@categorie_id_5, 'activite'),
(@categorie_id_6, 'activite'),
(@categorie_id_7, 'parc_attractions'),
(@categorie_id_8, 'parc_attractions'),
(@categorie_id_9, 'spectacle'),
(@categorie_id_10, 'spectacle');


-- -------------------------------------
-- Offres
-- -------------------------------------
SET @offre_id_1 = UUID();
SET @offre_id_2 = UUID();
SET @offre_id_3 = UUID();
SET @offre_id_4 = UUID();
SET @offre_id_5 = UUID();
SET @offre_id_6 = UUID();
SET @offre_id_7 = UUID();
SET @offre_id_8 = UUID();
SET @offre_id_9 = UUID();
SET @offre_id_10 = UUID();

INSERT INTO `offres` (`id`, `categorie_id`, `adresse_id`, `pro_id`, `title`, `summary`, `description`, `main_photo`, `reviews_nb`, `accessibility`, `website`, `phone`, `price`, `created_at`, `updated_at`) VALUES
-- Restaurations
(@offre_id_1, @categorie_id_1, @adresse_offre_1, @compte_pro_id_8, 'La Cantina Niçoise', 'Ambiance conviviale et saveurs italiennes sur le Cours Saleya.', 'Notre restaurant vous propose des spécialités niçoises et italiennes. Pissaladière, socca, et pâtes fraîches maison.', '/photos/la_cantina.jpg', 0, 'Terrasse accessible PMR', 'https://lacantina-nice.fr', '0493887766', 22.00, NOW(), NOW()),
(@offre_id_2, @categorie_id_2, @adresse_offre_2, @compte_pro_id_1, 'Le Goût des Calanques', 'Restaurant gastronomique face à la mer à Cassis.', 'Dégustez notre menu "Retour de Pêche", une ode aux produits de la Méditerranée dans un cadre exceptionnel.', '/photos/gout_calanques.jpg', 0, 'Accessible PMR', 'https://gout-des-calanques.fr', '0491876543', 90.00, NOW(), NOW()),
-- Visites
(@offre_id_3, @categorie_id_3, @adresse_offre_3, @compte_pro_id_4, 'Visite de Bormes-les-Mimosas', 'Balade historique dans un des plus beaux villages du Var.', 'Organisée par les Amis du Patrimoine, cette visite vous contera l''histoire du village, de son château à ses ruelles fleuries.', '/photos/bormes.jpg', 0, 'Difficile pour PMR (pentes, escaliers)', 'https://patrimoine-var.org', '0494112233', 10.00, NOW(), NOW()),
(@offre_id_4, @categorie_id_4, @adresse_offre_4, @compte_pro_id_7, 'Randonnée des Merveilles', 'Excursion guidée dans le Parc National du Mercantour.', 'Découvrez les gravures rupestres protohistoriques avec un guide du parc. Une journée de marche et d''histoire en pleine nature.', '/photos/mercantour.jpg', 0, 'Non accessible PMR', 'https://mercantour.fr', '0493032315', 35.00, NOW(), NOW()),
-- Activités
(@offre_id_5, @categorie_id_5, @adresse_offre_5, @compte_pro_id_5, 'Stage de Voile à St-Tropez', 'Apprenez à naviguer sur un voilier habitable.', 'Stage de 2 jours pour découvrir les bases de la voile et de la navigation dans le golfe mythique de Saint-Tropez.', '/photos/voile.jpg', 0, 'Non accessible', 'https://voile-saint-tropez.fr', '0494543210', 250.00, NOW(), NOW()),
(@offre_id_6, @categorie_id_6, @adresse_offre_6, @compte_pro_id_3, 'Tour du Cap d''Antibes à Vélo', 'Louez un vélo électrique et suivez notre guide.', 'Une balade facile de 3 heures pour découvrir les villas de milliardaires et les criques secrètes du Cap d''Antibes.', '/photos/velo_antibes.jpg', 0, 'Accessible si vélo adapté', 'https://velosriviera.com', '0493987654', 40.00, NOW(), NOW()),
-- Parcs d'attractions
(@offre_id_7, @categorie_id_7, @adresse_offre_7, @compte_pro_id_2, 'Parc "L''Enigme Absolue"', 'Un parc d''attractions basé sur la résolution d''énigmes.', 'Parcourez nos 10 salles thématiques, déjouez les pièges et résolvez le mystère final. Une aventure pour toute la famille.', '/photos/enigmeparc.jpg', 0, 'Partiellement accessible', 'https://escape-provence.com/parc', '0492345678', 28.00, NOW(), NOW()),
(@offre_id_8, @categorie_id_8, @adresse_offre_8, @compte_pro_id_7, 'Ecomusée de la Faune Alpine', 'Découvrez la faune du Mercantour à la Maison du Parc.', 'Un espace muséographique ludique pour comprendre l''écosystème de la montagne, avec des expositions et un film 3D.', '/photos/ecomusee.jpg', 0, 'Accessible PMR', 'https://mercantour.fr/ecomusee', '0493032315', 8.00, NOW(), NOW()),
-- Spectacles
(@offre_id_9, @categorie_id_9, @adresse_offre_9, @compte_pro_id_6, 'Cinéma sur la Plage', 'Projection en plein air pendant le Festival de Cannes.', 'Assistez gratuitement à la projection d''un classique du cinéma, les pieds dans le sable sur la plage Macé.', '/photos/cinema_plage.jpg', 0, 'Zone PMR prévue', 'https://cannes.fr', '0493392424', 0.00, NOW(), NOW()),
(@offre_id_10, @categorie_id_10, @adresse_offre_10, @compte_pro_id_2, 'Spectacle "Mentalista"', 'Un show de magie et de mentalisme immersif.', 'Dans notre salle de spectacle, laissez-vous bluffer par les tours incroyables de notre magicien résident.', '/photos/magie.jpg', 0, 'Accessible PMR', 'https://escape-provence.com/spectacle', '0492345678', 22.00, NOW(), NOW());

-- -------------------------------------
-- Tables spécifiques et tables de liaison
-- -------------------------------------
INSERT INTO `restaurations` (`categorie_id`, `menu_url`, `price_range`) VALUES
(@categorie_id_1, 'https://lacantina-nice.fr/menu', '€€'),
(@categorie_id_2, 'https://gout-des-calanques.fr/menu', '€€€');

INSERT INTO `visites` (`categorie_id`, `duration`, `minimum_price`, `date`, `start_time`, `is_guided_tour`) VALUES
-- Date de visite dans le futur (dans 1 mois)
(@categorie_id_3, 90, 10.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '10:30:00', 1),
-- Date de visite dans le futur (dans 45 jours)
(@categorie_id_4, 360, 35.00, DATE_ADD(CURDATE(), INTERVAL 45 DAY), '09:00:00', 1);

INSERT INTO `activites` (`categorie_id`, `duration`, `minimum_price`, `required_age`) VALUES
(@categorie_id_5, 1440, 250.00, 18),
(@categorie_id_6, 180, 40.00, 14);

INSERT INTO `parcs_attractions` (`categorie_id`, `minimum_price`, `required_age`, `attraction_nb`, `map_url`) VALUES
(@categorie_id_7, 28.00, 8, 10, 'https://escape-provence.com/parc/map'),
(@categorie_id_8, 8.00, 0, 5, 'https://mercantour.fr/ecomusee/plan');

INSERT INTO `spectacles` (`categorie_id`, `duration`, `minimum_price`, `date`, `start_time`, `capacity`) VALUES
-- Date de spectacle dans le futur (dans 1 mois)
(@categorie_id_9, 120, 0.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '21:30:00', 1000),
-- Date de spectacle dans le futur (dans 3 mois)
(@categorie_id_10, 75, 22.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), '21:00:00', 150);

INSERT INTO `offres_tags` (`offre_id`, `tag_id`) VALUES
(@offre_id_1, @tag_id_9),
(@offre_id_1, @tag_id_22),
(@offre_id_2, @tag_id_9),
(@offre_id_2, @tag_id_23),
(@offre_id_2, @tag_id_19),
(@offre_id_3, @tag_id_1),
(@offre_id_3, @tag_id_2),
(@offre_id_4, @tag_id_5),
(@offre_id_4, @tag_id_6),
(@offre_id_4, @tag_id_3),
(@offre_id_5, @tag_id_7),
(@offre_id_5, @tag_id_8),
(@offre_id_6, @tag_id_7),
(@offre_id_6, @tag_id_6),
(@offre_id_6, @tag_id_13),
(@offre_id_7, @tag_id_13),
(@offre_id_8, @tag_id_5),
(@offre_id_8, @tag_id_10),
(@offre_id_9, @tag_id_14),
(@offre_id_9, @tag_id_1),
(@offre_id_10, @tag_id_1);

INSERT INTO `activites_prestations_incluses` (`activite_id`, `prestation_id`) VALUES
(@categorie_id_5, @prestation_id_1),
(@categorie_id_5, @prestation_id_2),
(@categorie_id_6, @prestation_id_1);

INSERT INTO `visites_langues` (`visite_id`, `langue_id`) VALUES
(@categorie_id_3, @langue_id_1),
(@categorie_id_4, @langue_id_1),
(@categorie_id_4, @langue_id_3);

INSERT INTO `restaurations_repas` (`restauration_id`, `repas_id`) VALUES
(@categorie_id_1, @repas_id_1),
(@categorie_id_1, @repas_id_2),
(@categorie_id_2, @repas_id_2);

-- -------------------------------------
-- Souscriptions
-- -------------------------------------
-- La logique de la page d'accueil affiche les offres ayant une souscription 'A la une' active.
INSERT INTO `souscriptions` (`offre_id`, `option_id`, `duration`, `taken_date`, `launch_date`) VALUES
-- Souscription ACTIVE. Début : il y a 1 mois. Durée : 3 mois. Fin : dans 2 mois. DEVRAIT APPARAITRE.
(@offre_id_5, @option_id_2, 3, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),

-- Souscription ACTIVE. Début : il y a 5 jours. Durée : 6 mois. Fin : dans ~6 mois. DEVRAIT APPARAITRE.
(@offre_id_2, @option_id_2, 6, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY)),

-- Souscription EXPIREE. Début : il y a 6 mois. Durée : 3 mois. Fin : il y a 3 mois. NE DEVRAIT PAS APPARAITRE.
(@offre_id_6, @option_id_1, 3, DATE_SUB(CURDATE(), INTERVAL 190 DAY), DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),

-- Souscription FUTURE. Début : dans 1 mois. Durée : 2 mois. NE DEVRAIT PAS APPARAITRE.
(@offre_id_10, @option_id_2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH));


-- =================================================================================
-- PEUPLEMENT DU CONTENU UTILISATEUR
-- =================================================================================

-- -------------------------------------
-- Avis
-- -------------------------------------
SET @avis_id_1 = UUID();
SET @avis_id_2 = UUID();

INSERT INTO `avis` (`id`, `membre_id`, `offre_id`, `title`, `comment`, `rating`, `visit_date`, `context`, `viewed`, `thumb_up_nb`, `thumb_down_nb`, `published_at`) VALUES
-- Date de visite dans le passé récent (il y a 1 mois)
(@avis_id_1, @compte_membre_id_1, @offre_id_5, 'Expérience géniale !', 'Le stage de voile était parfait. Le moniteur, Fred, est un excellent pédagogue. On apprend en toute sécurité dans un cadre de rêve. Je reviendrai !', 5, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'solo', 1, 18, 0, NOW()),
-- Date de visite dans le passé récent (il y a 20 jours)
(@avis_id_2, @compte_membre_id_3, @offre_id_7, 'Original et bien fait', 'On a passé un super après-midi à L''Enigme Absolue. Les décors sont immersifs et les énigmes sont bien pensées. Idéal pour un groupe d''amis.', 4.5, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'amis', 1, 11, 0, NOW());

-- -------------------------------------
-- Réponses des professionnels
-- -------------------------------------
SET @reponse_id_1 = UUID();

INSERT INTO `reponses_pro` (`id`, `pro_id`, `avis_id`, `content`, `published_at`) VALUES
(@reponse_id_1, @compte_pro_id_5, @avis_id_1, 'Merci Iwan ! Nous sommes ravis que le stage avec Fred vous ait plu. Le golfe de Saint-Tropez est un terrain de jeu magnifique. Au plaisir de vous revoir sur l''eau.', NOW());

-- Le reste (photos, statuts, tokens) est généré de la même manière
INSERT INTO `photos_offres` (`id`, `offre_id`, `url`) VALUES (UUID(), @offre_id_5, '/photos/offres/voile_st_tropez_2.jpg');
INSERT INTO `photos_avis` (`id`, `avis_id`, `url`) VALUES (UUID(), @avis_id_1, '/photos/avis/selfie_voilier.jpg');
INSERT INTO `statuts` (`id`, `offre_id`, `status`, `changed_at`) SELECT UUID(), `id`, 1, NOW() FROM `offres`;
INSERT INTO `auth_tokens` (`id`, `email`, `token`) VALUES
(UUID(), 'evan.collet@etudiant.univ-rennes.fr', 'fake_jwt_token_for_evan_collet_xyz'),
(UUID(), 'contact@patrimoine-var.org', 'fake_jwt_token_for_patrimoine_var_abc');


-- =================================================================================
-- FINALISATION
-- =================================================================================

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS=1;

SELECT 'Peuplement de la base de données terminé.' AS status;