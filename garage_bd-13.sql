-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 18 août 2025 à 14:54
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `garage_bd`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories_pieces`
--

CREATE TABLE `categories_pieces` (
  `id_categorie` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories_pieces`
--

INSERT INTO `categories_pieces` (`id_categorie`, `libelle`, `description`, `supprimer`) VALUES
(1, 'rs', 'rss', 'Oui'),
(2, 'rasera', 'rasera', 'Non'),
(3, 'rsr', 'est', 'Oui');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id_client` int(11) NOT NULL,
  `code_client` varchar(20) NOT NULL,
  `type_client` enum('Particulier','Entreprise') NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `raison_sociale` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `date_enregistrement` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` enum('Actif','Inactif') NOT NULL DEFAULT 'Actif',
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id_client`, `code_client`, `type_client`, `nom`, `prenom`, `raison_sociale`, `email`, `telephone`, `adresse`, `ville`, `pays`, `date_enregistrement`, `statut`, `supprimer`) VALUES
(1, 'CL-20250812-6612', 'Particulier', 'ss', 'ss', NULL, NULL, '44', NULL, NULL, NULL, '2025-08-12 09:28:19', 'Actif', 'Oui'),
(2, 'CL-20250812-2689', 'Particulier', 'Mossadegh', 'Mohamed', NULL, 'mohamad@gmail.com', '64342343', 'ouaga', 'bobo', NULL, '2025-08-12 09:50:36', 'Actif', 'Non'),
(3, 'CL-20250812-4575', 'Particulier', 'KONATE', 'Ladji', NULL, NULL, '76543432', NULL, 'bf', NULL, '2025-08-12 10:22:14', 'Actif', 'Oui'),
(4, 'CL-20250812-4273', 'Particulier', 'SIMPORE', 'Moustapha', 'non', 'simpo@gmail.com', '54326754', 'Tanghin', 'BF', 'ouaga', '2025-08-12 10:42:40', 'Actif', 'Non'),
(5, 'CL-20250812-7939', 'Particulier', 'SARE', 'SARA', NULL, 'sare@gmail.com', '05987654', NULL, 'Burkina faso', 'kaya', '2025-08-12 11:05:06', 'Actif', 'Non'),
(6, 'CL-20250813-9732', 'Particulier', 'ss', 'kk', NULL, NULL, '24', NULL, NULL, NULL, '2025-08-13 15:55:44', 'Actif', 'Oui');

-- --------------------------------------------------------

--
-- Structure de la table `employes`
--

CREATE TABLE `employes` (
  `id_employe` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `poste` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `salaire_base` decimal(10,2) DEFAULT NULL,
  `statut` enum('Actif','Suspendu','Archivé') NOT NULL DEFAULT 'Actif',
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `employes`
--

INSERT INTO `employes` (`id_employe`, `nom`, `prenom`, `poste`, `email`, `telephone`, `date_embauche`, `salaire_base`, `statut`, `supprimer`) VALUES
(1, 'sama', 'Massoud', '', '', '', NULL, NULL, 'Actif', 'Oui'),
(2, 'ss', 'ss', '', '', '', NULL, NULL, 'Actif', 'Oui'),
(3, 'ss', 's', '', '', '', NULL, NULL, 'Actif', 'Oui'),
(4, 'rino', 'de lor', 'mécano9', 'rini@gmail.com', '65464383', NULL, NULL, 'Actif', 'Oui'),
(5, 'Chris', 'john', '', '', '', NULL, NULL, 'Actif', 'Oui'),
(6, 'Sancho1', 'miguel', '', '', '', NULL, NULL, 'Actif', 'Oui'),
(7, 'TOUGMA', 'Wilfried', 'Gestionnaire', 'tougma@gmail.com', '56789076', '2025-08-10', NULL, 'Actif', 'Non'),
(8, 'KOKONBO', 'Bertrant', 'Technicien en pneumatiques et géométrie', NULL, NULL, NULL, 700000.00, 'Actif', 'Non'),
(9, 'Nanema', 'philomaine', 'Mécanicien poids lourds', 'nane@gmail.com', '56789876', '2024-08-14', 900000.00, 'Actif', 'Non'),
(10, 'sinare', 'josias', 'Mécanicien en véhicules hybrides/électriques', NULL, '5543', '2025-08-06', NULL, 'Actif', 'Non'),
(11, 'BADOLO', 'Moussa', 'Mécanicien diagnosticien', 'sa@gmail.com', '578976', '2025-08-01', 45000.00, 'Actif', 'Non'),
(12, 'ss', 'ss', 'Mécanicien en freinage et suspension', NULL, NULL, NULL, NULL, 'Actif', 'Oui'),
(13, 'dd', 'dd', 'Mécanicien en climatisation et chauffage', NULL, NULL, NULL, NULL, 'Actif', 'Non'),
(14, 'zzo', 'zzo', 'Mécanicien en freinage et suspension', NULL, NULL, NULL, NULL, 'Actif', 'Oui'),
(15, 'KABORE', 'Thierry', 'Admin', 'test@gmail.com', '76543453', NULL, NULL, 'Actif', 'Non');

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE `factures` (
  `id_facture` int(11) NOT NULL,
  `id_intervention` int(11) NOT NULL,
  `numero_facture` varchar(20) NOT NULL,
  `date_facture` datetime NOT NULL DEFAULT current_timestamp(),
  `montant_ht` decimal(10,2) DEFAULT NULL,
  `tva` decimal(5,2) DEFAULT NULL,
  `montant_ttc` decimal(10,2) DEFAULT NULL,
  `remise` decimal(10,2) DEFAULT NULL,
  `statut_paiement` enum('Non payé','Acompte','Payé') NOT NULL DEFAULT 'Non payé',
  `mode_paiement` enum('Espèces','Chèque','Carte','Mobile Money') DEFAULT NULL,
  `acompte` decimal(10,2) DEFAULT NULL,
  `reste_a_payer` decimal(10,2) DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id_facture`, `id_intervention`, `numero_facture`, `date_facture`, `montant_ht`, `tva`, `montant_ttc`, `remise`, `statut_paiement`, `mode_paiement`, `acompte`, `reste_a_payer`, `supprimer`) VALUES
(1, 2, 'FAC-2025-0001', '2025-08-12 00:00:00', 5000.00, 20.00, 5000.00, 1000.00, 'Payé', 'Espèces', 1000.00, 4000.00, 'Oui'),
(2, 2, 'FAC-2025-0002', '2025-08-06 00:00:00', 50000.00, 20.00, 59000.00, 1000.00, 'Payé', 'Chèque', 10000.00, 49000.00, 'Non');

-- --------------------------------------------------------

--
-- Structure de la table `historique_action`
--

CREATE TABLE `historique_action` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `adresse_ip` varchar(45) DEFAULT NULL,
  `date_heure_ajout` datetime NOT NULL DEFAULT current_timestamp(),
  `nom_action` text NOT NULL,
  `nom_table` varchar(50) DEFAULT NULL,
  `id_concerne` int(11) DEFAULT NULL,
  `ancienne_valeur` text DEFAULT NULL,
  `nouvelle_valeur` text DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historique_action`
--

INSERT INTO `historique_action` (`id`, `id_utilisateur`, `adresse_ip`, `date_heure_ajout`, `nom_action`, `nom_table`, `id_concerne`, `ancienne_valeur`, `nouvelle_valeur`, `supprimer`) VALUES
(1, NULL, '::1', '2025-08-13 14:05:25', 'Ajout employé', 'employes', 11, NULL, '{\"id_employe\":11,\"nom\":\"BADOLO\",\"prenom\":\"Moussa\",\"poste\":\"Mécanicien diagnosticien\",\"email\":\"sa@gmail.com\",\"telephone\":\"578976\",\"date_embauche\":\"2025-08-01\",\"salaire_base\":45000,\"statut\":\"Actif\"}', 'Non'),
(2, NULL, '::1', '2025-08-13 14:21:07', 'Ajout employé', 'employes', 12, NULL, '{\"id_employe\":12,\"nom\":\"ss\",\"prenom\":\"ss\",\"poste\":\"Mécanicien en freinage et suspension\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', 'Non'),
(3, NULL, '::1', '2025-08-13 14:46:02', 'Ajout employé', 'employes', 13, NULL, '{\"id_employe\":13,\"nom\":\"dd\",\"prenom\":\"dd\",\"poste\":\"Mécanicien en climatisation et chauffage\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', 'Non'),
(4, NULL, '::1', '2025-08-13 14:56:19', 'Ajout employé', 'employes', 14, NULL, '{\"id_employe\":14,\"nom\":\"zz\",\"prenom\":\"zz\",\"poste\":\"Mécanicien en freinage et suspension\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', 'Non'),
(5, NULL, '::1', '2025-08-13 15:27:19', 'Modification employé', 'employes', 14, '{\"id_employe\":14,\"nom\":\"zz\",\"prenom\":\"zz\",\"poste\":\"Mécanicien en freinage et suspension\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', '{\"id_employe\":14,\"nom\":\"zzo\",\"prenom\":\"zzo\",\"poste\":\"Mécanicien en freinage et suspension\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', 'Non'),
(6, NULL, '::1', '2025-08-13 15:30:02', 'Suppression employé', 'employes', 14, '{\"id_employe\":14,\"nom\":\"zzo\",\"prenom\":\"zzo\",\"poste\":\"Mécanicien en freinage et suspension\",\"email\":null,\"telephone\":null,\"date_embauche\":null,\"salaire_base\":null,\"statut\":\"Actif\"}', '{\"id_employe\":14,\"supprimer\":\"Oui\"}', 'Non'),
(7, NULL, '::1', '2025-08-13 15:34:25', 'Ajout catégorie pièce', 'categories_pieces', 3, NULL, '{\"id_categorie\":3,\"libelle\":\"rs\",\"description\":\"es\"}', 'Non'),
(8, NULL, '::1', '2025-08-13 15:37:05', 'Modification catégorie pièce', 'categories_pieces', 3, '{\"id_categorie\":3,\"libelle\":\"rs\",\"description\":\"es\"}', '{\"id_categorie\":3,\"libelle\":\"rsr\",\"description\":\"est\"}', 'Non'),
(9, NULL, '::1', '2025-08-13 15:38:51', 'Suppression catégorie pièce', 'categories_pieces', 3, '{\"id_categorie\":3,\"libelle\":\"rsr\",\"description\":\"est\"}', '{\"id_categorie\":3,\"supprimer\":\"Oui\"}', 'Non'),
(10, NULL, '::1', '2025-08-13 15:40:47', 'Ajout pièce', 'pieces', 5, NULL, '{\"id_piece\":5,\"reference\":\"cc\",\"designation\":\"cc\",\"prix_achat\":null,\"prix_vente\":null,\"quantite_stock\":null,\"seuil_minimal\":null,\"fournisseur\":null,\"id_categorie\":2}', 'Non'),
(11, NULL, '::1', '2025-08-13 15:43:57', 'Modification pièce', 'pieces', 5, '{\"id_piece\":5,\"reference\":\"cc\",\"designation\":\"cc\",\"prix_achat\":null,\"prix_vente\":null,\"quantite_stock\":null,\"seuil_minimal\":null,\"fournisseur\":null,\"id_categorie\":2}', '{\"id_piece\":5,\"reference\":\"cc\",\"designation\":\"cc\",\"prix_achat\":null,\"prix_vente\":null,\"quantite_stock\":null,\"seuil_minimal\":null,\"fournisseur\":\"rs\",\"id_categorie\":2}', 'Non'),
(12, NULL, '::1', '2025-08-13 15:46:23', 'Suppression pièce', 'pieces', 5, '{\"id_piece\":5,\"reference\":\"cc\",\"designation\":\"cc\",\"prix_achat\":null,\"prix_vente\":null,\"quantite_stock\":null,\"seuil_minimal\":null,\"fournisseur\":\"rs\",\"id_categorie\":2}', '{\"id_piece\":5,\"supprimer\":\"Oui\"}', 'Non'),
(13, NULL, '::1', '2025-08-13 15:55:03', 'Modification client', 'clients', 5, '{\"id_client\":5,\"code_client\":\"CL-20250812-7939\",\"nom\":\"SARE\",\"prenom\":\"SARAH\",\"telephone\":\"05987654\",\"email\":\"sare@gmail.com\",\"type_client\":\"Particulier\",\"raison_sociale\":null,\"adresse\":null,\"ville\":\"Burkina faso\",\"pays\":\"kaya\",\"statut\":\"Actif\"}', '{\"id_client\":5,\"nom\":\"SARE\",\"prenom\":\"SARA\",\"telephone\":\"05987654\",\"email\":\"sare@gmail.com\",\"type_client\":\"Particulier\",\"raison_sociale\":null,\"adresse\":null,\"ville\":\"Burkina faso\",\"pays\":\"kaya\",\"statut\":\"Actif\"}', 'Non'),
(14, NULL, '::1', '2025-08-13 15:55:44', 'Ajout client', 'clients', 6, NULL, '{\"id_client\":6,\"code_client\":\"CL-20250813-9732\",\"nom\":\"ss\",\"prenom\":\"kk\",\"telephone\":\"24\",\"email\":null,\"type_client\":\"Particulier\",\"raison_sociale\":null,\"adresse\":null,\"ville\":null,\"pays\":null,\"statut\":\"Actif\"}', 'Non'),
(15, NULL, '::1', '2025-08-13 15:57:24', 'Suppression client', 'clients', 6, '{\"id_client\":6,\"code_client\":\"CL-20250813-9732\",\"nom\":\"ss\",\"prenom\":\"kk\",\"telephone\":\"24\",\"email\":null,\"type_client\":\"Particulier\",\"raison_sociale\":null,\"adresse\":null,\"ville\":null,\"pays\":null,\"statut\":\"Actif\"}', '{\"id_client\":6,\"supprimer\":\"Oui\"}', 'Non'),
(16, NULL, '::1', '2025-08-13 16:06:25', 'Modification intervention', 'interventions', 4, '{\"id_intervention\":4,\"id_vehicule\":2,\"id_employe\":7,\"type_intervention\":\"Révision\",\"date_debut\":\"2025-08-13 11:16:01\",\"date_fin\":null,\"kilometrage\":null,\"statut\":\"En attente\",\"priorite\":\"Normale\",\"temps_estime\":null,\"temps_reel\":null,\"main_oeuvre_ht\":null,\"description\":null,\"remarques\":null}', '{\"id_intervention\":4,\"id_vehicule\":2,\"id_employe\":7,\"type_intervention\":\"Vidange\",\"date_debut\":\"2025-08-13 11:16:00\",\"date_fin\":null,\"kilometrage\":null,\"statut\":\"En attente\",\"priorite\":\"Normale\",\"temps_estime\":null,\"temps_reel\":null,\"main_oeuvre_ht\":null,\"description\":null,\"remarques\":null}', 'Non'),
(17, NULL, '::1', '2025-08-13 16:24:33', 'Modification facture', 'factures', 2, '{\"id_facture\":2,\"numero_facture\":\"FAC-2025-0002\",\"id_intervention\":2,\"date_facture\":\"2025-08-06\",\"montant_ht\":50000,\"tva\":20,\"remise\":1000,\"montant_ttc\":59000,\"acompte\":10000,\"reste_a_payer\":49000,\"statut_paiement\":\"Non payé\",\"mode_paiement\":\"Chèque\"}', '{\"id_facture\":2,\"numero_facture\":\"FAC-2025-0002\",\"id_intervention\":2,\"date_facture\":\"2025-08-06\",\"montant_ht\":50000,\"tva\":20,\"remise\":1000,\"montant_ttc\":59000,\"acompte\":10000,\"reste_a_payer\":49000,\"statut_paiement\":\"Payé\",\"mode_paiement\":\"Chèque\"}', 'Non'),
(18, NULL, '::1', '2025-08-13 16:35:30', 'Modification mouvement stock', 'mouvements_stock', 3, '{\"id_mouvement\":3,\"id_piece\":2,\"type_mouvement\":\"Ajustement\",\"quantite\":4,\"date_mouvement\":\"2025-08-07 17:26:00\",\"motif\":\"r\"}', '{\"id_mouvement\":3,\"id_piece\":2,\"type_mouvement\":\"Ajustement\",\"quantite\":4,\"date_mouvement\":\"2025-08-07 17:26:00\",\"motif\":\"rrr\"}', 'Non'),
(19, NULL, '::1', '2025-08-13 16:37:47', 'Suppression mouvement stock', 'mouvements_stock', 3, '{\"id_mouvement\":3,\"id_piece\":2,\"type_mouvement\":\"Ajustement\",\"quantite\":4,\"date_mouvement\":\"2025-08-07 17:26:00\",\"motif\":\"rrr\",\"stock_avant\":33,\"stock_apres\":29}', '{\"id_mouvement\":3,\"supprimer\":\"Oui\"}', 'Non'),
(20, NULL, '::1', '2025-08-13 16:43:09', 'Modification intervention_piece', 'intervention_pieces', 6, '{\"id_intervention\":2,\"id_piece\":2,\"quantite\":1,\"prix_unitaire\":700000,\"date_ajout\":\"2025-08-13 12:17:30\"}', '{\"id\":6,\"id_intervention\":2,\"id_piece\":2,\"quantite\":10,\"prix_unitaire\":700000,\"date_modification\":\"2025-08-13 18:43:09\"}', 'Non'),
(21, NULL, '::1', '2025-08-13 16:46:03', 'Suppression intervention_piece', 'intervention_pieces', 6, '{\"id\":6,\"id_intervention\":2,\"id_piece\":2,\"quantite\":10,\"prix_unitaire\":700000,\"date_ajout\":\"2025-08-13 12:17:30\"}', '{\"id\":6,\"supprimer\":\"Oui\"}', 'Non'),
(22, NULL, '::1', '2025-08-13 17:27:05', 'Modification profil', 'utilisateurs', 10, '{\"id_utilisateur\":10,\"nom_utilisateur\":\"test1\",\"role\":\"Admin\",\"dernier_acces\":\"2025-08-13 17:17:44\",\"actif\":1}', '{\"id_utilisateur\":10,\"nom_utilisateur\":\"test1\",\"role\":\"Admin\"}', 'Non'),
(23, 12, '::1', '2025-08-14 15:29:50', 'Modification intervention', 'interventions', 2, '{\"id_intervention\":2,\"id_vehicule\":2,\"id_employe\":10,\"type_intervention\":\"Diagnostic\",\"date_debut\":\"2025-08-09 16:15:00\",\"date_fin\":\"2025-08-16 16:15:00\",\"kilometrage\":120000,\"statut\":\"Terminé\",\"priorite\":\"Normale\",\"temps_estime\":9,\"temps_reel\":6,\"main_oeuvre_ht\":150000,\"description\":\"ras\",\"remarques\":null}', '{\"id_intervention\":2,\"id_vehicule\":2,\"id_employe\":10,\"type_intervention\":\"Diagnostic\",\"date_debut\":\"2025-08-09 16:15:00\",\"date_fin\":\"2025-08-16 16:15:00\",\"kilometrage\":120000,\"statut\":\"En cours\",\"priorite\":\"Normale\",\"temps_estime\":9,\"temps_reel\":6,\"main_oeuvre_ht\":150000,\"description\":\"ras\",\"remarques\":null}', 'Non'),
(24, 12, NULL, '2025-08-14 16:02:46', 'Déconnexion', NULL, NULL, NULL, NULL, 'Non'),
(25, 12, NULL, '2025-08-14 16:05:18', 'Déconnexion', NULL, NULL, NULL, NULL, 'Non'),
(26, 12, '::1', '2025-08-14 17:18:55', 'Modification catégorie pièce', 'categories_pieces', 2, '{\"id_categorie\":2,\"libelle\":\"rasera\",\"description\":\"rasera\"}', '{\"id_categorie\":2,\"libelle\":\"rasera\",\"description\":\"rasera\"}', 'Non'),
(27, 12, NULL, '2025-08-15 23:26:02', 'Déconnexion', NULL, NULL, NULL, NULL, 'Non'),
(28, 12, NULL, '2025-08-18 12:46:23', 'Déconnexion', NULL, NULL, NULL, NULL, 'Non');

-- --------------------------------------------------------

--
-- Structure de la table `interventions`
--

CREATE TABLE `interventions` (
  `id_intervention` int(11) NOT NULL,
  `id_vehicule` int(11) NOT NULL,
  `id_employe` int(11) NOT NULL,
  `type_intervention` enum('Diagnostic','Réparation','Révision','Vidange') NOT NULL,
  `date_debut` datetime NOT NULL DEFAULT current_timestamp(),
  `date_fin` datetime DEFAULT NULL,
  `kilometrage` int(11) DEFAULT NULL,
  `statut` enum('En attente','En cours','Terminé','Livré') NOT NULL DEFAULT 'En attente',
  `description` text DEFAULT NULL,
  `priorite` enum('Faible','Normale','Haute') NOT NULL DEFAULT 'Normale',
  `temps_estime` float DEFAULT NULL,
  `temps_reel` float DEFAULT NULL,
  `main_oeuvre_ht` decimal(10,2) DEFAULT NULL,
  `remarques` text DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `interventions`
--

INSERT INTO `interventions` (`id_intervention`, `id_vehicule`, `id_employe`, `type_intervention`, `date_debut`, `date_fin`, `kilometrage`, `statut`, `description`, `priorite`, `temps_estime`, `temps_reel`, `main_oeuvre_ht`, `remarques`, `supprimer`) VALUES
(1, 2, 8, 'Diagnostic', '2025-08-12 16:03:00', NULL, NULL, 'En attente', NULL, 'Normale', NULL, NULL, NULL, NULL, 'Oui'),
(2, 2, 10, 'Diagnostic', '2025-08-09 16:15:00', '2025-08-16 16:15:00', 120000, 'En cours', 'ras', 'Normale', 9, 6, 150000.00, NULL, 'Non'),
(3, 2, 9, 'Réparation', '2025-08-12 23:15:26', NULL, NULL, 'En attente', NULL, 'Normale', NULL, NULL, NULL, NULL, 'Non'),
(4, 2, 7, 'Vidange', '2025-08-13 11:16:00', NULL, NULL, 'En attente', NULL, 'Normale', NULL, NULL, NULL, NULL, 'Non');

-- --------------------------------------------------------

--
-- Structure de la table `intervention_pieces`
--

CREATE TABLE `intervention_pieces` (
  `id` int(11) NOT NULL,
  `id_intervention` int(11) NOT NULL,
  `id_piece` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp(),
  `supprimer` enum('Non','Oui') DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `intervention_pieces`
--

INSERT INTO `intervention_pieces` (`id`, `id_intervention`, `id_piece`, `quantite`, `prix_unitaire`, `date_ajout`, `supprimer`) VALUES
(1, 3, 2, 1, 700000.00, '2025-08-13 10:48:32', NULL),
(2, 2, 2, 3, 700000.00, '2025-08-13 10:56:30', 'Oui'),
(3, 2, 2, 4, 50000.00, '2025-08-13 11:01:08', 'Oui'),
(4, 2, 2, 1, 700000.00, '2025-08-13 11:04:36', 'Oui'),
(5, 3, 2, 9, 7.00, '2025-08-13 11:18:31', 'Oui'),
(6, 2, 2, 10, 700000.00, '2025-08-13 12:17:30', 'Oui');

-- --------------------------------------------------------

--
-- Structure de la table `mouvements_stock`
--

CREATE TABLE `mouvements_stock` (
  `id_mouvement` int(11) NOT NULL,
  `id_piece` int(11) NOT NULL,
  `type_mouvement` enum('Entrée','Sortie','Ajustement') NOT NULL,
  `quantite` int(11) NOT NULL,
  `date_mouvement` datetime NOT NULL DEFAULT current_timestamp(),
  `motif` varchar(255) DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `mouvements_stock`
--

INSERT INTO `mouvements_stock` (`id_mouvement`, `id_piece`, `type_mouvement`, `quantite`, `date_mouvement`, `motif`, `supprimer`) VALUES
(1, 2, 'Entrée', 63, '2025-08-12 17:04:00', 'ras', 'Oui'),
(2, 2, 'Sortie', 5, '2025-08-06 17:13:00', 'ras', 'Non'),
(3, 2, 'Ajustement', 4, '2025-08-07 17:26:00', 'rrr', 'Oui');

-- --------------------------------------------------------

--
-- Structure de la table `pieces`
--

CREATE TABLE `pieces` (
  `id_piece` int(11) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `prix_achat` decimal(10,2) DEFAULT NULL,
  `prix_vente` decimal(10,2) DEFAULT NULL,
  `quantite_stock` int(11) DEFAULT NULL,
  `seuil_minimal` int(11) DEFAULT NULL,
  `fournisseur` varchar(100) DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp(),
  `id_categorie` int(11) DEFAULT NULL,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pieces`
--

INSERT INTO `pieces` (`id_piece`, `reference`, `designation`, `prix_achat`, `prix_vente`, `quantite_stock`, `seuil_minimal`, `fournisseur`, `date_ajout`, `id_categorie`, `supprimer`) VALUES
(1, 'rs', 'rs', NULL, NULL, NULL, NULL, NULL, '2025-08-11 17:04:05', 2, 'Oui'),
(2, 'opr', 'opr', 6000.00, 700000.00, 29, 6, 'ras', '2025-08-11 20:33:55', 2, 'Non'),
(3, 'e', 'e', NULL, NULL, NULL, NULL, NULL, '2025-08-11 20:43:20', NULL, 'Oui'),
(4, 'qq3', 'qq', NULL, NULL, NULL, NULL, NULL, '2025-08-11 20:49:26', 2, 'Oui'),
(5, 'cc', 'cc', NULL, NULL, NULL, NULL, 'rs', '2025-08-13 15:40:47', 2, 'Oui');

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

CREATE TABLE `rendez_vous` (
  `id_rdv` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_vehicule` int(11) NOT NULL,
  `id_employe` int(11) NOT NULL,
  `date_heure` datetime NOT NULL,
  `objet` text DEFAULT NULL,
  `statut` enum('Confirmé','Annulé','Réalisé') NOT NULL DEFAULT 'Confirmé',
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `id_employe` int(11) DEFAULT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `mot_de_passe` text NOT NULL,
  `role` enum('Admin','Mécanicien','Gestionnaire','Caissier') NOT NULL DEFAULT 'Mécanicien',
  `dernier_acces` datetime DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `id_employe`, `nom_utilisateur`, `mot_de_passe`, `role`, `dernier_acces`, `actif`, `supprimer`) VALUES
(12, 15, 'test', '$2y$10$P6mpXuKKzHbdDluxBm/jDO9nu0fRDzSjvmGdwDTMQzNW0Pt4gwvES', 'Admin', '2025-08-18 12:47:08', 1, 'Non');

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `id_vehicule` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `immatriculation` varchar(20) NOT NULL,
  `marque` varchar(50) DEFAULT NULL,
  `modele` varchar(50) DEFAULT NULL,
  `type_moteur` varchar(30) DEFAULT NULL,
  `annee` int(11) DEFAULT NULL,
  `kilometrage` int(11) DEFAULT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `categorie` varchar(30) DEFAULT NULL,
  `vin` varchar(30) DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp(),
  `date_immatriculation` date DEFAULT NULL,
  `date_derniere_entretien` datetime DEFAULT NULL,
  `kilometrage_derniere_entretien` int(11) DEFAULT NULL,
  `date_prochain_entretien` datetime DEFAULT NULL,
  `type_assurance` varchar(50) DEFAULT NULL,
  `numero_assurance` varchar(50) DEFAULT NULL,
  `date_expiration_assurance` date DEFAULT NULL,
  `capacite_moteur` decimal(4,2) DEFAULT NULL,
  `puissance_cv` int(11) DEFAULT NULL,
  `transmission` enum('Manuelle','Automatique','Séquentielle') NOT NULL DEFAULT 'Manuelle',
  `conso_urbaine` decimal(5,2) DEFAULT NULL,
  `conso_extra_urbaine` decimal(5,2) DEFAULT NULL,
  `emission_co2` int(11) DEFAULT NULL,
  `garantie_fin` date DEFAULT NULL,
  `nbr_portes` tinyint(4) DEFAULT NULL,
  `couleur_interieur` varchar(50) DEFAULT NULL,
  `statut_vehicule` enum('En service','En réparation','Hors service') NOT NULL DEFAULT 'En service',
  `supprimer` enum('Non','Oui') NOT NULL DEFAULT 'Non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`id_vehicule`, `id_client`, `immatriculation`, `marque`, `modele`, `type_moteur`, `annee`, `kilometrage`, `couleur`, `categorie`, `vin`, `date_ajout`, `date_immatriculation`, `date_derniere_entretien`, `kilometrage_derniere_entretien`, `date_prochain_entretien`, `type_assurance`, `numero_assurance`, `date_expiration_assurance`, `capacite_moteur`, `puissance_cv`, `transmission`, `conso_urbaine`, `conso_extra_urbaine`, `emission_co2`, `garantie_fin`, `nbr_portes`, `couleur_interieur`, `statut_vehicule`, `supprimer`) VALUES
(1, 2, '1234-AB-01', 'toyota', 'Corolla', 'Diesel', 2020, 85000, 'Blanc', 'SUV', '001', '2025-08-12 12:20:20', '2025-08-03', '2025-08-09 12:19:00', 8000, '2025-08-23 12:20:00', 'Tous risque', 'ras', '2025-08-12', 1.50, 110, 'Automatique', 7.50, 5.20, 120, '2025-08-12', 4, 'Noir', 'Hors service', 'Oui'),
(2, 5, '1435-ca', 'Toyota', 'Corolla', 'Essence', 2025, 80000, 'Vert', 'Berline', '0012', '2025-08-12 12:51:17', '2025-08-03', '2025-08-12 12:50:00', 70000, '2025-08-09 12:51:00', 'Tous risque', 'Contrat', '2024-06-07', 1.87, 120, 'Séquentielle', 8.60, 7.50, 220, '2025-08-12', 6, 'vert', 'En réparation', 'Non');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories_pieces`
--
ALTER TABLE `categories_pieces`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id_client`);

--
-- Index pour la table `employes`
--
ALTER TABLE `employes`
  ADD PRIMARY KEY (`id_employe`);

--
-- Index pour la table `factures`
--
ALTER TABLE `factures`
  ADD PRIMARY KEY (`id_facture`),
  ADD KEY `fk_fac_int` (`id_intervention`);

--
-- Index pour la table `historique_action`
--
ALTER TABLE `historique_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hist_user` (`id_utilisateur`);

--
-- Index pour la table `interventions`
--
ALTER TABLE `interventions`
  ADD PRIMARY KEY (`id_intervention`),
  ADD KEY `fk_int_veh` (`id_vehicule`),
  ADD KEY `fk_int_emp` (`id_employe`);

--
-- Index pour la table `intervention_pieces`
--
ALTER TABLE `intervention_pieces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intervention` (`id_intervention`),
  ADD KEY `idx_piece` (`id_piece`);

--
-- Index pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  ADD PRIMARY KEY (`id_mouvement`),
  ADD KEY `fk_mov_piece` (`id_piece`);

--
-- Index pour la table `pieces`
--
ALTER TABLE `pieces`
  ADD PRIMARY KEY (`id_piece`),
  ADD KEY `fk_piece_cat` (`id_categorie`);

--
-- Index pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `fk_rdv_client` (`id_client`),
  ADD KEY `fk_rdv_veh` (`id_vehicule`),
  ADD KEY `fk_rdv_emp` (`id_employe`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD KEY `fk_util_emp` (`id_employe`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`id_vehicule`),
  ADD KEY `fk_veh_client` (`id_client`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories_pieces`
--
ALTER TABLE `categories_pieces`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `employes`
--
ALTER TABLE `employes`
  MODIFY `id_employe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `historique_action`
--
ALTER TABLE `historique_action`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `interventions`
--
ALTER TABLE `interventions`
  MODIFY `id_intervention` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `intervention_pieces`
--
ALTER TABLE `intervention_pieces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  MODIFY `id_mouvement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `pieces`
--
ALTER TABLE `pieces`
  MODIFY `id_piece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `id_vehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `fk_fac_int` FOREIGN KEY (`id_intervention`) REFERENCES `interventions` (`id_intervention`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `historique_action`
--
ALTER TABLE `historique_action`
  ADD CONSTRAINT `fk_hist_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `interventions`
--
ALTER TABLE `interventions`
  ADD CONSTRAINT `fk_int_emp` FOREIGN KEY (`id_employe`) REFERENCES `employes` (`id_employe`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_int_veh` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `intervention_pieces`
--
ALTER TABLE `intervention_pieces`
  ADD CONSTRAINT `fk_intervention_pieces_intervention` FOREIGN KEY (`id_intervention`) REFERENCES `interventions` (`id_intervention`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_intervention_pieces_piece` FOREIGN KEY (`id_piece`) REFERENCES `pieces` (`id_piece`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  ADD CONSTRAINT `fk_mov_piece` FOREIGN KEY (`id_piece`) REFERENCES `pieces` (`id_piece`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `pieces`
--
ALTER TABLE `pieces`
  ADD CONSTRAINT `fk_piece_cat` FOREIGN KEY (`id_categorie`) REFERENCES `categories_pieces` (`id_categorie`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `fk_rdv_client` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rdv_emp` FOREIGN KEY (`id_employe`) REFERENCES `employes` (`id_employe`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rdv_veh` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_util_emp` FOREIGN KEY (`id_employe`) REFERENCES `employes` (`id_employe`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `fk_veh_client` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
