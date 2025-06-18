-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : mariadb:3306
-- Généré le : mer. 21 mai 2025 à 15:00
-- Version du serveur : 11.0.6-MariaDB-ubu2204
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sae`
--
CREATE DATABASE IF NOT EXISTS `sae` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sae`;

-- --------------------------------------------------------

--
-- Structure de la table `activites`
--

DROP TABLE IF EXISTS `activites`;
CREATE TABLE `activites` (
  `categorie_id` char(36) NOT NULL,
  `duration` int(11) NOT NULL,
  `minimum_price` float NOT NULL,
  `required_age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `activites_prestations_incluses`
--

DROP TABLE IF EXISTS `activites_prestations_incluses`;
CREATE TABLE `activites_prestations_incluses` (
  `activite_id` char(36) NOT NULL,
  `prestation_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `activites_prestations_non_incluses`
--

DROP TABLE IF EXISTS `activites_prestations_non_incluses`;
CREATE TABLE `activites_prestations_non_incluses` (
  `activite_id` char(36) NOT NULL,
  `prestation_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adresses`
--

DROP TABLE IF EXISTS `adresses`;
CREATE TABLE `adresses` (
  `id` char(36) NOT NULL,
  `street` varchar(32) NOT NULL,
  `postal_code` varchar(5) NOT NULL,
  `city` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attractions`
--

DROP TABLE IF EXISTS `attractions`;
CREATE TABLE `attractions` (
  `id` char(36) NOT NULL,
  `parc_attractions_id` char(36) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `auth_tokens`
--

DROP TABLE IF EXISTS `auth_tokens`;
CREATE TABLE `auth_tokens` (
  `id` char(36) NOT NULL,
  `email` varchar(64) NOT NULL,
  `token` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE `avis` (
  `id` char(36) NOT NULL,
  `membre_id` char(36) NOT NULL,
  `offre_id` char(36) NOT NULL,
  `title` varchar(64) NOT NULL,
  `comment` varchar(512) NOT NULL,
  `rating` float NOT NULL,
  `visit_date` date NOT NULL,
  `context` enum('affaires','couple','famille','amis','solo') NOT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT 0,
  `thumb_up_nb` int(11) NOT NULL DEFAULT 0,
  `thumb_down_nb` int(11) NOT NULL DEFAULT 0,
  `published_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `type` enum('activite','visite','spectacle','parc_attractions','restauration') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comptes_membre`
--

DROP TABLE IF EXISTS `comptes_membre`;
CREATE TABLE `comptes_membre` (
  `id` char(36) NOT NULL,
  `adresse_id` char(36) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `alias` varchar(32) NOT NULL,
  `otp_enabled` tinyint(1) NOT NULL,
  `otp_secret` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comptes_pro`
--

DROP TABLE IF EXISTS `comptes_pro`;
CREATE TABLE `comptes_pro` (
  `id` char(36) NOT NULL,
  `adresse_id` char(36) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `company_name` varchar(32) NOT NULL,
  `is_private` tinyint(1) NOT NULL,
  `siren` varchar(9) NOT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaires_activites`
--

DROP TABLE IF EXISTS `horaires_activites`;
CREATE TABLE `horaires_activites` (
  `id` char(36) NOT NULL,
  `activite_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaires_attractions`
--

DROP TABLE IF EXISTS `horaires_attractions`;
CREATE TABLE `horaires_attractions` (
  `id` char(36) NOT NULL,
  `attraction_id` char(36) NOT NULL,
  `day` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaires_restaurants`
--

DROP TABLE IF EXISTS `horaires_restaurants`;
CREATE TABLE `horaires_restaurants` (
  `id` char(36) NOT NULL,
  `restauration_id` char(36) NOT NULL,
  `day` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `langues`
--

DROP TABLE IF EXISTS `langues`;
CREATE TABLE `langues` (
  `id` char(36) NOT NULL,
  `language` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offres`
--

DROP TABLE IF EXISTS `offres`;
CREATE TABLE `offres` (
  `id` char(36) NOT NULL,
  `categorie_id` char(36) NOT NULL,
  `adresse_id` char(36) NOT NULL,
  `pro_id` char(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `summary` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `main_photo` text NOT NULL,
  `rating` float DEFAULT NULL,
  `reviews_nb` int(11) NOT NULL,
  `accessibility` varchar(256) NOT NULL,
  `website` text DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `price` float NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offres_tags`
--

DROP TABLE IF EXISTS `offres_tags`;
CREATE TABLE `offres_tags` (
  `offre_id` char(36) NOT NULL,
  `tag_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `id` char(36) NOT NULL,
  `name` varchar(32) NOT NULL,
  `price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parcs_attractions`
--

DROP TABLE IF EXISTS `parcs_attractions`;
CREATE TABLE `parcs_attractions` (
  `categorie_id` char(36) NOT NULL,
  `minimum_price` float NOT NULL,
  `required_age` int(11) NOT NULL,
  `attraction_nb` int(11) NOT NULL,
  `map_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos_avis`
--

DROP TABLE IF EXISTS `photos_avis`;
CREATE TABLE `photos_avis` (
  `id` char(36) NOT NULL,
  `avis_id` char(36) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos_offres`
--

DROP TABLE IF EXISTS `photos_offres`;
CREATE TABLE `photos_offres` (
  `id` char(36) NOT NULL,
  `offre_id` char(36) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prestations`
--

DROP TABLE IF EXISTS `prestations`;
CREATE TABLE `prestations` (
  `id` char(36) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `repas`
--

DROP TABLE IF EXISTS `repas`;
CREATE TABLE `repas` (
  `id` char(36) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reponses_pro`
--

DROP TABLE IF EXISTS `reponses_pro`;
CREATE TABLE `reponses_pro` (
  `id` char(36) NOT NULL,
  `pro_id` char(36) NOT NULL,
  `avis_id` char(36) NOT NULL,
  `content` varchar(512) NOT NULL,
  `published_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `restaurations`
--

DROP TABLE IF EXISTS `restaurations`;
CREATE TABLE `restaurations` (
  `categorie_id` char(36) NOT NULL,
  `menu_url` text NOT NULL,
  `price_range` enum('€','€€','€€€') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `restaurations_repas`
--

DROP TABLE IF EXISTS `restaurations_repas`;
CREATE TABLE `restaurations_repas` (
  `restauration_id` char(36) NOT NULL,
  `repas_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `souscriptions`
--

DROP TABLE IF EXISTS `souscriptions`;
CREATE TABLE `souscriptions` (
  `offre_id` char(36) NOT NULL,
  `option_id` char(36) NOT NULL,
  `duration` int(11) NOT NULL,
  `taken_date` date NOT NULL,
  `launch_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `spectacles`
--

DROP TABLE IF EXISTS `spectacles`;
CREATE TABLE `spectacles` (
  `categorie_id` char(36) NOT NULL,
  `duration` int(11) NOT NULL,
  `minimum_price` float NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statuts`
--

DROP TABLE IF EXISTS `statuts`;
CREATE TABLE `statuts` (
  `id` char(36) NOT NULL,
  `offre_id` char(36) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `changed_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` char(36) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `visites`
--

DROP TABLE IF EXISTS `visites`;
CREATE TABLE `visites` (
  `categorie_id` char(36) NOT NULL,
  `duration` int(11) NOT NULL,
  `minimum_price` float NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `is_guided_tour` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `visites_langues`
--

DROP TABLE IF EXISTS `visites_langues`;
CREATE TABLE `visites_langues` (
  `visite_id` char(36) NOT NULL,
  `langue_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activites`
--
ALTER TABLE `activites`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `activites_prestations_incluses`
--
ALTER TABLE `activites_prestations_incluses`
  ADD PRIMARY KEY (`activite_id`,`prestation_id`),
  ADD KEY `fk_activites_prestations_incluses_prestations` (`prestation_id`);

--
-- Index pour la table `activites_prestations_non_incluses`
--
ALTER TABLE `activites_prestations_non_incluses`
  ADD PRIMARY KEY (`activite_id`,`prestation_id`),
  ADD KEY `fk_activites_prestations_non_incluses_prestations` (`prestation_id`);

--
-- Index pour la table `adresses`
--
ALTER TABLE `adresses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `attractions`
--
ALTER TABLE `attractions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attractions_parcs_attractions` (`parc_attractions_id`);

--
-- Index pour la table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_avis_comptes_membre` (`membre_id`),
  ADD KEY `fk_avis_offres` (`offre_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `comptes_membre`
--
ALTER TABLE `comptes_membre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comptes_membre_adresses` (`adresse_id`);

--
-- Index pour la table `comptes_pro`
--
ALTER TABLE `comptes_pro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comptes_pro_adresses` (`adresse_id`);

--
-- Index pour la table `horaires_activites`
--
ALTER TABLE `horaires_activites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_horaires_activites_activites` (`activite_id`);

--
-- Index pour la table `horaires_attractions`
--
ALTER TABLE `horaires_attractions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_horaires_attractions_attractions` (`attraction_id`);

--
-- Index pour la table `horaires_restaurants`
--
ALTER TABLE `horaires_restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_horaires_restaurants_restaurations` (`restauration_id`);

--
-- Index pour la table `langues`
--
ALTER TABLE `langues`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `offres`
--
ALTER TABLE `offres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_offres_categories` (`categorie_id`),
  ADD KEY `fk_offres_adresses` (`adresse_id`),
  ADD KEY `fk_offres_comptes_pro` (`pro_id`);

--
-- Index pour la table `offres_tags`
--
ALTER TABLE `offres_tags`
  ADD PRIMARY KEY (`offre_id`,`tag_id`),
  ADD KEY `fk_offres_tags_tags` (`tag_id`);

--
-- Index pour la table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `parcs_attractions`
--
ALTER TABLE `parcs_attractions`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `photos_avis`
--
ALTER TABLE `photos_avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_photos_avis_avis` (`avis_id`);

--
-- Index pour la table `photos_offres`
--
ALTER TABLE `photos_offres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_photos_offres_offres` (`offre_id`);

--
-- Index pour la table `prestations`
--
ALTER TABLE `prestations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `repas`
--
ALTER TABLE `repas`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reponses_pro`
--
ALTER TABLE `reponses_pro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reponses_pro_comptes_pro` (`pro_id`),
  ADD KEY `fk_reponses_pro_avis` (`avis_id`);

--
-- Index pour la table `restaurations`
--
ALTER TABLE `restaurations`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `restaurations_repas`
--
ALTER TABLE `restaurations_repas`
  ADD PRIMARY KEY (`restauration_id`,`repas_id`),
  ADD KEY `fk_restaurations_repas_repas` (`repas_id`);

--
-- Index pour la table `souscriptions`
--
ALTER TABLE `souscriptions`
  ADD PRIMARY KEY (`offre_id`,`option_id`),
  ADD KEY `fk_souscriptions_options` (`option_id`);

--
-- Index pour la table `spectacles`
--
ALTER TABLE `spectacles`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `statuts`
--
ALTER TABLE `statuts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_statuts_offres` (`offre_id`);

--
-- Index pour la table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `visites`
--
ALTER TABLE `visites`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `visites_langues`
--
ALTER TABLE `visites_langues`
  ADD PRIMARY KEY (`visite_id`,`langue_id`),
  ADD KEY `fk_visites_langues_langues` (`langue_id`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activites`
--
ALTER TABLE `activites`
  ADD CONSTRAINT `fk_activites_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `activites_prestations_incluses`
--
ALTER TABLE `activites_prestations_incluses`
  ADD CONSTRAINT `fk_activites_prestations_incluses_activites` FOREIGN KEY (`activite_id`) REFERENCES `activites` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_activites_prestations_incluses_prestations` FOREIGN KEY (`prestation_id`) REFERENCES `prestations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `activites_prestations_non_incluses`
--
ALTER TABLE `activites_prestations_non_incluses`
  ADD CONSTRAINT `fk_activites_prestations_non_incluses_activites` FOREIGN KEY (`activite_id`) REFERENCES `activites` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_activites_prestations_non_incluses_prestations` FOREIGN KEY (`prestation_id`) REFERENCES `prestations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `attractions`
--
ALTER TABLE `attractions`
  ADD CONSTRAINT `fk_attractions_parcs_attractions` FOREIGN KEY (`parc_attractions_id`) REFERENCES `parcs_attractions` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_comptes_membre` FOREIGN KEY (`membre_id`) REFERENCES `comptes_membre` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avis_offres` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `comptes_membre`
--
ALTER TABLE `comptes_membre`
  ADD CONSTRAINT `fk_comptes_membre_adresses` FOREIGN KEY (`adresse_id`) REFERENCES `adresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `comptes_pro`
--
ALTER TABLE `comptes_pro`
  ADD CONSTRAINT `fk_comptes_pro_adresses` FOREIGN KEY (`adresse_id`) REFERENCES `adresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `horaires_activites`
--
ALTER TABLE `horaires_activites`
  ADD CONSTRAINT `fk_horaires_activites_activites` FOREIGN KEY (`activite_id`) REFERENCES `activites` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `horaires_attractions`
--
ALTER TABLE `horaires_attractions`
  ADD CONSTRAINT `fk_horaires_attractions_attractions` FOREIGN KEY (`attraction_id`) REFERENCES `attractions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `horaires_restaurants`
--
ALTER TABLE `horaires_restaurants`
  ADD CONSTRAINT `fk_horaires_restaurants_restaurations` FOREIGN KEY (`restauration_id`) REFERENCES `restaurations` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `offres`
--
ALTER TABLE `offres`
  ADD CONSTRAINT `fk_offres_adresses` FOREIGN KEY (`adresse_id`) REFERENCES `adresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_offres_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_offres_comptes_pro` FOREIGN KEY (`pro_id`) REFERENCES `comptes_pro` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `offres_tags`
--
ALTER TABLE `offres_tags`
  ADD CONSTRAINT `fk_offres_tags_offres` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_offres_tags_tags` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `parcs_attractions`
--
ALTER TABLE `parcs_attractions`
  ADD CONSTRAINT `fk_parcs_attractions_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `photos_avis`
--
ALTER TABLE `photos_avis`
  ADD CONSTRAINT `fk_photos_avis_avis` FOREIGN KEY (`avis_id`) REFERENCES `avis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `photos_offres`
--
ALTER TABLE `photos_offres`
  ADD CONSTRAINT `fk_photos_offres_offres` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reponses_pro`
--
ALTER TABLE `reponses_pro`
  ADD CONSTRAINT `fk_reponses_pro_avis` FOREIGN KEY (`avis_id`) REFERENCES `avis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reponses_pro_comptes_pro` FOREIGN KEY (`pro_id`) REFERENCES `comptes_pro` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `restaurations`
--
ALTER TABLE `restaurations`
  ADD CONSTRAINT `fk_restaurations_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `restaurations_repas`
--
ALTER TABLE `restaurations_repas`
  ADD CONSTRAINT `fk_restaurations_repas_repas` FOREIGN KEY (`repas_id`) REFERENCES `repas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_restaurations_repas_restaurations` FOREIGN KEY (`restauration_id`) REFERENCES `restaurations` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `souscriptions`
--
ALTER TABLE `souscriptions`
  ADD CONSTRAINT `fk_souscriptions_offres` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_souscriptions_options` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `spectacles`
--
ALTER TABLE `spectacles`
  ADD CONSTRAINT `fk_spectacles_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `statuts`
--
ALTER TABLE `statuts`
  ADD CONSTRAINT `fk_statuts_offres` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `visites`
--
ALTER TABLE `visites`
  ADD CONSTRAINT `fk_visites_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `visites_langues`
--
ALTER TABLE `visites_langues`
  ADD CONSTRAINT `fk_visites_langues_langues` FOREIGN KEY (`langue_id`) REFERENCES `langues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_visites_langues_visites` FOREIGN KEY (`visite_id`) REFERENCES `visites` (`categorie_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
