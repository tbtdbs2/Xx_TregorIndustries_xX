-- Désactiver temporairement les vérifications de clés étrangères pour faciliter l'insertion
SET FOREIGN_KEY_CHECKS=0;

-- Vider les tables (optionnel, soyez TRÈS prudent si vous avez des données importantes)
-- Décommentez les lignes suivantes pour vider les tables avant l'insertion.

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


-- Peuplement des adresses
SET @adresse_id_1 = UUID();
SET @adresse_id_2 = UUID();
SET @adresse_id_3 = UUID();
SET @adresse_id_4 = UUID();
SET @adresse_id_5 = UUID();

INSERT INTO `adresses` (`id`, `street`, `postal_code`, `city`) VALUES
(@adresse_id_1, '2 Quai de la Marine', '22300', 'Lannion'),
(@adresse_id_2, '18 Boulevard des Mimosas', '22700', 'Perros-Guirec'),
(@adresse_id_3, '5 Rue des Korrigans', '22560', 'Trébeurden'),
(@adresse_id_4, '101 Rue du Commerce', '75015', 'Paris'),
(@adresse_id_5, '22 Vieux Port', '13002', 'Marseille');

-- Peuplement des comptes pro
SET @compte_pro_id_1 = UUID();
SET @compte_pro_id_2 = UUID();

INSERT INTO `comptes_pro` (`id`, `adresse_id`, `email`, `password`, `phone`, `company_name`, `is_private`, `siren`, `iban`, `bic`) VALUES
(@compte_pro_id_1, @adresse_id_1, 'contact@aventurebretonne.bzh', 'aventureMDP', '0296112233', 'Aventure Bretonne', 0, '123456789', 'FR7612345678901234567890123', 'AGRIFRPPXXX'),
(@compte_pro_id_2, @adresse_id_4, 'reservations@parisexplorer.fr', 'parisExplorerMDP', '0144556677', 'Paris Explorer SAS', 0, '987654321', 'FR7698765432109876543210987', 'BNPAFRPPXXX');

-- Peuplement des comptes membre
SET @compte_membre_id_1 = UUID();
SET @compte_membre_id_2 = UUID();
SET @compte_membre_id_3 = UUID();

INSERT INTO `comptes_membre` (`id`, `adresse_id`, `email`, `password`, `phone`, `lastname`, `firstname`, `alias`) VALUES
(@compte_membre_id_1, @adresse_id_2, 'yann.le-goff@email.bzh', 'passwordMembre123', '0610203040', 'Le Goff', 'Yann', 'YannLG'),
(@compte_membre_id_2, @adresse_id_3, 'marie.riou@email.fr', 'autreMotDePasse456', '0650607080', 'Riou', 'Marie', 'MarieR22'),
(@compte_membre_id_3, @adresse_id_5, 'pierre.durand@email.com', 'encoreUnPass789', '0780900010', 'Durand', 'Pierre', 'Pierro13');

-- Peuplement des tags
SET @tag_id_plein_air = UUID();
SET @tag_id_famille = UUID();
SET @tag_id_sport = UUID();
SET @tag_id_culture = UUID();
SET @tag_id_gastronomie = UUID();
SET @tag_id_mer = UUID();

INSERT INTO `tags` (`id`, `name`) VALUES
(@tag_id_plein_air, 'Plein air'),
(@tag_id_famille, 'Famille'),
(@tag_id_sport, 'Sport'),
(@tag_id_culture, 'Culture'),
(@tag_id_gastronomie, 'Gastronomie'),
(@tag_id_mer, 'Mer');

-- --- Catégories et Offres ---

-- OFFRE 1: Activité - Kayak en Côte de Granit Rose
SET @categorie_activite_kayak_id = UUID();
INSERT INTO `categories` (`id`, `type`) VALUES (@categorie_activite_kayak_id, 'activite');
INSERT INTO `activites` (`categorie_id`, `duration`, `minimum_price`, `required_age`) VALUES
(@categorie_activite_kayak_id, 180, 35.00, 8);

SET @offre_kayak_id = UUID();
INSERT INTO `offres` (`id`, `categorie_id`, `adresse_id`, `pro_id`, `title`, `summary`, `description`, `main_photo`, `rating`, `reviews_nb`, `accessibility`, `website`, `phone`, `price`, `created_at`, `updated_at`) VALUES
(@offre_kayak_id, @categorie_activite_kayak_id, @adresse_id_2, @compte_pro_id_1, 'Kayak Granit Rose', 'Explorez la Côte de Granit Rose en kayak de mer.', 'Une balade de 3h accompagnée par un moniteur diplômé. Découvrez des paysages à couper le souffle accessibles uniquement par la mer. Matériel fourni.', 'uploads/offres/kayak_granit_rose.jpg', 4.7, 75, 'Savoir nager est requis. Enfants à partir de 8 ans accompagnés.', 'http:/aventurebretonne.bzh/kayak', '0296112233', 40.00, NOW(), NOW());

INSERT INTO `photos_offres` (`id`, `offre_id`, `url`) VALUES
(UUID(), @offre_kayak_id, 'uploads/offres/kayak_granit_rose_detail1.jpg'),
(UUID(), @offre_kayak_id, 'uploads/offres/kayak_granit_rose_detail2.jpg');

INSERT INTO `statuts` (`id`, `offre_id`, `status`, `changed_at`) VALUES (UUID(), @offre_kayak_id, 1, NOW());

INSERT INTO `offres_tags` (`offre_id`, `tag_id`) VALUES
(@offre_kayak_id, @tag_id_plein_air),
(@offre_kayak_id, @tag_id_sport),
(@offre_kayak_id, @tag_id_mer),
(@offre_kayak_id, @tag_id_famille);

-- OFFRE 2: Visite - Château de la Roche Jagu
SET @categorie_visite_chateau_id = UUID();
INSERT INTO `categories` (`id`, `type`) VALUES (@categorie_visite_chateau_id, 'visite');
INSERT INTO `visites` (`categorie_id`, `duration`, `minimum_price`, `date`, `start_time`, `is_guided_tour`) VALUES
(@categorie_visite_chateau_id, 90, 8.00, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '15:00:00', 1);

SET @offre_chateau_id = UUID();
INSERT INTO `offres` (`id`, `categorie_id`, `adresse_id`, `pro_id`, `title`, `summary`, `description`, `main_photo`, `rating`, `reviews_nb`, `accessibility`, `website`, `phone`, `price`, `created_at`, `updated_at`) VALUES
(@offre_chateau_id, @categorie_visite_chateau_id, @adresse_id_1, @compte_pro_id_1, 'Visite Château Roche Jagu', 'Découvrez le château médiéval et ses jardins remarquables.', 'Visite guidée du château et promenade libre dans les jardins contemporains d''inspiration médiévale surplombant le Trieux.', 'uploads/offres/roche_jagu.jpg', 4.5, 120, 'Partiellement accessible PMR (jardins). Château avec escaliers.', 'http:/www.larochejagu.fr', '0296112233', 10.00, NOW(), NOW());

INSERT INTO `statuts` (`id`, `offre_id`, `status`, `changed_at`) VALUES (UUID(), @offre_chateau_id, 1, NOW());
INSERT INTO `offres_tags` (`offre_id`, `tag_id`) VALUES
(@offre_chateau_id, @tag_id_culture),
(@offre_chateau_id, @tag_id_famille),
(@offre_chateau_id, @tag_id_plein_air);


-- OFFRE 3: Restauration - Crêperie Bretonne
SET @categorie_creperie_id = UUID();
INSERT INTO `categories` (`id`, `type`) VALUES (@categorie_creperie_id, 'restauration');
INSERT INTO `restaurations` (`categorie_id`, `menu_url`, `price_range`) VALUES
(@categorie_creperie_id, 'http:/aventurebretonne.bzh/creperie/menu', '€');

SET @offre_creperie_id = UUID();
INSERT INTO `offres` (`id`, `categorie_id`, `adresse_id`, `pro_id`, `title`, `summary`, `description`, `main_photo`, `rating`, `reviews_nb`, `accessibility`, `website`, `phone`, `price`, `created_at`, `updated_at`) VALUES
(@offre_creperie_id, @categorie_creperie_id, @adresse_id_3, @compte_pro_id_1, 'Crêperie du Port', 'Crêpes et galettes traditionnelles face à la mer.', 'Dégustez nos spécialités bretonnes, galettes de sarrasin et crêpes de froment, élaborées avec des produits locaux. Terrasse ensoleillée.', 'uploads/offres/creperie_port.jpg', 4.6, 95, 'Accessible PMR.', 'http:/aventurebretonne.bzh/creperie', '0296112233', 15.00, NOW(), NOW());

INSERT INTO `statuts` (`id`, `offre_id`, `status`, `changed_at`) VALUES (UUID(), @offre_creperie_id, 1, NOW());
INSERT INTO `offres_tags` (`offre_id`, `tag_id`) VALUES
(@offre_creperie_id, @tag_id_gastronomie),
(@offre_creperie_id, @tag_id_famille),
(@offre_creperie_id, @tag_id_mer);


-- --- Peuplement des tables liées ---

-- Prestations
SET @presta_id_wifi = UUID();
SET @presta_id_parking = UUID();
SET @presta_id_pmr = UUID();
SET @presta_id_animaux = UUID();
INSERT INTO `prestations` (`id`, `name`) VALUES
(@presta_id_wifi, 'Wifi gratuit'),
(@presta_id_parking, 'Parking gratuit'),
(@presta_id_pmr, 'Accessible PMR'),
(@presta_id_animaux, 'Animaux acceptés');

-- Lier prestations à l'activité Kayak
INSERT INTO `activites_prestations_incluses` (`activite_id`, `prestation_id`) VALUES
(@categorie_activite_kayak_id, @presta_id_parking);
INSERT INTO `activites_prestations_non_incluses` (`activite_id`, `prestation_id`) VALUES
(@categorie_activite_kayak_id, @presta_id_wifi); -- Supposons wifi non inclus

-- Horaires pour l'activité Kayak
INSERT INTO `horaires_activites` (`id`, `activite_id`, `date`, `start_time`) VALUES
(UUID(), @categorie_activite_kayak_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00'),
(UUID(), @categorie_activite_kayak_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:30:00'),
(UUID(), @categorie_activite_kayak_id, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:30:00');

-- Langues
SET @lang_id_fr = UUID();
SET @lang_id_en = UUID();
SET @lang_id_br = UUID();
INSERT INTO `langues` (`id`, `language`) VALUES
(@lang_id_fr, 'Français'),
(@lang_id_en, 'Anglais'),
(@lang_id_br, 'Breton');

-- Lier langues à la visite du château
INSERT INTO `visites_langues` (`visite_id`, `langue_id`) VALUES
(@categorie_visite_chateau_id, @lang_id_fr),
(@categorie_visite_chateau_id, @lang_id_en);

-- Repas
SET @repas_id_midi = UUID();
SET @repas_id_soir = UUID();
SET @repas_id_gouter = UUID();
INSERT INTO `repas` (`id`, `name`) VALUES
(@repas_id_midi, 'Déjeuner'),
(@repas_id_soir, 'Dîner'),
(@repas_id_gouter, 'Goûter');

-- Lier repas à la crêperie
INSERT INTO `restaurations_repas` (`restauration_id`, `repas_id`) VALUES
(@categorie_creperie_id, @repas_id_midi),
(@categorie_creperie_id, @repas_id_soir),
(@categorie_creperie_id, @repas_id_gouter);

-- Horaires pour la crêperie
INSERT INTO `horaires_restaurants` (`id`, `restauration_id`, `day`, `start_time`, `end_time`) VALUES
(UUID(), @categorie_creperie_id, 'mardi', '12:00:00', '14:30:00'),
(UUID(), @categorie_creperie_id, 'mardi', '19:00:00', '21:30:00'),
(UUID(), @categorie_creperie_id, 'mercredi', '12:00:00', '14:30:00'),
(UUID(), @categorie_creperie_id, 'mercredi', '19:00:00', '21:30:00'),
(UUID(), @categorie_creperie_id, 'jeudi', '12:00:00', '14:30:00'),
(UUID(), @categorie_creperie_id, 'jeudi', '19:00:00', '21:30:00'),
(UUID(), @categorie_creperie_id, 'vendredi', '12:00:00', '14:30:00'),
(UUID(), @categorie_creperie_id, 'vendredi', '19:00:00', '22:00:00'),
(UUID(), @categorie_creperie_id, 'samedi', '12:00:00', '22:00:00'), -- Continu
(UUID(), @categorie_creperie_id, 'dimanche', '12:00:00', '15:00:00');

-- Avis
INSERT INTO `avis` (`id`, `membre_id`, `offre_id`, `title`, `comment`, `rating`, `visit_date`, `context`, `viewed`, `thumb_up_nb`, `thumb_down_nb`, `published_at`) VALUES
(UUID(), @compte_membre_id_1, @offre_kayak_id, 'Incroyable !', 'Une expérience mémorable, le moniteur était génial et le site est magnifique. A refaire !', 5.0, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'famille', 1, 22, 0, UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 6 DAY))),
(UUID(), @compte_membre_id_2, @offre_chateau_id, 'Belle découverte', 'Le château est bien conservé et les jardins sont superbes. La guide était passionnée.', 4.0, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'couple', 0, 10, 1, UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 11 DAY))),
(UUID(), @compte_membre_id_3, @offre_creperie_id, 'Délicieuses crêpes', 'Les meilleures crêpes que j''ai mangées depuis longtemps. Service rapide et agréable.', 4.5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'amis', 1, 15, 0, UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY)));

-- Options (pour futures souscriptions)
SET @option_premium_id = UUID();
SET @option_highlight_id = UUID();
INSERT INTO `options` (`id`, `name`, `price`) VALUES
(@option_premium_id, 'Mise en avant Premium', 29.99),
(@option_highlight_id, 'Offre à la Une', 19.99);


-- auth_tokens (exemple simple)
INSERT INTO `auth_tokens` (`id`, `email`, `token`) VALUES
(UUID(), 'contact@aventurebretonne.bzh', SHA2(CONCAT('secret_token_1', NOW()), 256)),
(UUID(), 'yann.le-goff@email.bzh', SHA2(CONCAT('secret_token_2', NOW()), 256));


-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS=1;

SELECT 'Peuplement de la base de données terminé avec des UUIDs générés par SQL.' AS status;