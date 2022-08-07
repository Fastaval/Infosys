-- MySQL dump 10.13  Distrib 5.5.60, for debian-linux-gnu (i686)
--
-- Host: db    Database: infosys2018
-- ------------------------------------------------------
-- Server version	5.5.60-0+deb7u1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activityageranges`
--

DROP TABLE IF EXISTS `activityageranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activityageranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `requirementtype` enum('min','max','exact') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_requirement` (`activity_id`,`requirementtype`),
  CONSTRAINT `activityageranges_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `aktiviteter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityageranges`
--

LOCK TABLES `activityageranges` WRITE;
/*!40000 ALTER TABLE `activityageranges` DISABLE KEYS */;
/*!40000 ALTER TABLE `activityageranges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `afviklinger`
--

DROP TABLE IF EXISTS `afviklinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `afviklinger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aktivitet_id` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `slut` datetime NOT NULL,
  `lokale_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periode_aktivitet` (`aktivitet_id`,`start`,`slut`),
  CONSTRAINT `afviklinger_ibfk_1` FOREIGN KEY (`aktivitet_id`) REFERENCES `aktiviteter` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger`
--

LOCK TABLES `afviklinger` WRITE;
/*!40000 ALTER TABLE `afviklinger` DISABLE KEYS */;
/*!40000 ALTER TABLE `afviklinger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `afviklinger_multiblok`
--

DROP TABLE IF EXISTS `afviklinger_multiblok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `afviklinger_multiblok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afvikling_id` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `slut` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periode_aktivitet` (`afvikling_id`,`start`,`slut`),
  CONSTRAINT `afviklinger_multiblok_ibfk_1` FOREIGN KEY (`afvikling_id`) REFERENCES `afviklinger` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger_multiblok`
--

LOCK TABLES `afviklinger_multiblok` WRITE;
/*!40000 ALTER TABLE `afviklinger_multiblok` DISABLE KEYS */;
/*!40000 ALTER TABLE `afviklinger_multiblok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aktiviteter`
--

DROP TABLE IF EXISTS `aktiviteter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aktiviteter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(256) NOT NULL,
  `kan_tilmeldes` enum('ja','nej') NOT NULL,
  `note` text,
  `foromtale` text,
  `varighed_per_afvikling` float NOT NULL,
  `min_deltagere_per_hold` int(11) NOT NULL,
  `max_deltagere_per_hold` int(11) NOT NULL,
  `spilledere_per_hold` int(11) NOT NULL,
  `pris` int(11) NOT NULL DEFAULT '20',
  `lokale_eksklusiv` enum('ja','nej') NOT NULL DEFAULT 'ja',
  `wp_link` int(11) NOT NULL,
  `teaser_dk` text NOT NULL,
  `teaser_en` text NOT NULL,
  `title_en` varchar(256) NOT NULL DEFAULT '',
  `description_en` text NOT NULL,
  `author` varchar(256) NOT NULL DEFAULT '',
  `type` enum('braet','rolle','live','figur','workshop','ottoviteter','magic','system','junior') NOT NULL,
  `tids_eksklusiv` enum('ja','nej') NOT NULL DEFAULT 'ja',
  `sprog` enum('dansk','engelsk','dansk+engelsk') NOT NULL DEFAULT 'dansk',
  `replayable` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `updated` datetime NOT NULL,
  `hidden` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `karmatype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `max_signups` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aktiviteter`
--

LOCK TABLES `aktiviteter` WRITE;
/*!40000 ALTER TABLE `aktiviteter` DISABLE KEYS */;
/*!40000 ALTER TABLE `aktiviteter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_auth`
--

DROP TABLE IF EXISTS `api_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `pass` char(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_auth`
--

LOCK TABLES `api_auth` WRITE;
/*!40000 ALTER TABLE `api_auth` DISABLE KEYS */;
INSERT INTO `api_auth` VALUES (1,'FV wordpress','Maem8ahchei8oozo8thai6ooCi8eLieF'),(2,'Fastaval app','eeng3roo1Aeyie7hae1rahfoo0cahy2e'),(3,'Fastaval Deltager Tilmelding','ohqu2oaRik0Foh2cai0chaeyaejev3th');
/*!40000 ALTER TABLE `api_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boardgameevents`
--

DROP TABLE IF EXISTS `boardgameevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boardgameevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `boardgame_id` int(10) unsigned NOT NULL,
  `type` enum('created','borrowed','returned','finished','present','not-present') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `boardgame` (`boardgame_id`),
  CONSTRAINT `boardgameevents_ibfk_1` FOREIGN KEY (`boardgame_id`) REFERENCES `boardgames` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgameevents`
--

LOCK TABLES `boardgameevents` WRITE;
/*!40000 ALTER TABLE `boardgameevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `boardgameevents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boardgames`
--

DROP TABLE IF EXISTS `boardgames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boardgames` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `designergame` tinyint(4) NOT NULL DEFAULT '0',
  `bgg_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgames`
--

LOCK TABLES `boardgames` WRITE;
/*!40000 ALTER TABLE `boardgames` DISABLE KEYS */;
/*!40000 ALTER TABLE `boardgames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brugerkategorier`
--

DROP TABLE IF EXISTS `brugerkategorier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brugerkategorier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(256) NOT NULL,
  `arrangoer` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `beskrivelse` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brugerkategorier`
--

LOCK TABLES `brugerkategorier` WRITE;
/*!40000 ALTER TABLE `brugerkategorier` DISABLE KEYS */;
INSERT INTO `brugerkategorier` VALUES (1,'Deltager','nej',NULL),(2,'Arrangør','ja',NULL),(3,'Infonaut','ja',NULL),(4,'Forfatter','ja',NULL),(5,'Arrangrhangaround','nej','Arrangr hangaround'),(6,'DirtBuster','ja',NULL),(7,'Freeloaders','nej','Folk, der skal ha gratis ting'),(8,'Brandvagt','ja','Medlem af brandvagten'),(9,'Kioskninja','ja','Vagt i kiosken'),(10,'Juniordeltager','nej','Junior deltager'),(11,'Kaffekrotjener','ja','Tjener i kaffekroen'),(12,'Fastaval Junior-arrangør','ja','Volunteer for Fastaval Junior');
/*!40000 ALTER TABLE `brugerkategorier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brugerkategorier_idtemplates`
--

DROP TABLE IF EXISTS `brugerkategorier_idtemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brugerkategorier_idtemplates` (
  `template_id` int(10) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`template_id`,`category_id`),
  KEY `category` (`category_id`),
  CONSTRAINT `brugerkategorier_idtemplates_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `brugerkategorier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `brugerkategorier_idtemplates_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `idtemplates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brugerkategorier_idtemplates`
--

LOCK TABLES `brugerkategorier_idtemplates` WRITE;
/*!40000 ALTER TABLE `brugerkategorier_idtemplates` DISABLE KEYS */;
INSERT INTO `brugerkategorier_idtemplates` VALUES (35,2),(38,3),(39,4),(37,6),(35,8),(43,9),(41,11),(35,12);
/*!40000 ALTER TABLE `brugerkategorier_idtemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere`
--

DROP TABLE IF EXISTS `deltagere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fornavn` varchar(128) NOT NULL,
  `efternavn` varchar(256) NOT NULL,
  `gender` enum('m','k','a') NOT NULL,
  `alder` int(11) NOT NULL,
  `email` varchar(512) NOT NULL,
  `tlf` varchar(32) DEFAULT NULL,
  `mobiltlf` varchar(32) DEFAULT NULL,
  `adresse1` varchar(128) NOT NULL,
  `adresse2` varchar(128) DEFAULT NULL,
  `postnummer` varchar(32) NOT NULL,
  `by` varchar(128) NOT NULL,
  `land` varchar(64) NOT NULL DEFAULT 'Danmark',
  `medbringer_mobil` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `sprog` set('dansk','engelsk','skandinavisk') NOT NULL DEFAULT 'dansk',
  `brugerkategori_id` int(11) NOT NULL,
  `forfatter` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `international` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `arrangoer_naeste_aar` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `betalt_beloeb` int(11) NOT NULL DEFAULT '0',
  `rel_karma` int(11) NOT NULL DEFAULT '0',
  `abs_karma` int(11) NOT NULL DEFAULT '0',
  `deltaget_i_fastaval` int(11) NOT NULL DEFAULT '0',
  `deltager_note` text,
  `admin_note` text,
  `beskeder` text,
  `created` datetime NOT NULL,
  `flere_gdsvagter` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `supergm` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `supergds` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `rig_onkel` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `arbejdsomraade` varchar(256) DEFAULT NULL,
  `scenarie` varchar(256) DEFAULT NULL,
  `udeblevet` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `rabat` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `sovesal` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `ungdomsskole` varchar(128) DEFAULT NULL,
  `hemmelig_onkel` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `krigslive_bus` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `oprydning_tirsdag` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `ready_mandag` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `ready_tirsdag` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `tilmeld_scenarieskrivning` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `may_contact` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `ny_alea` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `er_alea` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `password` varchar(64) NOT NULL,
  `original_price` float NOT NULL DEFAULT '0',
  `sovelokale_id` int(11) DEFAULT NULL,
  `paid_note` text,
  `checkin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `desired_activities` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `game_reallocation_participant` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `dancing_with_the_clans` enum('nej','Brujah','Gangrel','Malkavian','Nosferatu','Toreador','Tremere','Ventrue') NOT NULL DEFAULT 'nej',
  `birthdate` datetime NOT NULL,
  `extra_vouchers` tinyint(4) NOT NULL DEFAULT '0',
  `medical_note` text NOT NULL,
  `interpreter` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `gcm_id` text NOT NULL,
  `contacted_about_diy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `skills` text,
  `activity_lock` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `signed_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `annulled` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `package_gds` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `apple_id` char(64) NOT NULL DEFAULT '',
  `desired_diy_shifts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `brugerkategori_id` (`brugerkategori_id`),
  KEY `rel_karma` (`rel_karma`),
  KEY `abs_karma` (`abs_karma`),
  KEY `sovelokale_id` (`sovelokale_id`),
  CONSTRAINT `deltagere_ibfk_1` FOREIGN KEY (`brugerkategori_id`) REFERENCES `brugerkategorier` (`id`),
  CONSTRAINT `deltagere_ibfk_2` FOREIGN KEY (`sovelokale_id`) REFERENCES `lokaler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere`
--

LOCK TABLES `deltagere` WRITE;
/*!40000 ALTER TABLE `deltagere` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_gdstilmeldinger`
--

DROP TABLE IF EXISTS `deltagere_gdstilmeldinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_gdstilmeldinger` (
  `deltager_id` int(11) NOT NULL,
  `period` char(17) NOT NULL DEFAULT '',
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`deltager_id`,`period`,`category_id`),
  KEY `period` (`period`),
  KEY `gdscategory_fk` (`category_id`),
  CONSTRAINT `deltagere_gdstilmeldinger_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `gdscategory_fk` FOREIGN KEY (`category_id`) REFERENCES `gdscategories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_gdstilmeldinger`
--

LOCK TABLES `deltagere_gdstilmeldinger` WRITE;
/*!40000 ALTER TABLE `deltagere_gdstilmeldinger` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_gdstilmeldinger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_gdsvagter`
--

DROP TABLE IF EXISTS `deltagere_gdsvagter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_gdsvagter` (
  `deltager_id` int(11) NOT NULL,
  `gdsvagt_id` int(11) NOT NULL,
  `noshow` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`deltager_id`,`gdsvagt_id`),
  KEY `gdsvagt_id` (`gdsvagt_id`),
  CONSTRAINT `deltagere_gdsvagter_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_gdsvagter_ibfk_2` FOREIGN KEY (`gdsvagt_id`) REFERENCES `gdsvagter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_gdsvagter`
--

LOCK TABLES `deltagere_gdsvagter` WRITE;
/*!40000 ALTER TABLE `deltagere_gdsvagter` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_gdsvagter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_indgang`
--

DROP TABLE IF EXISTS `deltagere_indgang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_indgang` (
  `deltager_id` int(11) NOT NULL,
  `indgang_id` int(11) NOT NULL,
  PRIMARY KEY (`deltager_id`,`indgang_id`),
  KEY `indgang_id` (`indgang_id`),
  CONSTRAINT `deltagere_indgang_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_indgang_ibfk_2` FOREIGN KEY (`indgang_id`) REFERENCES `indgang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_indgang`
--

LOCK TABLES `deltagere_indgang` WRITE;
/*!40000 ALTER TABLE `deltagere_indgang` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_indgang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_madtider`
--

DROP TABLE IF EXISTS `deltagere_madtider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_madtider` (
  `deltager_id` int(11) NOT NULL,
  `madtid_id` int(11) NOT NULL,
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `time_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`deltager_id`,`madtid_id`),
  KEY `madtid_id` (`madtid_id`),
  CONSTRAINT `deltagere_madtider_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_madtider_ibfk_2` FOREIGN KEY (`madtid_id`) REFERENCES `madtider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_madtider`
--

LOCK TABLES `deltagere_madtider` WRITE;
/*!40000 ALTER TABLE `deltagere_madtider` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_madtider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_tilmeldinger`
--

DROP TABLE IF EXISTS `deltagere_tilmeldinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_tilmeldinger` (
  `deltager_id` int(11) NOT NULL,
  `prioritet` int(11) NOT NULL,
  `afvikling_id` int(11) NOT NULL,
  `tilmeldingstype` enum('spiller','spilleder') NOT NULL DEFAULT 'spiller',
  PRIMARY KEY (`deltager_id`,`prioritet`,`afvikling_id`),
  KEY `afvikling_id` (`afvikling_id`),
  CONSTRAINT `deltagere_tilmeldinger_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_tilmeldinger_ibfk_2` FOREIGN KEY (`afvikling_id`) REFERENCES `afviklinger` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_tilmeldinger`
--

LOCK TABLES `deltagere_tilmeldinger` WRITE;
/*!40000 ALTER TABLE `deltagere_tilmeldinger` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_tilmeldinger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deltagere_wear`
--

DROP TABLE IF EXISTS `deltagere_wear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deltagere_wear` (
  `deltager_id` int(11) NOT NULL,
  `wearpris_id` int(11) NOT NULL,
  `antal` int(11) NOT NULL,
  `size` varchar(8) NOT NULL,
  `received` enum('t','f') NOT NULL DEFAULT 'f',
  PRIMARY KEY (`deltager_id`,`wearpris_id`,`size`),
  KEY `wearpris_id` (`wearpris_id`),
  CONSTRAINT `deltagere_wear_ibfk_1` FOREIGN KEY (`wearpris_id`) REFERENCES `wearpriser` (`id`),
  CONSTRAINT `deltagere_wear_ibfk_2` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_wear`
--

LOCK TABLES `deltagere_wear` WRITE;
/*!40000 ALTER TABLE `deltagere_wear` DISABLE KEYS */;
/*!40000 ALTER TABLE `deltagere_wear` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diyageranges`
--

DROP TABLE IF EXISTS `diyageranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diyageranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diy_id` int(11) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `requirementtype` enum('min','max','exact') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `diy_requirement` (`diy_id`,`requirementtype`),
  CONSTRAINT `diyageranges_ibfk_1` FOREIGN KEY (`diy_id`) REFERENCES `gds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diyageranges`
--

LOCK TABLES `diyageranges` WRITE;
/*!40000 ALTER TABLE `diyageranges` DISABLE KEYS */;
/*!40000 ALTER TABLE `diyageranges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamestarts`
--

DROP TABLE IF EXISTS `gamestarts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamestarts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `datetime` (`datetime`),
  KEY `user_id_fk` (`user_id`),
  CONSTRAINT `gamestarts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `deltagere` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestarts`
--

LOCK TABLES `gamestarts` WRITE;
/*!40000 ALTER TABLE `gamestarts` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamestarts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamestartschedules`
--

DROP TABLE IF EXISTS `gamestartschedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamestartschedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gamestart_id` int(10) unsigned NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `gamers_present` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `gm_status` varchar(1024) NOT NULL,
  `updated` datetime NOT NULL,
  `reserves_offered` tinyint(3) unsigned NOT NULL,
  `reserves_accepted` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamestart_id` (`gamestart_id`,`schedule_id`),
  KEY `user_id_fk` (`user_id`),
  KEY `schedule_id_fk` (`schedule_id`),
  CONSTRAINT `gamestartschedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `gamestartschedules_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `afviklinger` (`id`),
  CONSTRAINT `gamestartschedules_ibfk_3` FOREIGN KEY (`gamestart_id`) REFERENCES `gamestarts` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestartschedules`
--

LOCK TABLES `gamestartschedules` WRITE;
/*!40000 ALTER TABLE `gamestartschedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamestartschedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gds`
--

DROP TABLE IF EXISTS `gds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(64) NOT NULL,
  `beskrivelse` varchar(512) DEFAULT NULL,
  `moedested` varchar(256) DEFAULT NULL,
  `title_en` varchar(64) NOT NULL DEFAULT '',
  `description_en` varchar(512) NOT NULL DEFAULT '',
  `category_id` int(10) unsigned NOT NULL,
  `moedested_en` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `navn` (`navn`),
  KEY `gds_category` (`category_id`),
  CONSTRAINT `gds_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gdscategories` (`id`),
  CONSTRAINT `gds_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `gdscategories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gds`
--

LOCK TABLES `gds` WRITE;
/*!40000 ALTER TABLE `gds` DISABLE KEYS */;
INSERT INTO `gds` VALUES (1,'Fest-opsætning','Som navnet antyder så handler det om opsætning til den store Ottofest. Opstilling af borde, stole og pynt.','Kære deltager, din GDS-tjans er Banketopsætning, mødestedet er fællesområdet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-prepping','As the name implies this has to do with the setup of the great Otto party.  Mostly about moving tables and chair, and setting up decorations.',4,'Dear participant, your DIY-job is party-prepping Sunday. Meeting place is the big hall. It will be fun and we\'re happy that you\'ll be joining us - see you there!'),(2,'Brandvagt','En tjans for dig der er over 18 og har mobiltelefonen med. Sørg for at der ikke udbryder brand i sovesalen. Det gør der som regel ikke, så det er muligvis en god ide at have en bog med.','Kære deltager, din GDS-tjans er Brandvagt, mød op i infoen et kvarter før din vagt starter. Det bliver hyggeligt, og på forhånd tak for hjælpen. :)','Night watch','A job for the over-18 with a working mobile. All you need to do is make sure no fires break out - or, if they do, alert everyone and the fire department. You\'re welcome to keep company (in the form of friends, candy, books or all of the above) while on duty.',5,'Dear participant, your DIY-job is fireguard. Meeting place is the information 15 minutes before the shift starts. Thanks very much for your help - we look forward to seeing you!'),(4,'Kiosk','Opfyldning af varer, produktion af toasts og naturligvis kundepleje kan du få udvidet kendskab til i kiosken. En fantastisk måde at møde mennesker på.','Kære deltager, din GDS-tjans er Kiosk, mødestedet er kiosken. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Shop','Stocking goods, producing toasts and obviously taking care of customers are all part of standig in the kiosk. A great way to meet people',2,'Dear participant, your DIY-job is helping out in the kiosk - meeting place is the kiosk. It will be lots of fun and we look forward to seeing you!'),(6,'Madudlevering','Delagtiggør resten af Fastaval i de mirakler køkkenet har frembragt.','Kære deltager, din GDS-tjans er Madudlevering, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Food handout','Make the miracles from the kitchen available to everybody',2,'Dear participant, your DIY-job is food handout. Meeting place is the kitchen. We look forward to seeing - thanks for joining us!'),(7,'Opvask','Den perfekte mulighed for køkkenhyggen, selv hvis du ikke er helt du med madlavning.','Kære deltager, din GDS-tjans er Opvask, mødestedet er madudleveringen. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Dishwashing','The perfect opportunity to take part in the kitchen cheer even if your cooking skill are not all the hot',3,'Dear participant, your DIY-job is dishwashing. Meeting place is where the food is handed out. We look forward to seeing you - thanks for joining!'),(10,'James','Deltag i det stilfulde James live. Høflig servering af dessert og drinks mm.','Kære deltager, din GDS-tjans er James, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Waiter (James)','Take part in the stylish James live. Politely serving dessert and drinks.',6,'Dear participant, your DIY-job is James (waiter). Meeting place is the kitchen. We look forward to seeing you - thanks for joining the fun!'),(11,'Fest-opvask','Opvask af glas fra banketten. Varer to timer.','Kære deltager, din GDS-tjans er Banketopvask, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-dishwashing','Cleaning of glasses after the banquet. The shift is shorter than previous years.',3,'Dear participant, your DIY-job is dishwashing after the party. Meeting place is the kitchen. We look forward to seeing you - thanks for joining!'),(12,'Lokaleoprydning','Lukning og oprydning af lokaler om lørdagen og søndagen. En vigtig tjans','Kære deltager, din GDS-tjans er oprydning, mødestedet er informationen kl.12.00. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Cleaning','Cleaning and closing game rooms on Saturday and Sunday.',3,'Dear participant, your DIY-job is cleaning, meeting place is the information at 12. We look forward to seeing you - it\'ll be fun!'),(13,'Bazar','Hurtig og nem tjans. Mest af alt slæbearbejde med opstilling of borde og den slags.','0','Bazaar','Fast and simple chore. Mostly heavy liftning to move tables back and forth',4,NULL),(17,'Caféhjælp','Hjælp til i caféen, der er brug for hjælp både i køkkenet med praktiske opgaver, som opvask anretning af tapas, men også bag baren til mixing og servering af drinks. Du aftaler selv med caféen om hvad du vil hjælpe med. Min alder 18 år.','Caféen','Café help','Help out in the café by cleaning, washing dishes and supplying drinks. Skills in drinks-mixing not required. You will settle tasks with the Cafe on arriving. Minimum age 18 years.',2,'Café'),(20,'Barhjælp','Bartenderi og generel hjælp i baren','Baren','Barhelp','Helping out tending bar and general bar duties',2,'The bar'),(21,'Kaffekro-hjælp','Hjælp til i kaffekroen med forefaldende arbejde','Kaffekroen','Coffee inn help','Help out in the coffee inn',2,'Coffee inn'),(22,'Fastaval Junior hjælp','Hjælp til opstilling og klargøring til Fastaval Junior','Fælleslokalet på D-gangen','Fastaval Junior setup','Setting up and prepping for Fastaval Junior',2,'The common area in the D-block'),(23,'Toasthalla - Lord of the Toast','Lord of the toast - Der skal laves toast og det skal være fedt!. Der skal skæres lidt grøntsager og ellers skal der klappes toast. Vi ses i køkkenet!','Køkkenet på gymnasiet','Toasthalla - Lord of the Toast','Lord of the toast - We\'re making toast and it will be epic! Vegetables need cutting and ingredients put together. See you in the kitchen1',2,'The kitchen'),(24,'Sushi-forberedelse','Sushi - så skal der rulles sushi! Vi sætter musik på og så står vi i køkkenet og ruller lækker sushi, når der ikke er mere sushi så stopper vagten, det bliver super hygge!','Køkkenet','Sushi-making','Sushi - let\'s roll some sushi! We\'ll put on some music and rock\'n\'roll some delicious sushi - when we\'re out of ingredients we\'re all done. It\'ll be great fun!',2,'The kitchen'),(25,'Fest-barhjælp (alkoholfri) ','Alkoholfri bartenderi og generel hjælp i den alkoholfri bar.','Den alkoholfri bar overfor baren','Party-barhelp (alcohol free)','Helping out tending the alcohol free bar and general bar duties.',2,'The alcohol free bar next to the bar');
/*!40000 ALTER TABLE `gds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gdscategories`
--

DROP TABLE IF EXISTS `gdscategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gdscategories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_da` varchar(64) NOT NULL,
  `name_en` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gdscategories`
--

LOCK TABLES `gdscategories` WRITE;
/*!40000 ALTER TABLE `gdscategories` DISABLE KEYS */;
INSERT INTO `gdscategories` VALUES (1,'Forplejning','Food-handling'),(2,'Service','Service'),(3,'Rengøring','Cleaning'),(4,'Manuelt arbejde','Manual labor'),(5,'Brandvagt','Night watch'),(6,'James (Otto-fest tjener)','James (Otto-celebration waiter)'),(7,'Ungdomslounge','Youth lounge');
/*!40000 ALTER TABLE `gdscategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gdsvagter`
--

DROP TABLE IF EXISTS `gdsvagter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gdsvagter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gds_id` int(11) NOT NULL,
  `antal_personer` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `slut` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gds_id` (`gds_id`,`start`),
  CONSTRAINT `gdsvagter_ibfk_1` FOREIGN KEY (`gds_id`) REFERENCES `gds` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gdsvagter`
--

LOCK TABLES `gdsvagter` WRITE;
/*!40000 ALTER TABLE `gdsvagter` DISABLE KEYS */;
INSERT INTO `gdsvagter` VALUES (9,4,2,'2018-03-28 20:00:00','2018-03-28 23:00:00'),(10,4,1,'2018-03-29 08:00:00','2018-03-29 11:00:00'),(12,4,2,'2018-03-29 12:00:00','2018-03-29 15:00:00'),(13,4,2,'2018-03-29 16:00:00','2018-03-29 19:00:00'),(14,4,2,'2018-03-29 20:00:00','2018-03-29 23:00:00'),(15,4,1,'2018-03-30 08:00:00','2018-03-30 11:00:00'),(17,4,2,'2018-03-30 12:00:00','2018-03-30 15:00:00'),(18,4,2,'2018-03-30 20:00:00','2018-03-30 23:00:00'),(25,4,1,'2018-03-31 08:00:00','2018-03-31 11:00:00'),(26,4,2,'2018-03-31 12:00:00','2018-03-31 15:00:00'),(27,4,2,'2018-03-31 16:00:00','2018-03-31 19:00:00'),(102,10,14,'2018-04-01 18:00:00','2018-04-01 23:00:00'),(105,1,4,'2018-03-31 11:00:00','2018-03-31 14:00:00'),(144,7,4,'2018-03-29 08:30:00','2018-03-29 10:30:00'),(145,7,4,'2018-03-30 08:30:00','2018-03-30 10:30:00'),(146,7,4,'2018-03-31 08:30:00','2018-03-31 10:30:00'),(149,7,5,'2018-03-29 18:00:00','2018-03-29 20:30:00'),(150,7,5,'2018-03-30 18:00:00','2018-03-30 20:30:00'),(151,7,5,'2018-03-31 18:00:00','2018-03-31 20:30:00'),(152,11,4,'2018-04-01 20:30:00','2018-04-01 23:30:00'),(153,4,2,'2018-03-30 16:00:00','2018-03-30 19:00:00'),(169,17,2,'2018-03-29 15:00:00','2018-03-29 19:00:00'),(170,17,1,'2018-03-29 19:00:00','2018-03-29 23:00:00'),(171,17,1,'2018-03-29 23:00:00','2018-03-30 02:00:00'),(173,17,2,'2018-03-30 15:00:00','2018-03-30 19:00:00'),(174,17,1,'2018-03-30 19:00:00','2018-03-30 23:00:00'),(175,17,1,'2018-03-30 23:00:00','2018-03-31 02:00:00'),(179,17,2,'2018-03-31 15:00:00','2018-03-31 19:00:00'),(185,20,1,'2018-03-29 01:30:00','2018-03-29 03:30:00'),(188,20,1,'2018-03-30 01:30:00','2018-03-30 03:30:00'),(191,20,1,'2018-03-31 01:30:00','2018-03-31 03:30:00'),(199,21,1,'2018-03-29 10:00:00','2018-03-29 12:00:00'),(200,21,1,'2018-03-30 10:00:00','2018-03-30 12:00:00'),(201,21,1,'2018-03-31 10:00:00','2018-03-31 12:00:00'),(204,21,1,'2018-03-29 16:00:00','2018-03-29 18:00:00'),(205,21,1,'2018-03-30 16:00:00','2018-03-30 18:00:00'),(207,21,1,'2018-03-28 19:00:00','2018-03-28 21:00:00'),(208,21,1,'2018-03-29 19:00:00','2018-03-29 21:00:00'),(209,21,1,'2018-03-30 19:00:00','2018-03-30 21:00:00'),(210,22,2,'2018-03-29 11:15:00','2018-03-29 13:15:00'),(213,23,3,'2018-03-29 12:00:00','2018-03-29 16:00:00'),(215,2,1,'2018-03-30 14:45:00','2018-03-30 18:00:00'),(220,2,1,'2018-03-29 08:45:00','2018-03-29 12:00:00'),(221,2,1,'2018-03-29 11:45:00','2018-03-29 15:00:00'),(222,2,1,'2018-03-29 14:45:00','2018-03-29 18:00:00'),(223,2,1,'2018-03-29 17:45:00','2018-03-29 21:00:00'),(224,2,1,'2018-03-29 20:45:00','2018-03-29 23:00:00'),(225,2,1,'2018-03-30 08:45:00','2018-03-30 12:00:00'),(226,2,1,'2018-03-30 11:45:00','2018-03-30 15:00:00'),(227,2,1,'2018-03-30 17:45:00','2018-03-30 21:00:00'),(228,2,1,'2018-03-31 08:45:00','2018-03-31 12:00:00'),(229,2,1,'2018-03-31 11:45:00','2018-03-31 15:00:00'),(230,2,1,'2018-03-31 14:45:00','2018-03-31 18:00:00'),(231,2,1,'2018-03-31 17:45:00','2018-03-31 21:00:00'),(232,2,1,'2018-03-31 20:45:00','2018-03-31 23:00:00'),(233,2,1,'2018-03-30 20:45:00','2018-03-30 23:00:00'),(234,17,1,'2018-03-31 23:00:00','2018-04-01 02:00:00'),(236,17,1,'2018-03-31 19:00:00','2018-03-31 23:00:00'),(237,1,2,'2018-04-01 10:00:00','2018-04-01 14:00:00'),(238,1,2,'2018-04-01 13:00:00','2018-04-01 17:00:00'),(239,1,1,'2018-04-01 12:00:00','2018-04-01 16:00:00'),(240,4,1,'2018-04-01 08:00:00','2018-04-01 10:00:00'),(241,4,1,'2018-04-01 10:00:00','2018-04-01 12:00:00'),(247,2,1,'2018-04-01 08:45:00','2018-04-01 12:00:00'),(248,2,1,'2018-04-01 11:45:00','2018-04-01 15:00:00'),(249,2,1,'2018-04-01 14:45:00','2018-04-01 18:00:00'),(250,2,1,'2018-04-01 17:45:00','2018-04-01 21:00:00'),(251,2,1,'2018-04-01 20:45:00','2018-04-01 23:00:00'),(252,7,4,'2018-03-29 14:00:00','2018-03-29 16:00:00'),(253,7,4,'2018-03-30 14:00:00','2018-03-30 16:00:00'),(254,7,4,'2018-03-31 14:00:00','2018-03-31 16:00:00'),(255,7,4,'2018-04-01 08:30:00','2018-04-01 10:30:00'),(256,7,4,'2018-04-01 14:00:00','2018-04-01 16:00:00'),(257,7,5,'2018-04-01 18:00:00','2018-04-01 20:30:00'),(258,17,2,'2018-04-02 01:00:00','2018-04-02 03:00:00'),(259,17,2,'2018-04-01 23:00:00','2018-04-02 01:00:00'),(262,25,4,'2018-04-01 23:00:00','2018-04-02 01:00:00'),(263,4,2,'2018-03-28 16:00:00','2018-03-28 19:00:00'),(264,17,2,'2018-04-01 19:00:00','2018-04-01 23:00:00');
/*!40000 ALTER TABLE `gdsvagter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hold`
--

DROP TABLE IF EXISTS `hold`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hold` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afvikling_id` int(11) NOT NULL,
  `holdnummer` int(11) NOT NULL,
  `lokale_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `afvikling_id` (`afvikling_id`,`holdnummer`),
  KEY `lokale_id` (`lokale_id`),
  CONSTRAINT `hold_ibfk_1` FOREIGN KEY (`afvikling_id`) REFERENCES `afviklinger` (`id`),
  CONSTRAINT `hold_ibfk_2` FOREIGN KEY (`lokale_id`) REFERENCES `lokaler` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hold`
--

LOCK TABLES `hold` WRITE;
/*!40000 ALTER TABLE `hold` DISABLE KEYS */;
/*!40000 ALTER TABLE `hold` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `idtemplates`
--

DROP TABLE IF EXISTS `idtemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `idtemplates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `background` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `idtemplates`
--

LOCK TABLES `idtemplates` WRITE;
/*!40000 ALTER TABLE `idtemplates` DISABLE KEYS */;
INSERT INTO `idtemplates` VALUES (33,'Special18','/uploads/id_25_bg.jpg'),(35,'Arrangør18','/uploads/id_35_bg.png'),(37,'Dirtbusters18','/uploads/id_37_bg.png'),(38,'Infonaut18','/uploads/id_38_bg.png'),(39,'Scenarie18','/uploads/id_39_bg.png'),(40,'Brætspil18','/uploads/id_40_bg.png'),(41,'Kaffekrotjener18','/uploads/id_41_bg.png'),(42,'Grafiker og tema18','/uploads/id_42_bg.png'),(43,'Kioskninja18','/uploads/id_43_bg.png');
/*!40000 ALTER TABLE `idtemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `idtemplates_items`
--

DROP TABLE IF EXISTS `idtemplates_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `idtemplates_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `itemtype` enum('photo','text','barcode') NOT NULL,
  `x` int(10) unsigned NOT NULL,
  `y` int(10) unsigned NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `rotation` int(11) NOT NULL DEFAULT '0',
  `datasource` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `template` (`template_id`),
  CONSTRAINT `idtemplates_items_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `idtemplates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11247 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `idtemplates_items`
--

LOCK TABLES `idtemplates_items` WRITE;
/*!40000 ALTER TABLE `idtemplates_items` DISABLE KEYS */;
INSERT INTO `idtemplates_items` VALUES (10152,35,'photo',155,190,213,295,0,''),(10153,35,'text',528,230,200,50,0,'id'),(10154,35,'text',375,290,500,50,0,'name'),(10155,35,'text',375,350,500,50,0,'workarea'),(10156,35,'barcode',450,415,350,100,0,''),(10612,41,'photo',155,190,213,295,0,''),(10613,41,'text',528,230,200,50,0,'id'),(10614,41,'text',375,290,500,50,0,'name'),(10615,41,'text',375,350,500,50,0,'workarea'),(10616,41,'barcode',450,415,350,100,0,''),(10722,42,'photo',155,190,213,295,0,''),(10723,42,'text',528,230,200,50,0,'id'),(10724,42,'text',375,290,500,50,0,'name'),(10725,42,'text',375,350,500,50,0,'workarea'),(10726,42,'barcode',450,415,350,100,0,''),(10832,43,'photo',155,190,213,295,0,''),(10833,43,'text',528,230,200,50,0,'id'),(10834,43,'text',375,290,500,50,0,'name'),(10835,43,'text',375,350,500,50,0,'workarea'),(10836,43,'barcode',450,415,350,100,0,''),(10852,37,'photo',155,190,213,295,0,''),(10853,37,'text',528,230,200,50,0,'id'),(10854,37,'text',375,290,500,50,0,'group'),(10855,37,'text',375,350,500,50,0,'workarea'),(10856,37,'barcode',450,415,350,100,0,''),(10882,40,'photo',158,190,213,295,0,''),(10883,40,'text',528,230,200,50,0,'id'),(10884,40,'text',375,290,500,50,0,'name'),(10885,40,'text',375,350,500,50,0,'scenario'),(10886,40,'barcode',450,415,350,100,0,''),(11162,39,'photo',158,190,213,295,0,''),(11163,39,'text',528,230,200,50,0,'id'),(11164,39,'text',375,290,500,50,0,'name'),(11165,39,'text',375,350,500,50,0,'scenario'),(11166,39,'barcode',450,415,350,100,0,''),(11227,38,'photo',155,190,213,295,0,''),(11228,38,'text',528,230,200,50,0,'id'),(11229,38,'text',375,290,500,50,0,'name'),(11230,38,'text',375,350,500,50,0,'workarea'),(11231,38,'barcode',450,415,350,100,0,''),(11242,33,'photo',155,190,213,295,0,''),(11243,33,'text',528,230,200,50,0,'id'),(11244,33,'text',375,290,500,50,0,'name'),(11245,33,'text',375,350,500,50,0,'workarea'),(11246,33,'barcode',450,415,350,100,0,'');
/*!40000 ALTER TABLE `idtemplates_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indgang`
--

DROP TABLE IF EXISTS `indgang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indgang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pris` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `type` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indgang`
--

LOCK TABLES `indgang` WRITE;
/*!40000 ALTER TABLE `indgang` DISABLE KEYS */;
INSERT INTO `indgang` VALUES (1,300,'2018-03-28 00:00:00','Indgang - Partout'),(2,175,'2018-03-28 00:00:00','Indgang - Partout - Alea'),(3,75,'2018-03-28 08:00:00','Indgang - Enkelt'),(4,75,'2018-03-29 08:00:00','Indgang - Enkelt'),(5,75,'2018-03-30 08:00:00','Indgang - Enkelt'),(6,75,'2018-03-31 08:00:00','Indgang - Enkelt'),(7,75,'2018-04-01 08:00:00','Indgang - Enkelt'),(8,200,'2018-03-28 00:00:00','Overnatning - Partout'),(9,75,'2018-03-28 22:00:00','Overnatning - Enkelt'),(10,75,'2018-03-29 22:00:00','Overnatning - Enkelt'),(11,75,'2018-03-30 22:00:00','Overnatning - Enkelt'),(12,75,'2018-03-31 22:00:00','Overnatning - Enkelt'),(13,75,'2018-04-01 22:00:00','Overnatning - Enkelt'),(14,150,'2018-03-28 00:00:00','Leje af madras'),(15,100,'2018-03-28 00:00:00','Indgang - Partout - Alea - Ung'),(16,200,'2018-03-28 00:00:00','Indgang - Partout - Ung'),(19,125,'2018-03-28 00:00:00','Overnatning - Partout - Arrangør'),(20,75,'2018-03-28 00:00:00','Alea medlemskab'),(21,100,'2018-04-01 20:00:00','Ottofest'),(22,225,'2018-04-01 00:00:00','Indgang - Partout - Arrangør'),(23,100,'2018-04-01 00:00:00','Indgang - Partout - Alea - Arrangør'),(24,99,'2018-04-01 00:00:00','Ottofest - Champagne'),(27,0,'2018-03-28 00:00:00','GRATIST Dagsbillet'),(28,0,'2018-03-29 00:00:00','GRATIST Dagsbillet'),(29,0,'2018-03-30 00:00:00','GRATIST Dagsbillet'),(30,0,'2018-03-31 00:00:00','GRATIST Dagsbillet'),(31,0,'2018-04-01 00:00:00','GRATIST Dagsbillet'),(32,0,'2018-03-28 00:00:00','GRATIST Overnatning'),(33,0,'2018-03-29 00:00:00','GRATIST Overnatning'),(34,0,'2018-03-30 00:00:00','GRATIST Overnatning'),(35,0,'2018-03-31 00:00:00','GRATIST Overnatning'),(36,0,'2018-04-01 00:00:00','GRATIST Overnatning'),(37,10,'2018-03-28 00:00:00','Bankoverførselsgebyr'),(38,50,'2018-03-28 00:00:00','Dørbetalingsgebyr'),(39,0,'2018-03-28 00:00:00','Indgang - Partout - Barn'),(40,15,'2018-02-03 00:00:00','Billetgebyr'),(41,80,'2018-04-01 00:00:00','Ottofest - hvidvin'),(42,80,'2018-04-01 00:00:00','Ottofest - rødvin'),(43,190,'2018-03-28 00:00:00','Campingvogn'),(44,115,'2018-03-28 00:00:00','Campingvogn - Arrangør'),(45,170,'2018-03-29 00:00:00','Indgang - Junior'),(46,100,'2018-03-28 00:00:00','Rig onkel - 100'),(47,200,'2018-03-28 00:00:00','Rig onkel - 200'),(48,300,'2018-03-28 00:00:00','Rig onkel - 300'),(49,400,'2018-03-28 00:00:00','Rig onkel - 400'),(50,500,'2018-03-28 00:00:00','Rig onkel - 500'),(51,100,'2018-03-28 00:00:00','Hemmelig onkel - 100'),(52,200,'2018-03-28 00:00:00','Hemmelig onkel - 200'),(53,300,'2018-03-28 00:00:00','Hemmelig onkel - 300'),(54,400,'2018-03-28 00:00:00','Hemmelig onkel - 400'),(55,500,'2018-03-28 00:00:00','Hemmelig onkel - 500'),(56,600,'2018-03-28 00:00:00','Hemmelig onkel - 600'),(58,800,'2018-03-28 00:00:00','Hemmelig onkel - 800'),(59,900,'2018-03-28 00:00:00','Hemmelig onkel - 900'),(60,600,'2018-03-28 00:00:00','Rig onkel - 600'),(61,700,'2018-03-28 00:00:00','Rig onkel - 700'),(62,800,'2018-03-28 00:00:00','Rig onkel - 800'),(63,900,'2018-03-28 00:00:00','Rig onkel - 900'),(64,700,'2018-03-28 00:00:00','Hemmelig onkel - 700'),(65,0,'2018-03-28 00:00:00','GRATIST Partout'),(68,0,'2018-03-28 00:00:00','GRATIS Overnatning - Partout');
/*!40000 ALTER TABLE `indgang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loanevents`
--

DROP TABLE IF EXISTS `loanevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loanevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loanitem_id` int(10) unsigned NOT NULL,
  `type` enum('created','borrowed','returned','finished') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `loanitem` (`loanitem_id`),
  CONSTRAINT `loanevents_ibfk_1` FOREIGN KEY (`loanitem_id`) REFERENCES `loanitems` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanevents`
--

LOCK TABLES `loanevents` WRITE;
/*!40000 ALTER TABLE `loanevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `loanevents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loanitems`
--

DROP TABLE IF EXISTS `loanitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loanitems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanitems`
--

LOCK TABLES `loanitems` WRITE;
/*!40000 ALTER TABLE `loanitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `loanitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `message` varchar(256) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lokaler`
--

DROP TABLE IF EXISTS `lokaler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lokaler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beskrivelse` varchar(256) DEFAULT NULL,
  `omraade` varchar(32) DEFAULT NULL,
  `skole` varchar(64) NOT NULL,
  `kan_bookes` enum('ja','nej') NOT NULL DEFAULT 'ja',
  `sovelokale` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `sovekapacitet` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokaler`
--

LOCK TABLES `lokaler` WRITE;
/*!40000 ALTER TABLE `lokaler` DISABLE KEYS */;
INSERT INTO `lokaler` VALUES (1,'Fællesområde','','','ja','nej',0),(2,'Brætspil','','','ja','nej',0),(3,'Ottos kaffekro ved samlingspunktet','','','ja','nej',0),(4,'A-gang','','','ja','nej',0),(5,'A07','','','ja','nej',3),(7,'B32','','','ja','nej',0),(8,'B30','','','ja','nej',0),(9,'B36','','','ja','nej',0),(10,'B38','','','ja','nej',0),(11,'B39','','','ja','nej',0),(12,'B41','','','ja','nej',0),(13,'B43','','','ja','nej',0),(14,'B44 ','','','ja','nej',0),(15,'B46 ','','','ja','nej',0),(16,'B47','','','ja','nej',0),(17,'C53','Kælder','','ja','nej',0),(18,'D60','','','ja','nej',0),(19,'D61','','','ja','nej',0),(20,'D62','','','ja','nej',0),(21,'D65','','','ja','nej',0),(22,'D66','','','ja','nej',0),(23,'D67','','','ja','nej',0),(24,'1.01 - Info','Info','','ja','nej',0),(25,'1.02','','','ja','nej',0),(27,'1.04','','','ja','nej',0),(28,'1.05','','','ja','nej',0),(29,'2.01','','','ja','nej',0),(30,'2.02','','','ja','nej',0),(31,'2.03','','','ja','nej',0),(32,'2.04','','','ja','nej',0),(34,'2.06','','','ja','nej',0),(35,'2.09','','','ja','nej',0),(36,'Svømmehal','','','ja','nej',0),(37,'E70','TV','','nej','nej',3),(38,'FastaWar','','','ja','nej',0),(39,'Baren','','','ja','nej',0),(43,'X Fiktivt lokale 6','','','ja','nej',0),(44,'C58 - Formning','\"begrænset brug\"','','ja','nej',0),(45,'X Fiktivt lokale 5','','','ja','nej',0),(46,'X Fiktivt lokale 1','','','ja','nej',0),(47,'X Fiktivt lokale 2','','','ja','nej',0),(48,'X Fiktivt lokale 3','','','ja','nej',0),(49,'X Fiktivt lokale 4','','','ja','nej',0),(50,'X Fiktivt lokale 7','','','ja','nej',0),(51,'X Fiktivt lokale 8','','','ja','nej',0),(52,'X Fiktivt lokale 9','','','ja','nej',0),(53,'X Fiktivt lokale 10','','','ja','nej',0),(54,'B45','','','nej','nej',0),(55,'A08','','','ja','nej',0),(56,'B 34','','','ja','nej',0),(59,'C 50','Kælder','','ja','nej',0),(60,'C 51','Kælder','','ja','nej',0),(61,'C 55','Kælder','','ja','nej',0),(62,'Cykelkælder','','','ja','nej',0),(63,'Caféen','','','ja','nej',0),(64,'Brætspilscafeen','','','ja','nej',0),(65,'D-gang - Magic','Magic','','ja','nej',0),(66,'C48','Brandvagts sovesal','','nej','nej',10),(67,'Arrangørsovesal','','','nej','nej',50),(68,'Ungdomssovesal','','','nej','nej',50),(69,'C58','Hinterlandet','','ja','nej',0),(70,'Sovesal','Idrætscenteret','','nej','nej',362),(71,'E71','','','ja','nej',0),(72,'Vandrehjem/Hostel','','','ja','nej',0),(73,'Pathfinder','','','ja','nej',0),(74,'2.05','','','ja','nej',0),(75,'2.24','','','ja','nej',0),(76,'C57','','','nej','nej',0),(77,'B40','','','nej','nej',0),(78,'A10','','','nej','nej',0),(80,'B40','HInterlandet','','ja','nej',0),(81,'A09','','Mariagerfjord Gymnasium','ja','nej',0),(82,'Østerskov','','','ja','nej',0),(83,'Game Rush','','','ja','nej',0),(84,'Rød sovesal (DB)','Idrætscenteret','','nej','nej',30);
/*!40000 ALTER TABLE `lokaler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mad`
--

DROP TABLE IF EXISTS `mad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori` varchar(64) NOT NULL,
  `pris` int(11) NOT NULL,
  `title_en` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mad`
--

LOCK TABLES `mad` WRITE;
/*!40000 ALTER TABLE `mad` DISABLE KEYS */;
INSERT INTO `mad` VALUES (5,'Morgenmad - Full English',45,'Breakfast - Full English'),(7,'Morgenmad - Big Veggie',45,'Breakfast - Big Veggie'),(8,'Morgenmad - Grød & Yoghurt menu',32,'Breakfast - Porridge & Yoghurt'),(9,'Frokost - Frokostburger m. okse eller vegetarbøf',45,'Lunch - Lunch Burger with beef or vegetarian steak'),(10,'Frokost - Sæsonens sandwich',45,'Lunch - Season?s Sandwich'),(11,'Frokost - Quinoa Tabbouleh',45,'Lunch - Quinoa Tabbouleh'),(12,'Aftensmad - Boeuf Bourguignon',68,'Dinner - Boeuf Bourguignon'),(13,'Aftensmad - Klassisk Pizza ',68,'Dinner - Classic Pizza'),(14,'Aftensmad - Batat Chili Sin Carne',68,'Dinner - Batat Chili Sin Carne'),(15,'Aftensmad - Ratatouille ',68,'Dinner - Ratatouille'),(17,'Aftensmad - Klassisk burger',68,'Dinner - Classical Burger'),(18,'Aftensmad - Cowboy steg m. rodfrugter',68,'Dinner - Cowboy steak with root crops'),(19,'Aftensmad - Shepards/Cottage Pie',68,'Dinner - Shepards / Cottage Pie'),(20,'Frokost - Sprød Cæsar salat',45,'Lunch - Crisp Caesar Salad'),(22,'Frokost - Egen tunsalat m. pocheret æg',45,'Lunch - Tuna salad with poached egg');
/*!40000 ALTER TABLE `mad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `madtider`
--

DROP TABLE IF EXISTS `madtider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `madtider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mad_id` int(11) NOT NULL,
  `dato` datetime NOT NULL,
  `description_da` varchar(128) NOT NULL DEFAULT '',
  `description_en` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mad_id` (`mad_id`,`dato`),
  CONSTRAINT `madtider_ibfk_1` FOREIGN KEY (`mad_id`) REFERENCES `mad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `madtider`
--

LOCK TABLES `madtider` WRITE;
/*!40000 ALTER TABLE `madtider` DISABLE KEYS */;
INSERT INTO `madtider` VALUES (194,12,'2018-03-28 17:00:00','',''),(195,12,'2018-03-29 17:00:00','',''),(196,12,'2018-03-30 17:00:00','',''),(197,12,'2018-03-31 17:00:00','',''),(198,12,'2018-04-01 17:00:00','',''),(199,13,'2018-03-28 17:00:00','',''),(200,13,'2018-03-29 17:00:00','',''),(201,13,'2018-03-30 17:00:00','',''),(202,13,'2018-03-31 17:00:00','',''),(203,13,'2018-04-01 17:00:00','',''),(204,14,'2018-03-28 17:00:00','',''),(205,14,'2018-03-29 17:00:00','',''),(206,14,'2018-03-30 17:00:00','',''),(207,14,'2018-03-31 17:00:00','',''),(208,14,'2018-04-01 17:00:00','',''),(209,15,'2018-03-28 17:00:00','',''),(210,15,'2018-03-29 17:00:00','',''),(211,15,'2018-03-30 17:00:00','',''),(212,15,'2018-03-31 17:00:00','',''),(213,15,'2018-04-01 17:00:00','',''),(219,17,'2018-03-28 17:00:00','',''),(220,17,'2018-03-29 17:00:00','',''),(221,17,'2018-03-30 17:00:00','',''),(222,17,'2018-03-31 17:00:00','',''),(223,17,'2018-04-01 17:00:00','',''),(224,18,'2018-03-28 17:00:00','',''),(225,18,'2018-03-29 17:00:00','',''),(226,18,'2018-03-30 17:00:00','',''),(227,18,'2018-03-31 17:00:00','',''),(228,18,'2018-04-01 17:00:00','',''),(229,19,'2018-03-28 17:00:00','',''),(230,19,'2018-03-29 17:00:00','',''),(231,19,'2018-03-30 17:00:00','',''),(232,19,'2018-03-31 17:00:00','',''),(233,19,'2018-04-01 17:00:00','',''),(244,5,'2018-03-29 08:00:00','',''),(245,5,'2018-03-30 08:00:00','',''),(246,5,'2018-03-31 08:00:00','',''),(247,5,'2018-04-01 08:00:00','',''),(248,7,'2018-03-29 08:00:00','',''),(249,7,'2018-03-30 08:00:00','',''),(250,7,'2018-03-31 08:00:00','',''),(251,7,'2018-04-01 08:00:00','',''),(252,8,'2018-03-29 08:00:00','',''),(253,8,'2018-03-30 08:00:00','',''),(254,8,'2018-03-31 08:00:00','',''),(255,8,'2018-04-01 08:00:00','',''),(256,9,'2018-03-28 12:00:00','',''),(257,9,'2018-03-29 12:00:00','',''),(258,9,'2018-03-30 12:00:00','',''),(259,9,'2018-03-31 12:00:00','',''),(260,9,'2018-04-01 12:00:00','',''),(266,10,'2018-03-29 12:00:00','',''),(267,10,'2018-03-30 12:00:00','',''),(268,10,'2018-03-31 12:00:00','',''),(269,10,'2018-04-01 12:00:00','',''),(270,11,'2018-03-29 13:00:00','',''),(271,11,'2018-03-30 13:00:00','',''),(272,11,'2018-03-31 12:00:00','',''),(273,11,'2018-04-01 12:00:00','',''),(274,20,'2018-03-29 13:00:00','',''),(275,20,'2018-03-30 13:00:00','',''),(276,20,'2018-03-31 12:00:00','',''),(277,20,'2018-04-01 12:00:00','',''),(278,22,'2018-03-29 13:00:00','',''),(279,22,'2018-03-30 13:00:00','',''),(280,22,'2018-03-31 12:00:00','',''),(281,22,'2018-04-01 12:00:00','','');
/*!40000 ALTER TABLE `madtider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1001),(1002),(1003),(1004),(1005),(1006),(1007),(1008),(1009),(1010),(1011),(1012),(1013),(1014),(1015),(1016),(1017),(1018),(1019),(1020),(1021),(1022),(1023),(1024),(1025),(1026);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `area` enum('shop','boardgames','loans') NOT NULL,
  `note` text NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`area`),
  UNIQUE KEY `area` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES ('boardgames','clank, mangler blå cube, ragnvalds udgave\n\nmagic maze og love letter i pose mangler ved tjek\n\ncatbox måske (ID: 756, Erica Larsson Lundh 2018-04-01 00:06:28)','2018-03-30 19:26:12');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantidtemplates`
--

DROP TABLE IF EXISTS `participantidtemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participantidtemplates` (
  `participant_id` int(11) NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`participant_id`),
  KEY `template` (`template_id`),
  CONSTRAINT `participantidtemplates_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `participantidtemplates_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `idtemplates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantidtemplates`
--

LOCK TABLES `participantidtemplates` WRITE;
/*!40000 ALTER TABLE `participantidtemplates` DISABLE KEYS */;
/*!40000 ALTER TABLE `participantidtemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantpaymenthashes`
--

DROP TABLE IF EXISTS `participantpaymenthashes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participantpaymenthashes` (
  `participant_id` int(11) NOT NULL,
  `hash` char(32) NOT NULL,
  PRIMARY KEY (`participant_id`),
  CONSTRAINT `participantpaymenthashes_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantpaymenthashes`
--

LOCK TABLES `participantpaymenthashes` WRITE;
/*!40000 ALTER TABLE `participantpaymenthashes` DISABLE KEYS */;
/*!40000 ALTER TABLE `participantpaymenthashes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantphotoidentifiers`
--

DROP TABLE IF EXISTS `participantphotoidentifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participantphotoidentifiers` (
  `participant_id` int(11) NOT NULL,
  `identifier` tinytext NOT NULL,
  PRIMARY KEY (`participant_id`),
  CONSTRAINT `participantphotoidentifiers_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantphotoidentifiers`
--

LOCK TABLES `participantphotoidentifiers` WRITE;
/*!40000 ALTER TABLE `participantphotoidentifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `participantphotoidentifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participants_sleepingplaces`
--

DROP TABLE IF EXISTS `participants_sleepingplaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participants_sleepingplaces` (
  `participant_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `starts` datetime NOT NULL,
  `ends` datetime NOT NULL,
  PRIMARY KEY (`participant_id`,`room_id`,`starts`),
  KEY `room_fk` (`room_id`),
  CONSTRAINT `participants_sleepingplaces_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `participants_sleepingplaces_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `lokaler` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants_sleepingplaces`
--

LOCK TABLES `participants_sleepingplaces` WRITE;
/*!40000 ALTER TABLE `participants_sleepingplaces` DISABLE KEYS */;
/*!40000 ALTER TABLE `participants_sleepingplaces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymentfritidlog`
--

DROP TABLE IF EXISTS `paymentfritidlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentfritidlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `participant_id` int(11) NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `cost` int(10) unsigned NOT NULL,
  `fees` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentfritidlog`
--

LOCK TABLES `paymentfritidlog` WRITE;
/*!40000 ALTER TABLE `paymentfritidlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `paymentfritidlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pladser`
--

DROP TABLE IF EXISTS `pladser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pladser` (
  `hold_id` int(11) NOT NULL,
  `pladsnummer` int(11) NOT NULL,
  `type` enum('spilleder','spiller') NOT NULL,
  `deltager_id` int(11) NOT NULL,
  PRIMARY KEY (`hold_id`,`pladsnummer`),
  UNIQUE KEY `hold_id` (`hold_id`,`deltager_id`),
  KEY `deltager_id` (`deltager_id`),
  CONSTRAINT `pladser_ibfk_1` FOREIGN KEY (`hold_id`) REFERENCES `hold` (`id`),
  CONSTRAINT `pladser_ibfk_2` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pladser`
--

LOCK TABLES `pladser` WRITE;
/*!40000 ALTER TABLE `pladser` DISABLE KEYS */;
/*!40000 ALTER TABLE `pladser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(128) NOT NULL,
  `method` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (44,'*','*'),(45,'ActivityController','*'),(46,'ParticipantController','*'),(47,'GdsController','*'),(48,'GroupsController','*'),(49,'IndexController','*'),(50,'LogController','*'),(51,'RoomsController','*'),(52,'FoodController','*'),(53,'WearController','*'),(54,'AdminController','*'),(55,'TodoController','*'),(56,'TodoController','viewTodoList'),(57,'GroupsController','main'),(58,'GroupsController','visHold'),(59,'GroupsController','visAlle'),(60,'ParticipantController','main'),(61,'ParticipantController','karmaStatus'),(62,'ParticipantController','visDeltager'),(63,'ParticipantController','visAlle'),(64,'ParticipantController','listForSchedule'),(65,'ParticipantController','listForGroup'),(66,'ParticipantController','showSearch'),(67,'ParticipantController','updateDeltager'),(68,'ParticipantController','updateDeltagerNote'),(69,'ParticipantController','listGMs'),(70,'ParticipantController','karmaList'),(71,'ParticipantController','showBoughtFood'),(72,'ParticipantController','printList'),(73,'ParticipantController','spillerSedler'),(74,'DeltagerController','economyBreakdown'),(75,'WearController','main'),(76,'WearController','showTypes'),(77,'WearController','wearBreakdown'),(78,'WearController','detailedOrderListPrint'),(79,'WearController','detailedOrderList'),(80,'WearController','detailedMiniList'),(81,'WearController','ajaxGetWear'),(82,'WearController','showWear'),(83,'EntranceController','*'),(84,'FoodController','displayHandout'),(85,'FoodController','displayHandoutAjax'),(86,'ParticipantController','showSearchResult'),(87,'ParticipantController','ajaxParameterSearch'),(88,'ParticipantController','ajaxUserSearch'),(89,'ParticipantController','ajaxlist'),(90,'ActivityController','main'),(91,'ActivityController','visAlle'),(92,'ActivityController','visAktivitet'),(93,'ActivityController','gameStartDetails'),(94,'EconomyController','*'),(95,'ShopController','*'),(97,'GraphController','*'),(98,'BoardgamesController','*'),(99,'ActivityController','showVotingStats'),(100,'IdTemplateController','renderIdCards'),(101,'GdsController','main'),(102,'GdsController','viewDay'),(103,'GdsController','ajaxGetGDSTider'),(104,'GdsController','ajaxGetGDSPeriods'),(105,'GdsController','ajaxGetSignups'),(106,'GdsController','listShifts'),(107,'GdsController','getShiftSuggestions'),(108,'GdsController','showShiftParticipants'),(109,'LoansController','*');
/*!40000 ALTER TABLE `privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (7,'admin','Administrator role - all powerful'),(8,'superuser','Access to nigh everything'),(9,'Infonaut','Generel power-role'),(10,'Food-handout','Food handout'),(11,'Activity-admin','Access to activity related stuff'),(12,'Wear-admin','Wear administrator'),(13,'Read-only','Look but not touch'),(14,'Read-only activity','Look but not touch activities'),(15,'Bazar-admin','Can see info on activities and participants'),(16,'Food-admin','Can deal with food stuff'),(17,'Participant admin','Can make changes to participants'),(18,'SMS','Privilege of sending SMS messages'),(19,'Boardgame admin','Handles the boardgame app'),(20,'Setup admin','In charge of Fastaval setup'),(21,'SetupTest','Fastaval Setup+'),(22,'GDS readonly','Readonly access to GDS');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_privileges`
--

DROP TABLE IF EXISTS `roles_privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_privileges` (
  `role_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`privilege_id`),
  KEY `privilege_id` (`privilege_id`),
  CONSTRAINT `roles_privileges_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `roles_privileges_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_privileges`
--

LOCK TABLES `roles_privileges` WRITE;
/*!40000 ALTER TABLE `roles_privileges` DISABLE KEYS */;
INSERT INTO `roles_privileges` VALUES (7,44),(8,44),(8,45),(9,45),(11,45),(15,45),(8,46),(9,46),(11,46),(12,46),(15,46),(17,46),(8,47),(9,47),(9,48),(11,48),(8,49),(9,49),(10,49),(11,49),(12,49),(13,49),(14,49),(15,49),(16,49),(19,49),(20,49),(22,49),(8,50),(9,50),(11,50),(12,50),(13,50),(14,50),(15,50),(16,50),(8,51),(9,51),(11,51),(20,51),(21,51),(8,52),(9,52),(16,52),(8,53),(9,53),(12,53),(8,56),(9,56),(8,57),(8,58),(8,59),(13,60),(21,60),(13,62),(13,63),(21,63),(13,66),(8,83),(9,83),(10,84),(10,85),(13,86),(21,86),(13,87),(13,88),(21,88),(13,89),(21,89),(14,90),(14,91),(14,92),(14,93),(9,94),(8,95),(13,97),(19,97),(19,98),(14,99),(9,100),(22,101),(22,102),(22,103),(22,104),(22,105),(22,106),(22,107),(22,108),(9,109);
/*!40000 ALTER TABLE `roles_privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules_votes`
--

DROP TABLE IF EXISTS `schedules_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedules_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `code` char(8) NOT NULL,
  `cast_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_code` (`schedule_id`,`code`),
  CONSTRAINT `schedules_votes_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `afviklinger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6423 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules_votes`
--

LOCK TABLES `schedules_votes` WRITE;
/*!40000 ALTER TABLE `schedules_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopevents`
--

DROP TABLE IF EXISTS `shopevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('cost','price','stock','sold') NOT NULL,
  `shopproduct_id` int(10) unsigned NOT NULL,
  `amount` decimal(20,5) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`shopproduct_id`),
  CONSTRAINT `shopevents_ibfk_1` FOREIGN KEY (`shopproduct_id`) REFERENCES `shopproducts` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopevents`
--

LOCK TABLES `shopevents` WRITE;
/*!40000 ALTER TABLE `shopevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `shopevents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopproducts`
--

DROP TABLE IF EXISTS `shopproducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopproducts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` varchar(16) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_u` (`name`),
  UNIQUE KEY `code_u` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopproducts`
--

LOCK TABLES `shopproducts` WRITE;
/*!40000 ALTER TABLE `shopproducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `shopproducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smslog`
--

DROP TABLE IF EXISTS `smslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nummer` bigint(20) unsigned DEFAULT NULL,
  `deltager_id` int(11) NOT NULL,
  `sendt` datetime NOT NULL,
  `besked` text NOT NULL,
  `return_val` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smslog`
--

LOCK TABLES `smslog` WRITE;
/*!40000 ALTER TABLE `smslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `smslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(64) NOT NULL,
  `row_id` int(11) NOT NULL,
  `english` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table` (`table`,`field`,`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `disabled` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `password_reset_hash` varchar(32) NOT NULL DEFAULT '',
  `password_reset_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'peter.e.lind@gmail.com','$2y$10$LVCn0lpFfHPVViCjKXzmZ.2lPkAFY0BEaBYIkK7x7sfUCHG4nBxLW','nej','54d0fd83f0eb8f9127985f55af999046','2015-11-23 22:04:05'),(48,'ego@kristoffermads.dk','$2y$10$AgYJtphpQj3iCZOBUDdUWu7ONdbgwBy6HC76WC3xLHH9K1panbqOO','ja','e89edf7be769a787769c258b49861711','2018-01-20 20:55:10'),(49,'afrostb@hotmail.com','$2y$10$ueY1k9vAqIaOLVx2WAzG8.yc6x5/llPkqCERM48auY2Hh36AyUfcC','ja','5113163d642f35ab323ed611e989f8f6','0000-00-00 00:00:00'),(52,'ak47@762.dk','$2y$10$zcoxGD.OQCCHyu0HACQ8TOtOUr5ShMU8IXORVdSQMdADyG5RtC2cG','nej','630919aae8f3bd285c52c0d3463e71c1','0000-00-00 00:00:00'),(54,'charlesbn@gmail.com','$2y$10$W3qJTpzBVdVrLYUuQwiqveopcjTfy3BwXekSVu1EbQuVPqhW9UvdS','ja','b972d125d8a59aeec76e31f8cb5f9332','2018-01-24 22:19:32'),(59,'shamgeez@gmail.com','$2y$10$uRwXgoNjPjyNeWB3nEjQ1OECSJsJ0sU0VsgEp.l2.7DjpV7skEohu','ja','476791fc45f0fdd372b3b8b3943fda4d','0000-00-00 00:00:00'),(61,'andreasskovse@gmail.com','$2y$10$vFSTDAl7sGIa72FH5wRDi.WgU2oFacR6v2MJA268nL1M1lzCJMk5u','nej','c760ac0a8a31d0ae3149392a4bc12775','0000-00-00 00:00:00'),(85,'mad@fastaval.dk','$2y$10$Kik090oqpLC4ac.dt6FUVum80KmM2kDiLoTz1WgbLCKkRg2mBE5HO','ja','','0000-00-00 00:00:00'),(86,'jonas.rassing@gmail.com','$2y$10$osw3c3xEb9Y5vM3i/Rl1Vurhmthh1bacUUBDRN8bCgiqbA6kCVzd.','ja','fea7e671370960f6cc844dc860690bb5','0000-00-00 00:00:00'),(89,'karenkbs@hotmail.com','$2y$10$LxLwNzyDSaN6X93LTVdZB.NcjQzD803VC6WcD/IpjhEaJGlTSKnW2','ja','db5609c15d81749e1772eb6ee2961886','0000-00-00 00:00:00'),(90,'realmoller@gmail.com','$2y$10$9zCo61Hf7IVPvcIr0otzpedLtrcyc95Jsk24GZsmXzM5dZ5IjlDR6','ja','b084775f4d23d5c0f53dfaf7201274d3','0000-00-00 00:00:00'),(92,'ragnvald42@gmail.com','$2y$10$gskGoEqmK1NbJv4J/nYzPOkhDAa0zYEKFkVvIMQ5XkxxftRXP6j8C','ja','e49429ee66c4255f1eed3427674c08a4','0000-00-00 00:00:00'),(93,'pixi.ixiq@gmail.com','$2y$10$HYEFLJ1xm9voDzb5J9yNgOp82rpvagDXcCyCHMgSU94urhMPU99hS','ja','be7e45d5ad3efbe779bcd25af2cdbcd3','0000-00-00 00:00:00'),(95,'nynnebrandt0125@gmail.com','$2y$10$QNIAg19IctXF4Kl6qMNUcuTQ9vCrvOadnV1pef5w5qL.VYlemjgjS','ja','8aba46cfc89d5a0cc1470106b1f56263','0000-00-00 00:00:00'),(97,'edinsumar@gmail.com','f28160278d0b48d0d71ff50d8a07ab80','ja','','0000-00-00 00:00:00'),(102,'gdsreadonly@fastaval.dk','$2y$10$sLWIQe1HnNa1j1cyTNsu/u4C2EWkbLzW2GQHcSdUOKz0sIitQTdva','ja','','0000-00-00 00:00:00'),(103,'mendeskmc@gmail.com','$2y$10$JsRWFDawkmnhjbb9USlcKe25fmixK9K9hLe1VtF/3r.rWFm7fqUv.','ja','cff2c9e41b5986c2ea507829521a16d7','0000-00-00 00:00:00'),(104,'jonasjpg@hotmail.com','$2y$10$H34TBxKD0AkN8vqh1EzxD.0R9ow/UpI6vrmmgGMjTrUAMo4OLPg12','ja','fcdcc2af01b713c0ada3cd6fd7f0acb5','0000-00-00 00:00:00'),(107,'ninakongsdal88@hotmail.com','$2y$10$5U6HQDO8BbY0rzMB/ddKhupVjouqZJaYOJYKa3Sg6kC7ost7qAyuG','ja','a7c3c19dad6cf1ade0c408ab878bf63b','0000-00-00 00:00:00'),(109,'kalle.hunnerup@gmail.com','$2y$10$6B7.PvwWWb8akrxfIEiF8.gHG5jDn7DSABGpZ.gR22MSktl3L2oAi','ja','b2bd67ee928129253af9c944b661159f','0000-00-00 00:00:00'),(111,'Love-kvik@hotmail.com','$2y$10$cqNdCO0ZLXa2c8Eo6UuRLe3SWMiSa0u39ysCrsp22mLH0Bnt7Yl6m','ja','cdb01468e3f2e936b1c1a1a7569ef4d0','0000-00-00 00:00:00'),(112,'Magnusklaesoe@gmail.com','$2y$10$/y6b.CRtudCFcdFXq5m15eFNihEU.mKbtJNrNfi5Eg.c0Z.enc4Pi','ja','32f41c6f99ee6c7084d971f85c51131c','0000-00-00 00:00:00'),(114,'kierans@live.dk','$2y$10$zVPsdELVPGwx25uLbthJcetetrOphzRsICKm2Bs8f8FHXJ/5CXRw.','ja','f1e202446c657b291f9fa1760c8af7bb','0000-00-00 00:00:00'),(115,'mikkel@onielsen.dk','$2y$10$IzHClnJeGs7/VmSTbfz/2e3UznFMNpvkFNwtWiPl74SozAu4Y1z6K','ja','fbdae5c1391bccdf5c2f533376e4d5cc','0000-00-00 00:00:00'),(117,'Regitsej@gmail.com','$2y$10$d7LB4MkpUPJWPFIvqIiQKOWOtw5VNrhTxC9AXfmPwF.BzH/evQRMO','ja','0520d2aaa6b1202c0f14f5cc534cc03e','0000-00-00 00:00:00'),(118,'braetspilscafeen@fastaval.dk','$2y$10$J/L7RdH7uLhtCtVv16lbne7cEz05TSjNM5hHVi.9xDlmkGFBIhuNm','ja','','0000-00-00 00:00:00'),(119,'sunel92@gmail.com','$2y$10$4dw4/WOS7QmC5He8fIZDkOFLoNKDgAMssXsSr7INkujuAuL8zv/I.','ja','69ab256a5e0da92ff2c98546ead91d04','0000-00-00 00:00:00'),(120,'mail@alacho.no','$2y$10$C9K5XnNYN3lJwwTQWGWPV.t6gKb4nudQKXer/I.19vPaRK29miJR.','ja','5f3ef46f7fa9297209420be22620afd4','0000-00-00 00:00:00'),(121,'Cecilietorp02@gmail.com','$2y$10$Zz8nlN6ZkwYTkTL2keHnjuBMWN/xrCnM4mvftCoeNKnYY/LVQ3QSK','ja','f3b2f40a13fe47f034b2f336b1cd99b4','0000-00-00 00:00:00'),(122,'Christian.ursell.smith@gmail.com','$2y$10$O79JI58Or.RDoaA7mBv0L.4tGCSlq/ob3jF9GJfofJO6bpO4c4vpy','ja','79d7fa86a215695f4adb1c6901348e97','0000-00-00 00:00:00'),(123,'ponsgaard@gmail.com','$2y$10$7hJCWbmV.du02vFtrw7tyOrApuYrILs1ZKr9lWx.Y4jpkRXP3SF5G','ja','ff66806471453a651eca765ce0410af8','0000-00-00 00:00:00'),(125,'1@makey.biz','$2y$10$qI6ESgX4AjuVEgI4JFcZfuNkmquxo9zw.F4lqvPHfyl390oGPVCHC','ja','2d8c78091b7e92f8d5f71d9585839551','0000-00-00 00:00:00'),(126,'abel.kathrine@gmail.com','$2y$10$a7y2v77Pf1qKDOetCMSvRuiXwAHQQOEu2vUz5H7ys6IoDUTZbI/w2','ja','6a7dd6ee74fa3d475866aa169ec040db','0000-00-00 00:00:00'),(127,'comfnug@gmail.com','$2y$10$QHyyoAXK2kWslfQKLihnouPJAWmfdCLLwpFkqFPu.9WDkRqy5bFYG','ja','d4e94328ff613b46a2c9a220b7277fc4','0000-00-00 00:00:00'),(128,'lauragerlev@gmail.com','$2y$10$22aFsAzjxgF4u28/GSfZ6.J40TxMvJRssWmihErVlryKY/nMgWdju','ja','c5b2d1f3d0fe6dc32153a99fec0c2bfd','0000-00-00 00:00:00'),(129,'Bjarke@fimbc.dk','$2y$10$ihK7So4yyWB4hvWNZz.AT.a7zcuup0GUNYeswvvmx6FWdGeQiQESC','ja','9e083524c74893f5878fe3d8a78845e6','0000-00-00 00:00:00'),(130,'uthalex@gmail.com','$2y$10$3FAcDSGEyq6AWu4zCoKsk.2IUqiQhCV2zHYfQMnop8515eypJt7CW','ja','a61f22c8a14f942d9bd7c8394c1b70d4','0000-00-00 00:00:00'),(131,'simonsteen@yahoo.dk','$2y$10$JubFf7IEmQlIjKH8CS2lIer3msdhW2ZB90/Fafb2.0I6lquNZmLfO','ja','8a5afbc12ca0291cad4a406031dc4291','0000-00-00 00:00:00'),(137,'vovhund2013@hotmail.com','$2y$10$kyjM6TgRg2XyRHpf3PbTZONsCDQI1mZUSuNUvn2b.prugQoJZAgeC','ja','cb8cae675ce8faa1b87d7c572ab1f47b','0000-00-00 00:00:00'),(138,'hilde.a.koch@gmail.com','$2y$10$gpI12zVaUlGOn9p1WT89Iu0rp6t3uoekTvcKoJhdSky1ScM8RYFtG','ja','e3ed1712a1cecbb7efd163ecf3411082','0000-00-00 00:00:00'),(141,'k.kraft.privat@gmail.com','$2y$10$PbJ9m2GK9/yClbpzdQRApuoJb/S2BSSon2NEQTn0CYyyyOAkjyTH6','ja','e1434d09c904aabeb76b068bdcbe4cef','0000-00-00 00:00:00'),(142,'krandkr@hotmail.com','$2y$10$VQqNyOcYnlx6nu.pxkRy2utfWERi7NQCyFN6zPlrBLpECRe7BezM2','ja','d7d964aa88af81a1945df0cb675d88e5','0000-00-00 00:00:00'),(144,'Kristina.knudsen@gmail.com','$2y$10$/qn309ENHXhxcU9WiVXzpeaRiyOVAkDHXcX7UDNXMWzIGwItU2vou','ja','03c0becabd4d571f2fe12b73dedfaafd','0000-00-00 00:00:00'),(148,'stefan.jacob@outlook.dk','$2y$10$.iUHEc1dC.9TWBjX9oqASuw9TWAr3kB1V4mxGJI.H/zZng.OBjWLK','ja','da7d42cbd3a215443d5ecc0dd35d77fd','0000-00-00 00:00:00'),(149,'stineegeberg@gmail.com','d8578edf8458ce06fbc5bb76a58c5ca4','ja','e4b794b1660f866d7f56871867820988','0000-00-00 00:00:00'),(150,'carolineckkoren@gmail.com','$2y$10$9lSq67J4cBoSN4OmRexOMuIrg6f5.v.M1CyYx4gabp9sURh3vH3Qu','ja','c21afe264ebb64ead52f3b88d912b406','0000-00-00 00:00:00'),(151,'flakesdk@gmail.com','ad3aa0ea7eb2687c1e4eee32819b9ef5','ja','','0000-00-00 00:00:00'),(153,'lukasvesth@gmail.com','$2y$10$AKOgtccO0KbT3z9FUU0MrORsQ0cbR4dhYSYMlONWC..2mcEnK83vy','ja','c7a297c17ebbaf45c1fa516c0f9bc3f2','0000-00-00 00:00:00'),(155,'johsbusted@gmail.com','$2y$10$cxu.cJgLz.Ftsje1vToX.eFBIzjLZDjwMQZSbXEAzUA4oyez9KFcO','ja','6f818689d4af85e3cac3b9bb7a9fc7b3','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,7),(48,7),(52,7),(59,7),(107,7),(130,7),(150,7),(49,8),(54,8),(125,8),(126,8),(127,8),(128,8),(129,8),(131,8),(151,8),(59,9),(61,9),(86,9),(89,9),(90,9),(93,9),(95,9),(103,9),(104,9),(107,9),(109,9),(111,9),(112,9),(114,9),(115,9),(117,9),(119,9),(120,9),(121,9),(122,9),(130,9),(137,9),(138,9),(141,9),(142,9),(144,9),(148,9),(149,9),(153,9),(85,10),(49,11),(97,11),(123,11),(126,11),(127,11),(130,11),(131,11),(155,11),(86,13),(92,14),(155,14),(59,17),(59,18),(89,18),(90,18),(93,18),(95,18),(92,19),(118,19),(86,20),(86,22),(102,22);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wear`
--

DROP TABLE IF EXISTS `wear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wear` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(64) NOT NULL,
  `size_range` varchar(16) NOT NULL,
  `beskrivelse` text,
  `title_en` varchar(64) NOT NULL DEFAULT '',
  `description_en` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wear`
--

LOCK TABLES `wear` WRITE;
/*!40000 ALTER TABLE `wear` DISABLE KEYS */;
INSERT INTO `wear` VALUES (3,'Infonaut T-Shirt','S-3XL','','Infonaut T-Shirt',''),(7,'Hættetrøje med lynlås','XXS-2XL','','Hoodie with zipper',''),(18,'Fastakruset','M-M','','The Fastaval Mug',''),(21,'T-Shirt, blå','S-3XL','','T-Shirt, blue',''),(22,'Crew T-Shirt','S-3XL','','Crew T-Shirt',''),(23,'Kiosk T-Shirt','S-3XL','','Kiosk T-Shirt',''),(24,'Kaffekro T-Shirt','S-3XL','','CoffeInn T-Shirt',''),(25,'Junior T-Shirt','S-3XL','','Junior T-Shirt',''),(28,'Polo','XS-6XL','','Polo',''),(29,'Fastaval spillekort','M-M','','Fastaval Playing Cards',''),(30,'Fastaval Sildesalat','M-M','Tilmedlingen til årets Fastaval sild, (Kun for nuværende og forhåndværende arrangør)','The Fastaval Ribbons','Registration for this year'),(31,'T-Shirt, sort','S-6XL','','T-Shirt, black','');
/*!40000 ALTER TABLE `wear` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wearpriser`
--

DROP TABLE IF EXISTS `wearpriser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wearpriser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wear_id` int(11) NOT NULL,
  `brugerkategori_id` int(11) NOT NULL,
  `pris` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wear_id` (`wear_id`,`brugerkategori_id`),
  KEY `brugerkategori_id` (`brugerkategori_id`),
  CONSTRAINT `wearpriser_ibfk_1` FOREIGN KEY (`wear_id`) REFERENCES `wear` (`id`),
  CONSTRAINT `wearpriser_ibfk_2` FOREIGN KEY (`brugerkategori_id`) REFERENCES `brugerkategorier` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wearpriser`
--

LOCK TABLES `wearpriser` WRITE;
/*!40000 ALTER TABLE `wearpriser` DISABLE KEYS */;
INSERT INTO `wearpriser` VALUES (147,3,3,0),(169,18,1,75),(170,18,2,75),(171,18,8,75),(172,18,6,75),(173,18,4,75),(174,18,3,75),(175,18,9,75),(197,7,1,275),(198,7,2,275),(199,7,8,275),(216,7,3,275),(217,7,6,275),(218,7,4,275),(219,7,9,275),(231,7,10,275),(232,18,10,75),(233,18,11,75),(234,7,11,275),(246,21,9,125),(247,21,2,125),(248,21,8,125),(249,21,1,125),(250,21,6,125),(251,21,4,125),(252,21,3,125),(253,21,10,125),(254,21,11,125),(255,22,9,95),(256,22,2,95),(257,22,8,95),(259,22,6,95),(260,22,4,95),(261,22,3,95),(263,22,11,95),(267,23,9,0),(268,24,11,0),(282,28,9,175),(284,28,2,175),(285,28,8,175),(286,28,1,175),(287,28,6,175),(288,28,4,175),(289,28,7,175),(290,28,3,175),(291,28,11,175),(292,29,9,60),(294,29,2,60),(295,29,8,60),(296,29,1,60),(297,29,6,60),(298,29,4,60),(300,29,3,60),(301,29,10,60),(302,29,11,60),(303,30,9,0),(305,30,2,0),(306,30,8,0),(307,30,1,0),(308,30,11,0),(309,30,6,0),(310,30,4,0),(311,30,7,0),(312,30,3,0),(313,31,9,125),(314,31,2,125),(315,31,8,125),(316,31,1,125),(317,31,6,125),(318,31,4,125),(319,31,3,125),(320,31,10,125),(321,31,11,125),(322,7,12,275),(323,18,12,75),(324,21,12,125),(325,22,12,95),(326,28,12,175),(327,29,12,60),(328,30,12,0),(329,31,12,125),(330,25,12,0);
/*!40000 ALTER TABLE `wearpriser` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-26  7:24:26
