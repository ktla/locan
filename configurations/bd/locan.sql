-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 04 Novembre 2013 à 11:07
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `locan`
--

-- --------------------------------------------------------

--
-- Structure de la table `absence`
--

CREATE TABLE IF NOT EXISTS `absence` (
  `IDABS` int(11) NOT NULL AUTO_INCREMENT,
  `IDCLASSE` varchar(15) DEFAULT NULL,
  `MATEL` int(11) NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `IDTRIMESTRE` int(11) NOT NULL,
  `IDSEQUENCE` int(11) NOT NULL,
  `NBHEURE` int(11) NOT NULL,
  `JOUR` datetime DEFAULT NULL,
  `JUSTIFY` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IDABS`),
  KEY `fk_idclasse` (`IDCLASSE`),
  KEY `fk_matel` (`MATEL`),
  KEY `NBHEURE` (`NBHEURE`),
  KEY `ANNEEACADEMIUE` (`PERIODE`,`IDTRIMESTRE`,`IDSEQUENCE`),
  KEY `fk_absence_trimestre` (`IDTRIMESTRE`),
  KEY `fk_absence_sequence` (`IDSEQUENCE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `ancien_etablissement`
--

CREATE TABLE IF NOT EXISTS `ancien_etablissement` (
  `IDETS` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDETS`),
  UNIQUE KEY `LIBELLE` (`LIBELLE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `annee_academique`
--

CREATE TABLE IF NOT EXISTS `annee_academique` (
  `ANNEEACADEMIQUE` varchar(15) NOT NULL,
  `DATEDEBUT` date NOT NULL,
  `DATEFIN` date NOT NULL,
  `DECOUPAGE` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ANNEEACADEMIQUE`),
  KEY `fk_annee_academique_decoupage` (`DECOUPAGE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `annee_academique`
--

INSERT INTO `annee_academique` (`ANNEEACADEMIQUE`, `DATEDEBUT`, `DATEFIN`, `DECOUPAGE`) VALUES
('2013-2014', '2013-09-02', '2014-06-25', 1);

-- --------------------------------------------------------

--
-- Structure de la table `classe`
--

CREATE TABLE IF NOT EXISTS `classe` (
  `IDCLASSE` varchar(15) NOT NULL,
  `LIBELLE` varchar(100) NOT NULL,
  `NIVEAU` int(11) NOT NULL COMMENT 'Niveau de la classe dans le cursus scolaire. ex 6eme = 1er nivo',
  PRIMARY KEY (`IDCLASSE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `classe_frais`
--

CREATE TABLE IF NOT EXISTS `classe_frais` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(20) NOT NULL,
  `IDCLASSE` varchar(25) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  `DATEDEBUT` date DEFAULT NULL,
  `DATEFIN` date DEFAULT NULL,
  `MONTANT` double NOT NULL,
  `TYPE` int(11) NOT NULL DEFAULT '0' COMMENT '0 = Frais officiels,  1 = Frais occasionel',
  `PERIODE` varchar(30) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDCLASSE` (`IDCLASSE`),
  KEY `PERIODE` (`PERIODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classe_parametre`
--

CREATE TABLE IF NOT EXISTS `classe_parametre` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDCLASSE` varchar(30) NOT NULL,
  `MONTANTINSCRIPTION` int(11) NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `TAILLEMAX` int(11) NOT NULL,
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Actif, 0 = Bloque',
  `PROFPRINCIPAL` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDCLASSE` (`IDCLASSE`),
  KEY `PERIODE` (`PERIODE`),
  KEY `MONTANTINSCRIPTION` (`MONTANTINSCRIPTION`),
  KEY `PROFPRINCIPAL` (`PROFPRINCIPAL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classe_reduction`
--

CREATE TABLE IF NOT EXISTS `classe_reduction` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(10) NOT NULL,
  `IDFRAIS` int(11) NOT NULL COMMENT 'ID du frais auquel la reduction est appliquee',
  `LIBELLE` varchar(255) NOT NULL,
  `MONTANT` double NOT NULL,
  `TYPE` varchar(15) NOT NULL COMMENT 'pourcentage ou valeur',
  PRIMARY KEY (`ID`),
  KEY `IDFRAIS` (`IDFRAIS`),
  KEY `IDFRAIS_2` (`IDFRAIS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE IF NOT EXISTS `compte` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDCOMPTE` varchar(50) NOT NULL DEFAULT 'CMPTCOMON00',
  `CORRESPONDANT` varchar(70) DEFAULT NULL,
  `DATECREATION` date DEFAULT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `AUTEUR` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `U_correspondant` (`CORRESPONDANT`),
  KEY `fk_acteur` (`AUTEUR`),
  KEY `PERIODE` (`PERIODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `decoupage`
--

CREATE TABLE IF NOT EXISTS `decoupage` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(30) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `LIBELLE` (`LIBELLE`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `decoupage`
--

INSERT INTO `decoupage` (`ID`, `LIBELLE`) VALUES
(2, 'Semestre'),
(1, 'Trimestre');

-- --------------------------------------------------------

--
-- Structure de la table `diplome`
--

CREATE TABLE IF NOT EXISTS `diplome` (
  `IDDIPLOME` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(255) NOT NULL,
  `FICHIER` varchar(255) NOT NULL,
  PRIMARY KEY (`IDDIPLOME`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `diplome`
--

INSERT INTO `diplome` (`IDDIPLOME`, `LIBELLE`, `FICHIER`) VALUES
(2, 'dipes', '');

-- --------------------------------------------------------

--
-- Structure de la table `droit`
--

CREATE TABLE IF NOT EXISTS `droit` (
  `IDDROIT` int(11) NOT NULL AUTO_INCREMENT,
  `IDMENU` int(11) NOT NULL,
  `LIBELLE` text NOT NULL,
  `CODEPAGE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDDROIT`),
  KEY `IDMENU` (`IDMENU`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=93 ;

--
-- Contenu de la table `droit`
--

INSERT INTO `droit` (`IDDROIT`, `IDMENU`, `LIBELLE`, `CODEPAGE`) VALUES
(1, 4, 'Inscription des eleves', 'INSCRIPTION_ELEVE'),
(2, 2, 'Ajout de notes dans tous les cours enseign&#233;s de l&#39;&#233;tablissement', 'ADD_ALL_NOTE'),
(3, 3, 'Ajout de classes:Montant inscription,\n TailleMax et appellation\n\n\n\n\n\n\n', 'ADD_CLASSE'),
(4, 1, 'Supprimer les El&egrave;ves', 'DEL_ELEVE'),
(5, 5, 'Supprimer des comptes', 'DEL_COMPTE'),
(6, 5, 'CrÃ©ation de nouveaux comptes', 'ADD_COMPTE'),
(7, 7, 'OpÃ©rations de dÃ©bit et de credit', 'OPERATION_COMPTE'),
(8, 8, 'Afficher liste des moratoires et action', 'SHOW_MORATOIRE'),
(9, 10, 'Afficher tous les professeurs', 'SHOW_PROFESSEUR'),
(10, 11, 'Impressions des états(pdf)', 'PRINT_ETAT'),
(11, 12, 'Information sur le contructeur et d&eacute;veloppeur', 'INFO_CONCEPTEUR'),
(12, 13, 'Ajout de nouveaux profiles MENUS', 'ADD_PROFILE'),
(13, 15, 'Fiche de votre Etablissement', 'FICHE_ETABLISSEMENT'),
(14, 18, 'Ajout des utilisateurs du systeme', 'ADD_USER'),
(15, 19, 'Cr&#233;ation de nouvelle pÃ©riodes acadÃ©miques', 'ADD_PERIODE'),
(16, 9, 'Ajout de mati&egraves;res enseign&eacute;es.', 'ADD_MATIERE'),
(17, 17, 'Solde', 'SHOW_SOLDE'),
(19, 23, 'G&eacute;n&eacute;rer les bulletins trimestriels', 'SHOW_BULLETIN'),
(20, 24, 'Statistique de la classe (Admis, Echou&eacute;s ...)', 'SHOW_STATISTIQUE'),
(21, 4, 'R&eacute;inscription des Anciens El&egrave;ves.', 'REINSCRIPTION_ELEVE'),
(22, 4, 'D&eacute;sincription des El&egrave;ves', 'DESINSCRIPTION_ELEVE'),
(23, 1, 'Modification des El&egrave;ves', 'EDIT_ELEVE'),
(24, 1, 'Impression de la liste de tous les El&egrave;ves de l Etablissement.', 'PRINT_ELEVE'),
(25, 14, 'Afficher la fiche de l''El&egrave;ve', 'FICHE_ELEVE'),
(26, 14, 'Imprimer la fiche des El&egrave;ves', 'PRINT_FICHE_ELEVE'),
(27, 1, 'Afficher tous les El&egrave;ves', 'SHOW_ELEVE'),
(28, 10, 'Imprimer la liste de tous les professeurs', 'PRINT_PROFESSEUR'),
(29, 10, 'Voir la fiche des professeurs', 'FICHE_PROFESSEUR'),
(30, 10, 'Modification des professeurs', 'EDIT_PROFESSEUR'),
(31, 10, 'Suppression des professeurs', 'DEL_PROFESSEUR'),
(32, 10, 'Ajouter un nouveau professeur', 'ADD_PROFESSEUR'),
(33, 2, 'Ajout des notes restreint &#224; ses cours enseign&#233;s', 'ADD_NOTE'),
(34, 21, 'Modification de notes dans tous les cours enseign&#233;s de l&#39;&#233;tablissement', 'EDIT_ALL_NOTE'),
(35, 21, 'Modification des notes restreint &#224; ses cours enseign&#233;s', 'EDIT_NOTE'),
(36, 20, 'Affecter les mati&#232;res aux professeurs.', 'ADD_ENSEIGNEMENT'),
(37, 20, 'Modifier les enseignements affect&#233; aux professeurs', 'EDIT_ENSEIGNEMENT'),
(38, 20, 'Supprimer un enseignement affect&#233; &#224; un professeur', 'DEL_ENSEIGNEMENT'),
(39, 20, 'Imprimer la liste des tous les enseignements', 'PRINT_ENSEIGNEMENT'),
(40, 8, 'Modification de moratoire', 'EDIT_MORATOIRE'),
(41, 8, 'Suppression de moratoire', 'DEL_MORATOIRE'),
(42, 9, 'Modification de mati&egraves;res enseign&eacute;es.', 'EDIT_MATIERE'),
(43, 9, 'Suppression de mati&egraves;res enseign&eacute;es.', 'DEL_MATIERE'),
(44, 5, 'Modification de compte', 'EDIT_COMPTE'),
(45, 15, 'Modification de l&#39;Etablissement', 'EDIT_ETABLISSEMENT'),
(46, 15, 'Imprimer la fiche de l&#039;Etablissement', 'PRINT_ETABLISSEMENT'),
(47, 19, 'Modification de p&#233;riodes acad&#233;miques', 'EDIT_PERIODE'),
(48, 19, 'Suppression de p&#233;riodes acad&#233;miques', 'DEL_PERIODE'),
(49, 18, 'Suppression des utilisateurs du syst&#232;me', 'DEL_USER'),
(50, 18, 'Modification des utilisateurs du syst&#232;me', 'EDIT_USER'),
(51, 13, 'Suppression de profiles de MENUS | DROIT', 'DEL_PROFILE'),
(52, 13, 'Mise &#224; jour des droits d&#039;un profile', 'EDIT_DROIT'),
(53, 3, 'Suppression de classe', 'DEL_CLASSE'),
(54, 3, 'Activer et d&#233;sactiver une classe', 'ACTIF_CLASSE'),
(55, 3, 'Modification d&#039;une classe : Montant inscription, tranches, TailleMax et appellation', 'EDIT_CLASSE'),
(56, 14, 'Afficher la fiche de chaque &#233;l&#232;ve', 'FICHE_ELEVE'),
(57, 14, 'Imprimer la fiche de chaque &#233;l&#232;ve', 'PRINT_FICHE_ELEVE'),
(58, 3, 'Afficher la liste des classes de l&#039;&#233;tablissement', 'SHOW_CLASSE'),
(59, 3, 'Afficher la liste des classes de l&#039;&#233;tablissement', 'PRINT_CLASSE'),
(60, 9, 'Afficher tous les mati&#232;res enseign&#233;es dans l&#039;tablissement', 'SHOW_MATIERE'),
(61, 9, 'Imprimer la liste de toutes les mati&#232;res enseign&#233;es.', 'PRINT_MATIERE'),
(62, 20, 'Afficher les enseignements de l&#039;tablissement ( Prof - Classe - Mati&#232;res)', 'SHOW_ENSEIGNEMENT'),
(63, 19, 'Afficher les p&#233;riode : Ann&#233;es acad&#233;miques, trimestres et s&#233;quence', 'SHOW_PERIODE'),
(64, 15, 'Cr&#233;er un nouvel Etablissement', 'ADD_ETABLISSEMENT'),
(65, 10, 'Imprimer la fiche du professeur', 'PRINT_FICHE_PROFESSEUR'),
(66, 25, 'Gestion de la conduite des El&#232;ves', 'SHOW_CONDUITE'),
(67, 26, 'Gestion de l&#039;emploi du temps de l&#039;Etablissement', 'SHOW_EMPLOI'),
(68, 27, 'Gestion des menus du syst&#232;me : Bloqu&#233; le menu, modifier le titre du menu, le lien du menu et son l&#039;ic&ocirc;ne', 'GESTION_MENU'),
(69, 13, 'Gestion des profiles, et &#233;dition des droits de profiles', 'GESTION_PROFILE'),
(70, 18, 'Afficher tous les utilisateurs du systeme', 'SHOW_USER'),
(71, 3, 'D&eacute;tails de la classe : Montant inscription, tranches, nb &eacute;l&egrave;ves, mati&egrave;res enseign&eacute;es', 'DETAILS_CLASSE'),
(72, 1, 'Ajout d&#039un nouvel &eacute;l&egrave;ves', 'ADD_ELEVE'),
(73, 5, 'Afficher tous les comptes de l&#039&#233;tablissement', 'SHOW_COMPTE'),
(74, 28, 'Frais de scolarité (frais officiels et frais occasionnels) et frais d''inscription de chaque classe', 'CAISSE_FRAIS'),
(75, 28, 'Afficher la fiche des frais d''une classe données', 'FRAIS_FICHE'),
(76, 29, 'Afficher les eleves inscrits a une classe', 'SHOW_INSCRIT'),
(77, 30, 'Gestion des operations en attente de comptabilisation', 'OPERATION_ATTENTE'),
(78, 31, 'Cursus scolaire des eleves depuis son entree pour toute periode confondus', 'CURSUS_ELEVE'),
(79, 8, 'Ajout ou creation de moratoire', 'ADD_MORATOIRE'),
(80, 32, 'Transmettre a une periode les valeurs de la periode precedente', 'INTER_PERIODE'),
(81, 30, 'Effectuer le payement de frais d un eleve', 'PAYER_FRAIS'),
(82, 30, 'Supprimer les frais affectes a l eleve lors de l inscription', 'DEL_FRAIS'),
(83, 7, 'Suppression une operation de debit ou credit', 'DEL_OPERATION'),
(84, 33, 'Afficher les religions', 'SHOW_RELIGION'),
(85, 33, 'Supprimer les religions', 'DEL_RELIGION'),
(86, 33, 'Modifier les religions', 'EDIT_RELIGION'),
(87, 33, 'Ajouter une nouvelle religion', 'ADD_RELIGION'),
(88, 34, 'Afficher les anciens etablissement', 'SHOW_ANCIENETABLISSEMENT'),
(89, 34, 'Modifier les anciens etablissements', 'EDIT_ANCIENETABLISSEMENT'),
(90, 34, 'Supprimer les anciens etablissement', 'DEL_ANCIENETABLISSEMENT'),
(91, 34, 'Ajouter un nouvel ancien etablissement', 'ADD_ANCIENETABLISSEMENT'),
(92, 7, 'Imprimer le journal', 'PRINT_JOURNAL');

-- --------------------------------------------------------

--
-- Structure de la table `eleve`
--

CREATE TABLE IF NOT EXISTS `eleve` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` varchar(25) NOT NULL DEFAULT 'ELCOMON00' COMMENT 'Matricule de l''eleve',
  `NOMEL` varchar(255) NOT NULL,
  `PRENOM` varchar(255) NOT NULL,
  `DATENAISS` date NOT NULL,
  `LIEUNAISS` varchar(255) NOT NULL,
  `TUTEUR` varchar(255) NOT NULL,
  `TEL` varchar(15) DEFAULT NULL,
  `ADRESSE` varchar(30) DEFAULT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `SEXE` varchar(15) NOT NULL,
  `IMAGE` varchar(255) DEFAULT NULL,
  `RELIGION` int(11) DEFAULT NULL,
  `ANCETBS` int(11) NOT NULL COMMENT 'Ancien etablissement',
  `DATEAJOUT` date NOT NULL COMMENT 'Date a laquelle l eleve a ete creer',
  `REDOUBLANT` int(11) NOT NULL COMMENT 'Lors de l''ajout, est cequ''il est redoublant? 0 = non et 1 = oui',
  `DATEARRIVEE` date NOT NULL,
  `PERIODE` varchar(15) NOT NULL DEFAULT '2011-2012',
  PRIMARY KEY (`ID`),
  KEY `PERIODE` (`PERIODE`),
  KEY `RELIGION` (`RELIGION`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `enseigner`
--

CREATE TABLE IF NOT EXISTS `enseigner` (
  `IDENSEIGNEMENT` int(11) NOT NULL AUTO_INCREMENT,
  `CODEMAT` varchar(8) NOT NULL,
  `CLASSE` varchar(8) NOT NULL,
  `PROF` int(11) NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `COEFF` int(11) NOT NULL,
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = enseigment actif et 0 = enseignement non actif',
  PRIMARY KEY (`IDENSEIGNEMENT`),
  KEY `CODEMAT` (`CODEMAT`),
  KEY `enseigner_ibfk_1` (`CLASSE`),
  KEY `enseigner_ibfk_6` (`PERIODE`),
  KEY `fk_professeur` (`PROF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `etablissement`
--

CREATE TABLE IF NOT EXISTS `etablissement` (
  `IDENTIFIANT` varchar(30) NOT NULL,
  `LIBELLE` text NOT NULL,
  `ADRESSE` varchar(255) DEFAULT NULL,
  `DATECREATION` date NOT NULL,
  `PRINCIPAL` varchar(255) NOT NULL,
  `LOGO` text,
  `EMAIL` varchar(50) DEFAULT NULL,
  `TEL` varchar(255) DEFAULT NULL,
  `MOBILE` varchar(50) DEFAULT NULL,
  `SITEWEB` varchar(100) DEFAULT NULL,
  `REGLEMENT` varchar(255) DEFAULT NULL,
  `AUTORISATION` varchar(255) DEFAULT NULL,
  `CPTEBANCAIRE` varchar(255) DEFAULT NULL,
  `HAUTEURLOGO` varchar(5) DEFAULT '50',
  `LARGEURLOGO` varchar(5) DEFAULT '50',
  PRIMARY KEY (`IDENTIFIANT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `etablissement`
--

INSERT INTO `etablissement` (`IDENTIFIANT`, `LIBELLE`, `ADRESSE`, `DATECREATION`, `PRINCIPAL`, `LOGO`, `EMAIL`, `TEL`, `MOBILE`, `SITEWEB`, `REGLEMENT`, `AUTORISATION`, `CPTEBANCAIRE`, `HAUTEURLOGO`, `LARGEURLOGO`) VALUES
('COMON', 'College catholique pere monti', 'BP: 331', '2013-10-31', 'Mr Kuba', '../configurations/logo/logo uac.jpg', 'lucarmels@yahoo.fr', '74 33 38 76', '78 12  22 12', 'www.ccpm.net', '', '', '00WZAA1223', '100', '100');

-- --------------------------------------------------------

--
-- Structure de la table `frais_apayer`
--

CREATE TABLE IF NOT EXISTS `frais_apayer` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` varchar(30) NOT NULL,
  `IDFRAIS` int(11) NOT NULL COMMENT 'Le montant du frais peut etre obtenu ici foreign key',
  `STATUT` int(11) NOT NULL DEFAULT '0' COMMENT '0 = non payer, 1=payer',
  `DATEOP` date NOT NULL COMMENT 'Date de l operation',
  `IDINSCRIPTION` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDINSCRIPTION` (`IDINSCRIPTION`),
  KEY `IDFRAIS` (`IDFRAIS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `header_menu`
--

CREATE TABLE IF NOT EXISTS `header_menu` (
  `IDHEADER` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDHEADER`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `header_menu`
--

INSERT INTO `header_menu` (`IDHEADER`, `LIBELLE`) VALUES
(1, 'El&egrave;ves'),
(2, 'Notes'),
(3, 'Classes'),
(4, 'Caisse'),
(5, 'Administrations'),
(6, 'Syst&egrave;mes');

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE IF NOT EXISTS `inscription` (
  `IDINSCRIPTION` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` int(11) NOT NULL,
  `IDCLASSE` varchar(30) NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `DATEINSCRIPTION` date NOT NULL,
  `MORATOIRE` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`IDINSCRIPTION`),
  KEY `IDCLASSE` (`IDCLASSE`),
  KEY `PERIODE` (`PERIODE`),
  KEY `MORATOIRE` (`MORATOIRE`),
  KEY `fk_incription_eleve` (`MATEL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `listedroit`
--

CREATE TABLE IF NOT EXISTS `listedroit` (
  `IDDROIT` int(11) NOT NULL,
  `PROFILE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDDROIT`,`PROFILE`),
  KEY `listedroit_ibfk_2` (`PROFILE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `listedroit`
--

INSERT INTO `listedroit` (`IDDROIT`, `PROFILE`) VALUES
(1, 'Administration'),
(2, 'Administration'),
(3, 'Administration'),
(4, 'Administration'),
(5, 'Administration'),
(6, 'Administration'),
(7, 'Administration'),
(8, 'Administration'),
(9, 'Administration'),
(10, 'Administration'),
(11, 'Administration'),
(12, 'Administration'),
(13, 'Administration'),
(14, 'Administration'),
(15, 'Administration'),
(16, 'Administration'),
(17, 'Administration'),
(19, 'Administration'),
(20, 'Administration'),
(21, 'Administration'),
(22, 'Administration'),
(23, 'Administration'),
(24, 'Administration'),
(25, 'Administration'),
(26, 'Administration'),
(27, 'Administration'),
(28, 'Administration'),
(29, 'Administration'),
(30, 'Administration'),
(31, 'Administration'),
(32, 'Administration'),
(33, 'Administration'),
(34, 'Administration'),
(35, 'Administration'),
(36, 'Administration'),
(37, 'Administration'),
(38, 'Administration'),
(39, 'Administration'),
(40, 'Administration'),
(41, 'Administration'),
(42, 'Administration'),
(43, 'Administration'),
(44, 'Administration'),
(45, 'Administration'),
(46, 'Administration'),
(47, 'Administration'),
(48, 'Administration'),
(49, 'Administration'),
(50, 'Administration'),
(51, 'Administration'),
(52, 'Administration'),
(53, 'Administration'),
(54, 'Administration'),
(55, 'Administration'),
(56, 'Administration'),
(57, 'Administration'),
(58, 'Administration'),
(59, 'Administration'),
(60, 'Administration'),
(61, 'Administration'),
(62, 'Administration'),
(63, 'Administration'),
(64, 'Administration'),
(65, 'Administration'),
(66, 'Administration'),
(67, 'Administration'),
(68, 'Administration'),
(69, 'Administration'),
(70, 'Administration'),
(71, 'Administration'),
(72, 'Administration'),
(73, 'Administration'),
(74, 'Administration'),
(75, 'Administration'),
(76, 'Administration'),
(77, 'Administration'),
(78, 'Administration'),
(79, 'Administration'),
(80, 'Administration'),
(81, 'Administration'),
(82, 'Administration'),
(83, 'Administration'),
(84, 'Administration'),
(85, 'Administration'),
(86, 'Administration'),
(87, 'Administration'),
(88, 'Administration'),
(89, 'Administration'),
(90, 'Administration'),
(91, 'Administration'),
(92, 'Administration');

-- --------------------------------------------------------

--
-- Structure de la table `locan`
--

CREATE TABLE IF NOT EXISTS `locan` (
  `ID` varchar(30) NOT NULL,
  `NOM` varchar(255) NOT NULL,
  `ADRESSE` varchar(30) NOT NULL,
  `TEL` varchar(255) NOT NULL,
  `EMAIL` varchar(30) NOT NULL,
  `SITEWEB` varchar(30) NOT NULL,
  `LOGO` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `matiere`
--

CREATE TABLE IF NOT EXISTS `matiere` (
  `CODEMAT` varchar(8) NOT NULL,
  `IDTYPE` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`CODEMAT`),
  KEY `fk_matiere_matiere_type` (`IDTYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `matiere_group`
--

CREATE TABLE IF NOT EXISTS `matiere_group` (
  `IDTYPE` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDTYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `IDMENU` int(11) NOT NULL AUTO_INCREMENT,
  `HEADER` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  `SIGNIFICATION` varchar(255) NOT NULL,
  `HREF` varchar(255) NOT NULL,
  `ICON` varchar(255) DEFAULT NULL,
  `ONCLICK` varchar(255) DEFAULT NULL,
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Actif, 0 = Bloque',
  `SOUSMENU` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IDMENU`),
  KEY `HEADER` (`HEADER`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

--
-- Contenu de la table `menu`
--

INSERT INTO `menu` (`IDMENU`, `HEADER`, `LIBELLE`, `SIGNIFICATION`, `HREF`, `ICON`, `ONCLICK`, `ACTIF`, `SOUSMENU`) VALUES
(1, 1, 'Edition', 'Ajouter, Supprimer, Lister, Modifer des El&egrave;ves', '../eleves/eleve.php', '../images/icons/edition.png', NULL, 1, 0),
(2, 2, 'Ajout des notes', 'Ajout de notes', '../notes/ajouter.php', '../images/icons/addnote.png', NULL, 1, 0),
(3, 3, 'Edition', 'Lister | Editer | Supprimer | Ajouter des classes', '../classes/classe.php', '../images/icons/lister_classe.png', NULL, 1, 0),
(4, 1, 'Inscription', 'Inscription des eleves', '../inscriptions/inscription.php', '../images/icons/inscription.png', NULL, 1, 1),
(5, 4, 'Edition', 'Lister, Supprimer et Ajout des comptes', '../comptes/compte.php', '../images/icons/lister_compte.png', NULL, 1, 0),
(7, 4, 'OpÃ©rations', 'OpÃ©rations de dÃ©bit et de credit', '../comptes/operation.php', '../images/icons/operation.png', NULL, 1, 0),
(8, 4, 'Moratoires', 'Gestion des moratoires', '../moratoires/moratoire.php', '../images/icons/moratoire.png', NULL, 1, 0),
(9, 5, 'Mati&egrave;res', 'Gestion des mati&egrave;res', '../matieres/matiere.php', '../images/icons/matiere.png', NULL, 1, 0),
(10, 5, 'Professeurs', 'Gestion des professeurs', '../professeurs/professeur.php', '../images/icons/professeur.png', NULL, 1, 0),
(11, 5, 'Imprimer Etats', 'Impressions des Ã©tats (pdf)', '../etats/etat.php', '../images/icons/etat.png', NULL, 1, 0),
(12, 6, 'Concepteurs', 'Information sur le contructeur et d&eacute;veloppeur', '../systemes/infos.php', '../images/icons/infos.png', NULL, 1, 0),
(13, 6, 'Droits et Profile', 'Gestion des profiles et menus', '../profiles/profile.php', '../images/icons/profile.png', NULL, 1, 0),
(14, 1, 'Fiche', 'Fiche et Impression de la fiche', '../eleves/fiche.php', '../images/icons/fiche.png', NULL, 1, 0),
(15, 5, 'Etablissement', 'A Propos de votre Etablissement', '../etablissements/fiche.php', '../images/icons/etablissement.png', NULL, 1, 0),
(16, 3, 'Imprimer', 'Impression des eleves de classes', '../classes/imprimer.php', '../images/icons/imprimer.gif', NULL, 1, 0),
(17, 4, 'Soldes', 'Soldes des eleves et de la classe', '../comptes/solde.php', '../images/icons/solde.png', NULL, 1, 0),
(18, 6, 'Utilisateurs', 'Gestion des utilisateurs du systeme', '../utilisateurs/utilisateur.php', '../images/icons/utilisateur.png', NULL, 1, 0),
(19, 5, 'PÃ©riode', 'Gestion des pÃ©riodes acadÃ©miques', '../periodes/periode.php', '../images/icons/periode.png', NULL, 1, 0),
(20, 3, 'Enseignements', 'Edition des mati&egraves;res enseign&eacute;es dans la classe', '../enseignements/enseignement.php', '../images/icons/enseignement.png', NULL, 1, 0),
(21, 2, 'Modification', 'Modification des notes', '../notes/modifier.php', '../images/icons/modifnote.png', NULL, 1, 0),
(23, 2, 'Bulletins', 'G&eacute;n&eacute;rer les bulletins', '../bulletins/bulletin.php', '../images/icons/bulletin.png', NULL, 1, 0),
(24, 2, 'Statistiques', 'Statistique de la classe (Admis, Echou&eacute;s ...)', '../classes/statistique.php', '../images/icons/statistique.png', NULL, 1, 0),
(25, 1, 'Conduite', 'Gestion de la conduite displinaire', '../conduites/conduite.php', '../images/icons/conduite.png', NULL, 1, 0),
(26, 3, 'Emploi du temps', 'Gestion de l&#039;emploi du temps', '../planifications/emploi.php', '../images/icons/emploi.png', NULL, 1, 0),
(27, 6, 'Menus', 'Gestion des menus du syst&#232;me', '../menus/menu.php', '../images/icons/menu.png', NULL, 1, 0),
(28, 4, 'Frais', 'Frais scolaires(inscription et scolarité) et frais occasionnels', '../caisses/frais.php', NULL, NULL, 1, 1),
(29, 3, 'El&egrave;ves inscrits', 'Liste des eleves inscrits a une classe', '../classes/inscrit.php', NULL, NULL, 1, 0),
(30, 4, 'Operation en attente', 'Gestion des operations en attente', '../caisses/attente.php', NULL, NULL, 1, 0),
(31, 1, 'Cursus scolaire', 'Afficher cursus des eleves pour toutes les periodes', '../eleves/cursus.php', NULL, NULL, 1, 0),
(32, 6, 'Inter-Periode', 'Transmettre a une periode les valeurs de la precedente', '../periodes/interperiode.php', NULL, NULL, 1, 0),
(33, 5, 'Gest. Religion', 'Gestion des religions', '../religions/religion.php', NULL, NULL, 1, 0),
(34, 5, 'Anc. Etbs.', 'Gestion des anciens etablissements', '../anciensetablissements/ancienetablissement.php', NULL, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `moratoire`
--

CREATE TABLE IF NOT EXISTS `moratoire` (
  `IDMORATOIRE` varchar(30) NOT NULL,
  `MATEL` int(11) NOT NULL,
  `MONTANT` double NOT NULL,
  `MONTANTUTILISE` double NOT NULL,
  `DATEDEBUT` date NOT NULL,
  `DATEFIN` date NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  PRIMARY KEY (`IDMORATOIRE`),
  KEY `PERIODE` (`PERIODE`),
  KEY `fk_moratoire_eleve` (`MATEL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `motif_disciplinaire`
--

CREATE TABLE IF NOT EXISTS `motif_disciplinaire` (
  `IDMOTIF` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` text NOT NULL,
  PRIMARY KEY (`IDMOTIF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

CREATE TABLE IF NOT EXISTS `note` (
  `IDNOTE` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` int(11) NOT NULL,
  `IDMATIERE` varchar(30) NOT NULL,
  `IDSEQUENCE` int(11) NOT NULL COMMENT 'Contient l''identifiant de la sequence si la periode est de type sequentielle, contient le trimestre si c''est trimestrielle, contient le semestre si c''est semestrielle',
  `NOTE` double NOT NULL,
  `BAREME` int(11) NOT NULL,
  `COEFFICIENT` int(11) NOT NULL,
  PRIMARY KEY (`IDNOTE`),
  KEY `MATEL` (`MATEL`),
  KEY `IDMATIERE` (`IDMATIERE`),
  KEY `BAREME` (`BAREME`,`COEFFICIENT`),
  KEY `fk_note_sequence` (`IDSEQUENCE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `operation`
--

CREATE TABLE IF NOT EXISTS `operation` (
  `IDOPERATION` int(11) NOT NULL AUTO_INCREMENT,
  `IDCOMPTE` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  `ACTION` double NOT NULL,
  `DATE` date NOT NULL,
  `PERIODE` varchar(15) NOT NULL,
  `AUTEUR` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`IDOPERATION`),
  KEY `fk_operation_compte` (`IDCOMPTE`),
  KEY `fk_operation_annee_academique` (`PERIODE`),
  KEY `fk_operation_users` (`AUTEUR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `operation_attente`
--

CREATE TABLE IF NOT EXISTS `operation_attente` (
  `CONCERNER` varchar(30) NOT NULL,
  `PERIODE` varchar(30) NOT NULL,
  PRIMARY KEY (`CONCERNER`,`PERIODE`),
  KEY `PERIODE` (`PERIODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `professeur`
--

CREATE TABLE IF NOT EXISTS `professeur` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDPROF` varchar(30) NOT NULL DEFAULT 'PROFCOMON00',
  `NOMPROF` varchar(255) NOT NULL,
  `PRENOM` varchar(255) NOT NULL,
  `DATENAISS` date NOT NULL,
  `TEL` varchar(255) NOT NULL,
  `ADRESSE` varchar(150) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `DIPLOME` int(11) NOT NULL,
  `SEXE` varchar(15) NOT NULL DEFAULT 'Masculin',
  `RELIGION` int(11) NOT NULL,
  `PHOTO` varchar(255) NOT NULL,
  `CURRICULUM` varchar(255) NOT NULL,
  `DATEDEBUT` date NOT NULL COMMENT 'Permet de preciser l''anciennete du professeur',
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Actif et 0 = non actif',
  PRIMARY KEY (`ID`),
  KEY `RELIGION` (`RELIGION`),
  KEY `DIPLOME` (`DIPLOME`),
  KEY `DATEDEBUT` (`DATEDEBUT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `LIBELLE` varchar(30) NOT NULL,
  `DROIT` text NOT NULL,
  PRIMARY KEY (`LIBELLE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `profile`
--

INSERT INTO `profile` (`LIBELLE`, `DROIT`) VALUES
('Administration', 'a:24:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"4";i:3;s:1:"7";i:4;s:2:"10";i:5;s:2:"11";i:6;s:2:"12";i:7;s:2:"13";i:8;s:2:"14";i:9;s:2:"15";i:10;s:2:"16";i:11;s:2:"17";i:12;s:2:"18";i:13;s:2:"19";i:14;s:2:"20";i:15;s:2:"21";i:16;s:2:"22";i:17;s:2:"23";i:18;s:2:"24";i:19;s:2:"25";i:20;s:2:"27";i:21;s:2:"28";i:22;s:2:"29";i:23;s:2:"30";}');

-- --------------------------------------------------------

--
-- Structure de la table `reduction_obtenue`
--

CREATE TABLE IF NOT EXISTS `reduction_obtenue` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` int(11) NOT NULL,
  `IDREDUCTION` int(11) NOT NULL COMMENT 'Le montant du frais peut etre obtenu ici foreign key',
  `STATUT` int(11) NOT NULL DEFAULT '0' COMMENT '0 = non payer, 1=payer',
  `DATEOP` date NOT NULL COMMENT 'Date de l operation',
  `IDINSCRIPTION` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDINSCRIPTION` (`IDINSCRIPTION`),
  KEY `IDREDUCTION` (`IDREDUCTION`),
  KEY `fk_reduction_obtenue_eleve` (`MATEL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `religion`
--

CREATE TABLE IF NOT EXISTS `religion` (
  `IDRELIGION` int(11) NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(100) NOT NULL,
  PRIMARY KEY (`IDRELIGION`),
  UNIQUE KEY `LIBELLE_2` (`LIBELLE`),
  KEY `LIBELLE` (`LIBELLE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sequence`
--

CREATE TABLE IF NOT EXISTS `sequence` (
  `IDSEQUENCE` int(11) NOT NULL AUTO_INCREMENT,
  `IDTRIMESTRE` int(11) NOT NULL,
  `ORDRE` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDSEQUENCE`),
  KEY `fk_sequence_trimestre` (`IDTRIMESTRE`),
  KEY `ORDRE` (`ORDRE`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `sequence`
--

INSERT INTO `sequence` (`IDSEQUENCE`, `IDTRIMESTRE`, `ORDRE`, `LIBELLE`) VALUES
(1, 15, 1, 'Seq 1'),
(2, 15, 2, 'Seq 2');

-- --------------------------------------------------------

--
-- Structure de la table `solde`
--

CREATE TABLE IF NOT EXISTS `solde` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MATEL` int(11) NOT NULL,
  `MONTANT` int(11) NOT NULL,
  `DATEVAL` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `MATEL` (`MATEL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sous_menu`
--

CREATE TABLE IF NOT EXISTS `sous_menu` (
  `IDSOUSMENU` int(11) NOT NULL AUTO_INCREMENT,
  `IDMENU` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  `SIGNIFICATION` varchar(255) NOT NULL,
  `HREF` varchar(255) NOT NULL,
  `ICON` varchar(255) DEFAULT NULL,
  `ONCLICK` varchar(255) DEFAULT NULL,
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Actif, 0 = Bloque',
  PRIMARY KEY (`IDSOUSMENU`),
  KEY `IDMENU` (`IDMENU`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `sous_menu`
--

INSERT INTO `sous_menu` (`IDSOUSMENU`, `IDMENU`, `LIBELLE`, `SIGNIFICATION`, `HREF`, `ICON`, `ONCLICK`, `ACTIF`) VALUES
(1, 4, 'Nouvelle', 'Nouvelle inscription', '../inscriptions/nouvelle.php', NULL, NULL, 1),
(2, 4, 'Reinscription', 'Reinscription des eleves', '../inscriptions/reinscription.php', NULL, NULL, 1),
(3, 4, 'Desinscription', 'Desincription', '../inscriptions/desinscription.php', NULL, NULL, 1),
(4, 28, 'Frais de classes', 'Frais appliques a une classe', '../caisses/frais.php', NULL, NULL, 1),
(5, 28, 'Frais payes', 'Frais payer par un eleve', '../caisses/fraispayes.php', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDSTAFF` varchar(30) NOT NULL DEFAULT 'STAFFCOMON00',
  `NOM` varchar(255) NOT NULL,
  `PRENOM` varchar(255) NOT NULL,
  `DATENAISS` date NOT NULL,
  `TEL` varchar(255) NOT NULL,
  `ADRESSE` varchar(150) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `DIPLOME` int(11) NOT NULL,
  `SEXE` varchar(15) NOT NULL DEFAULT 'Masculin',
  `RELIGION` int(11) NOT NULL,
  `PHOTO` varchar(255) NOT NULL,
  `CURRICULUM` varchar(255) NOT NULL,
  `DATEDEBUT` date NOT NULL COMMENT 'Permet de preciser l''anciennete du professeur',
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Actif et 0 = non actif',
  PRIMARY KEY (`ID`),
  KEY `RELIGION` (`RELIGION`),
  KEY `DIPLOME` (`DIPLOME`),
  KEY `DATEDEBUT` (`DATEDEBUT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `trimestre`
--

CREATE TABLE IF NOT EXISTS `trimestre` (
  `IDTRIMESTRE` int(11) NOT NULL AUTO_INCREMENT,
  `ANNEEACADEMIQUE` varchar(30) NOT NULL,
  `DATEDEBUT` date NOT NULL,
  `DATEFIN` date NOT NULL,
  `ORDRE` int(11) NOT NULL,
  `LIBELLE` varchar(255) NOT NULL,
  PRIMARY KEY (`IDTRIMESTRE`),
  KEY `ANNEEACADEMIQUE` (`ANNEEACADEMIQUE`),
  KEY `ORDRE` (`ORDRE`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Contenu de la table `trimestre`
--

INSERT INTO `trimestre` (`IDTRIMESTRE`, `ANNEEACADEMIQUE`, `DATEDEBUT`, `DATEFIN`, `ORDRE`, `LIBELLE`) VALUES
(15, '2013-2014', '2013-11-06', '2013-11-16', 1, '1er Trimestre'),
(16, '2013-2014', '2013-11-06', '2013-11-22', 2, '2ieme Trimestre');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(15) NOT NULL,
  `PASSWORD` varchar(30) NOT NULL,
  `PROFILE` varchar(30) DEFAULT NULL,
  `ACTIF` int(11) NOT NULL DEFAULT '1' COMMENT '0 = Bloque et  = actif',
  PRIMARY KEY (`ID`,`LOGIN`),
  KEY `LOGIN` (`LOGIN`),
  KEY `PROFILE` (`PROFILE`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`ID`, `LOGIN`, `PASSWORD`, `PROFILE`, `ACTIF`) VALUES
(26, 'armel', 'tintin', 'Administration', 1);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `absence`
--
ALTER TABLE `absence`
  ADD CONSTRAINT `fk_absence_annee_academique` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`),
  ADD CONSTRAINT `fk_absence_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`),
  ADD CONSTRAINT `fk_absence_sequence` FOREIGN KEY (`IDSEQUENCE`) REFERENCES `sequence` (`IDSEQUENCE`),
  ADD CONSTRAINT `fk_absence_trimestre` FOREIGN KEY (`IDTRIMESTRE`) REFERENCES `trimestre` (`IDTRIMESTRE`),
  ADD CONSTRAINT `fk_idclasse` FOREIGN KEY (`IDCLASSE`) REFERENCES `classe` (`IDCLASSE`);

--
-- Contraintes pour la table `annee_academique`
--
ALTER TABLE `annee_academique`
  ADD CONSTRAINT `fk_annee_academique_decoupage` FOREIGN KEY (`DECOUPAGE`) REFERENCES `decoupage` (`ID`);

--
-- Contraintes pour la table `classe_frais`
--
ALTER TABLE `classe_frais`
  ADD CONSTRAINT `classe_frais_ibfk_1` FOREIGN KEY (`IDCLASSE`) REFERENCES `classe` (`IDCLASSE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `classe_frais_ibfk_2` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `classe_parametre`
--
ALTER TABLE `classe_parametre`
  ADD CONSTRAINT `classe_parametre_ibfk_1` FOREIGN KEY (`IDCLASSE`) REFERENCES `classe` (`IDCLASSE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `classe_parametre_ibfk_2` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_professeur2` FOREIGN KEY (`PROFPRINCIPAL`) REFERENCES `professeur` (`ID`);

--
-- Contraintes pour la table `classe_reduction`
--
ALTER TABLE `classe_reduction`
  ADD CONSTRAINT `classe_reduction_ibfk_1` FOREIGN KEY (`IDFRAIS`) REFERENCES `classe_frais` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `compte`
--
ALTER TABLE `compte`
  ADD CONSTRAINT `compte_ibfk_2` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compte_ibfk_4` FOREIGN KEY (`AUTEUR`) REFERENCES `users` (`LOGIN`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Contraintes pour la table `droit`
--
ALTER TABLE `droit`
  ADD CONSTRAINT `droit_ibfk_1` FOREIGN KEY (`IDMENU`) REFERENCES `menu` (`IDMENU`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `eleve`
--
ALTER TABLE `eleve`
  ADD CONSTRAINT `eleve_ibfk_2` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `eleve_ibfk_3` FOREIGN KEY (`RELIGION`) REFERENCES `religion` (`IDRELIGION`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Contraintes pour la table `enseigner`
--
ALTER TABLE `enseigner`
  ADD CONSTRAINT `enseigner_ibfk_1` FOREIGN KEY (`CLASSE`) REFERENCES `classe` (`IDCLASSE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enseigner_ibfk_4` FOREIGN KEY (`CODEMAT`) REFERENCES `matiere` (`CODEMAT`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enseigner_ibfk_6` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_professeur` FOREIGN KEY (`PROF`) REFERENCES `professeur` (`ID`);

--
-- Contraintes pour la table `frais_apayer`
--
ALTER TABLE `frais_apayer`
  ADD CONSTRAINT `frais_apayer_ibfk_1` FOREIGN KEY (`IDINSCRIPTION`) REFERENCES `inscription` (`IDINSCRIPTION`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frais_apayer_ibfk_2` FOREIGN KEY (`IDFRAIS`) REFERENCES `classe_frais` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `fk_incription_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`),
  ADD CONSTRAINT `inscription_ibfk_3` FOREIGN KEY (`IDCLASSE`) REFERENCES `classe` (`IDCLASSE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscription_ibfk_4` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `listedroit`
--
ALTER TABLE `listedroit`
  ADD CONSTRAINT `listedroit_ibfk_2` FOREIGN KEY (`PROFILE`) REFERENCES `profile` (`LIBELLE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `listedroit_ibfk_3` FOREIGN KEY (`IDDROIT`) REFERENCES `droit` (`IDDROIT`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `matiere`
--
ALTER TABLE `matiere`
  ADD CONSTRAINT `fk_matiere_matiere_type` FOREIGN KEY (`IDTYPE`) REFERENCES `matiere_group` (`IDTYPE`);

--
-- Contraintes pour la table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`HEADER`) REFERENCES `header_menu` (`IDHEADER`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `moratoire`
--
ALTER TABLE `moratoire`
  ADD CONSTRAINT `fk_moratoire_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`),
  ADD CONSTRAINT `moratoire_ibfk_2` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `fk_note_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`),
  ADD CONSTRAINT `fk_note_matiere` FOREIGN KEY (`IDMATIERE`) REFERENCES `matiere` (`CODEMAT`),
  ADD CONSTRAINT `fk_note_sequence` FOREIGN KEY (`IDSEQUENCE`) REFERENCES `sequence` (`IDSEQUENCE`);

--
-- Contraintes pour la table `operation`
--
ALTER TABLE `operation`
  ADD CONSTRAINT `fk_operation_annee_academique` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`),
  ADD CONSTRAINT `fk_operation_compte` FOREIGN KEY (`IDCOMPTE`) REFERENCES `compte` (`ID`),
  ADD CONSTRAINT `fk_operation_users` FOREIGN KEY (`AUTEUR`) REFERENCES `users` (`LOGIN`);

--
-- Contraintes pour la table `operation_attente`
--
ALTER TABLE `operation_attente`
  ADD CONSTRAINT `operation_attente_ibfk_1` FOREIGN KEY (`PERIODE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reduction_obtenue`
--
ALTER TABLE `reduction_obtenue`
  ADD CONSTRAINT `fk_reduction_obtenue_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`),
  ADD CONSTRAINT `reduction_obtenue_ibfk_1` FOREIGN KEY (`IDREDUCTION`) REFERENCES `classe_reduction` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reduction_obtenue_ibfk_2` FOREIGN KEY (`IDINSCRIPTION`) REFERENCES `inscription` (`IDINSCRIPTION`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `sequence`
--
ALTER TABLE `sequence`
  ADD CONSTRAINT `fk_sequence_trimestre` FOREIGN KEY (`IDTRIMESTRE`) REFERENCES `trimestre` (`IDTRIMESTRE`);

--
-- Contraintes pour la table `solde`
--
ALTER TABLE `solde`
  ADD CONSTRAINT `fk_eleve_eleve` FOREIGN KEY (`MATEL`) REFERENCES `eleve` (`ID`);

--
-- Contraintes pour la table `sous_menu`
--
ALTER TABLE `sous_menu`
  ADD CONSTRAINT `sous_menu_ibfk_1` FOREIGN KEY (`IDMENU`) REFERENCES `menu` (`IDMENU`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `trimestre`
--
ALTER TABLE `trimestre`
  ADD CONSTRAINT `trimestre_ibfk_1` FOREIGN KEY (`ANNEEACADEMIQUE`) REFERENCES `annee_academique` (`ANNEEACADEMIQUE`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`PROFILE`) REFERENCES `profile` (`LIBELLE`) ON DELETE SET NULL ON UPDATE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
