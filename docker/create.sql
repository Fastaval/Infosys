CREATE DATABASE infosys default character set utf8mb4;
GRANT ALL ON infosys.* to 'infosys'@'%' IDENTIFIED BY 'infosys';
FLUSH PRIVILEGES;
USE infosys;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activityageranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `requirementtype` enum('min','max','exact') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_requirement` (`activity_id`,`requirementtype`),
  CONSTRAINT `activityageranges_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `aktiviteter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=563 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityageranges`
--

LOCK TABLES `activityageranges` WRITE;
/*!40000 ALTER TABLE `activityageranges` DISABLE KEYS */;
INSERT INTO `activityageranges` VALUES (84,192,10,'min'),(87,204,16,'min'),(89,205,14,'min'),(90,206,14,'min'),(92,194,12,'min'),(93,196,10,'min'),(94,198,10,'min'),(95,200,8,'min'),(97,202,10,'min'),(99,207,14,'min'),(100,208,10,'min'),(102,209,14,'min'),(103,210,8,'min'),(104,211,12,'min'),(105,212,14,'min'),(106,213,13,'min'),(108,214,14,'min'),(109,215,12,'min'),(110,216,12,'min'),(112,217,8,'min'),(113,219,14,'min'),(114,220,14,'min'),(116,221,14,'min'),(117,223,8,'min'),(119,222,8,'max'),(120,222,6,'min'),(125,226,8,'max'),(126,226,6,'min'),(127,227,8,'min'),(128,228,8,'max'),(129,228,6,'min'),(130,229,8,'min'),(131,230,8,'max'),(132,230,6,'min'),(133,231,18,'min'),(134,233,11,'max'),(135,233,9,'min'),(136,236,11,'max'),(137,236,9,'min'),(138,234,8,'min'),(139,238,11,'max'),(140,238,9,'min'),(141,237,14,'min'),(142,240,11,'max'),(143,240,9,'min'),(144,241,11,'max'),(145,241,9,'min'),(146,243,11,'max'),(147,243,9,'min'),(148,244,11,'max'),(149,244,9,'min'),(150,245,11,'max'),(151,245,9,'min'),(152,246,14,'max'),(153,246,12,'min'),(155,248,14,'max'),(156,248,12,'min'),(157,247,14,'max'),(158,247,12,'min'),(161,249,14,'max'),(162,249,12,'min'),(165,250,14,'max'),(166,250,12,'min'),(167,251,14,'max'),(168,251,12,'min'),(169,252,15,'min'),(180,253,18,'min'),(182,254,18,'min'),(183,255,15,'min'),(188,258,13,'min'),(189,259,13,'min'),(190,260,15,'min'),(191,261,17,'min'),(192,262,15,'min'),(193,263,15,'min'),(194,265,13,'min'),(195,266,15,'min'),(196,268,13,'min'),(197,269,13,'min'),(198,271,13,'min'),(200,267,13,'min'),(201,273,13,'min'),(202,275,18,'min'),(203,276,15,'min'),(204,277,13,'min'),(205,278,15,'min'),(206,279,13,'min'),(207,280,13,'min'),(208,281,5,'min'),(220,283,15,'min'),(238,297,15,'min'),(242,299,18,'min'),(250,306,13,'min'),(255,224,8,'max'),(256,224,6,'min'),(259,232,11,'max'),(260,232,9,'min'),(307,307,13,'min'),(310,308,13,'min'),(311,309,13,'min'),(353,310,15,'min'),(360,312,15,'min'),(361,311,13,'min'),(362,313,15,'min'),(363,314,15,'min'),(368,282,15,'min'),(533,354,100,'max'),(534,354,18,'min'),(539,355,100,'max'),(540,356,99,'max'),(543,357,99,'max'),(545,358,99,'max'),(546,359,99,'max'),(548,360,99,'max'),(549,362,99,'max'),(550,363,99,'max'),(553,364,99,'max'),(555,365,100,'max'),(556,366,100,'max'),(559,367,99,'max'),(561,368,100,'max'),(562,369,100,'max');
/*!40000 ALTER TABLE `activityageranges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `afviklinger`
--

DROP TABLE IF EXISTS `afviklinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=668 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger`
--

--
-- Table structure for table `afviklinger_multiblok`
--

DROP TABLE IF EXISTS `afviklinger_multiblok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `afviklinger_multiblok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afvikling_id` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `slut` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periode_aktivitet` (`afvikling_id`,`start`,`slut`),
  CONSTRAINT `afviklinger_multiblok_ibfk_1` FOREIGN KEY (`afvikling_id`) REFERENCES `afviklinger` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger_multiblok`
--


--
-- Table structure for table `aktiviteter`
--

DROP TABLE IF EXISTS `aktiviteter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=370 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aktiviteter`
--


--
-- Table structure for table `api_auth`
--

DROP TABLE IF EXISTS `api_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `boardgameevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `boardgame_id` int(10) unsigned NOT NULL,
  `type` enum('created','borrowed','returned','finished','present','not-present') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `boardgame` (`boardgame_id`),
  CONSTRAINT `boardgameevents_ibfk_1` FOREIGN KEY (`boardgame_id`) REFERENCES `boardgames` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8272 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgameevents`
--

--
-- Table structure for table `boardgames`
--

DROP TABLE IF EXISTS `boardgames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `boardgames` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `designergame` tinyint(4) NOT NULL DEFAULT '0',
  `bgg_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4891 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgames`
--


--
-- Table structure for table `brugerkategorier`
--

DROP TABLE IF EXISTS `brugerkategorier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `brugerkategorier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(256) NOT NULL,
  `arrangoer` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `beskrivelse` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `brugerkategorier_idtemplates` (
  `template_id` int(10) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`template_id`,`category_id`),
  KEY `category` (`category_id`),
  CONSTRAINT `brugerkategorier_idtemplates_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `brugerkategorier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `brugerkategorier_idtemplates_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `idtemplates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brugerkategorier_idtemplates`
--


--
-- Table structure for table `deltagere`
--

DROP TABLE IF EXISTS `deltagere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1013 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere`
--


--
-- Table structure for table `deltagere_gdstilmeldinger`
--

DROP TABLE IF EXISTS `deltagere_gdstilmeldinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `deltagere_gdstilmeldinger` (
  `deltager_id` int(11) NOT NULL,
  `period` char(17) NOT NULL DEFAULT '',
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`deltager_id`,`period`,`category_id`),
  KEY `period` (`period`),
  KEY `gdscategory_fk` (`category_id`),
  CONSTRAINT `deltagere_gdstilmeldinger_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `gdscategory_fk` FOREIGN KEY (`category_id`) REFERENCES `gdscategories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_gdstilmeldinger`
--

--
-- Table structure for table `deltagere_gdsvagter`
--

DROP TABLE IF EXISTS `deltagere_gdsvagter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `deltagere_gdsvagter` (
  `deltager_id` int(11) NOT NULL,
  `gdsvagt_id` int(11) NOT NULL,
  `noshow` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`deltager_id`,`gdsvagt_id`),
  KEY `gdsvagt_id` (`gdsvagt_id`),
  CONSTRAINT `deltagere_gdsvagter_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_gdsvagter_ibfk_2` FOREIGN KEY (`gdsvagt_id`) REFERENCES `gdsvagter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_gdsvagter`
--


--
-- Table structure for table `deltagere_indgang`
--

DROP TABLE IF EXISTS `deltagere_indgang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `deltagere_indgang` (
  `deltager_id` int(11) NOT NULL,
  `indgang_id` int(11) NOT NULL,
  PRIMARY KEY (`deltager_id`,`indgang_id`),
  KEY `indgang_id` (`indgang_id`),
  CONSTRAINT `deltagere_indgang_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_indgang_ibfk_2` FOREIGN KEY (`indgang_id`) REFERENCES `indgang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_indgang`
--

--
-- Table structure for table `deltagere_madtider`
--

DROP TABLE IF EXISTS `deltagere_madtider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `deltagere_madtider` (
  `deltager_id` int(11) NOT NULL,
  `madtid_id` int(11) NOT NULL,
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `time_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`deltager_id`,`madtid_id`),
  KEY `madtid_id` (`madtid_id`),
  CONSTRAINT `deltagere_madtider_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_madtider_ibfk_2` FOREIGN KEY (`madtid_id`) REFERENCES `madtider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_madtider`
--


--
-- Table structure for table `deltagere_tilmeldinger`
--

DROP TABLE IF EXISTS `deltagere_tilmeldinger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `deltagere_tilmeldinger` (
  `deltager_id` int(11) NOT NULL,
  `prioritet` int(11) NOT NULL,
  `afvikling_id` int(11) NOT NULL,
  `tilmeldingstype` enum('spiller','spilleder') NOT NULL DEFAULT 'spiller',
  PRIMARY KEY (`deltager_id`,`prioritet`,`afvikling_id`),
  KEY `afvikling_id` (`afvikling_id`),
  CONSTRAINT `deltagere_tilmeldinger_ibfk_1` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `deltagere_tilmeldinger_ibfk_2` FOREIGN KEY (`afvikling_id`) REFERENCES `afviklinger` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_tilmeldinger`
--


--
-- Table structure for table `deltagere_wear`
--

DROP TABLE IF EXISTS `deltagere_wear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere_wear`
--


--
-- Table structure for table `diyageranges`
--

DROP TABLE IF EXISTS `diyageranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `diyageranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diy_id` int(11) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `requirementtype` enum('min','max','exact') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `diy_requirement` (`diy_id`,`requirementtype`),
  CONSTRAINT `diyageranges_ibfk_1` FOREIGN KEY (`diy_id`) REFERENCES `gds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `gamestarts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `datetime` (`datetime`),
  KEY `user_id_fk` (`user_id`),
  CONSTRAINT `gamestarts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `deltagere` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestarts`
--


--
-- Table structure for table `gamestartschedules`
--

DROP TABLE IF EXISTS `gamestartschedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestartschedules`
--


--
-- Table structure for table `gds`
--

DROP TABLE IF EXISTS `gds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `gdscategories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_da` varchar(64) NOT NULL,
  `name_en` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `gdsvagter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gds_id` int(11) NOT NULL,
  `antal_personer` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `slut` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gds_id` (`gds_id`,`start`),
  CONSTRAINT `gdsvagter_ibfk_1` FOREIGN KEY (`gds_id`) REFERENCES `gds` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=684 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hold`
--

LOCK TABLES `hold` WRITE;
/*!40000 ALTER TABLE `hold` DISABLE KEYS */;
INSERT INTO `hold` VALUES (4,519,1,28),(5,468,1,29),(6,468,2,30),(7,468,3,31),(17,509,1,66),(18,509,3,17),(19,509,2,59),(21,509,5,60),(23,459,1,17),(24,509,4,61),(25,459,3,60),(26,459,2,61),(27,459,5,59),(28,509,7,15),(29,459,4,66),(30,459,7,8),(32,459,6,10),(33,462,1,32),(34,482,1,25),(35,482,3,27),(37,482,2,28),(38,462,2,74),(39,462,5,75),(40,462,4,35),(41,505,1,29),(42,464,1,16),(43,505,3,30),(44,505,2,31),(45,464,3,15),(46,505,5,32),(47,464,2,14),(48,505,4,74),(49,505,7,34),(50,464,5,13),(51,505,6,35),(53,464,4,12),(54,464,7,11),(55,464,6,27),(58,507,1,8),(59,464,8,25),(60,507,3,9),(62,466,1,55),(63,518,1,10),(64,518,3,11),(65,466,3,28),(66,518,2,12),(67,518,5,13),(68,466,2,9),(69,518,4,14),(71,518,6,16),(72,470,1,18),(73,470,3,20),(74,520,1,18),(75,470,2,19),(76,520,3,19),(77,520,2,20),(78,470,5,21),(79,520,5,21),(80,520,4,22),(82,470,4,22),(84,470,6,23),(85,519,3,13),(86,519,2,27),(88,474,1,17),(89,474,3,61),(90,474,2,59),(91,474,5,60),(92,474,4,66),(93,472,1,8),(94,472,3,9),(95,472,2,10),(96,472,5,11),(97,472,4,12),(98,517,1,34),(99,517,3,55),(100,517,2,25),(101,517,5,29),(102,517,4,30),(103,457,1,31),(104,457,3,32),(105,457,2,35),(108,457,4,74),(109,457,7,75),(110,483,1,8),(111,483,3,9),(112,483,2,10),(113,487,1,75),(114,487,3,35),(115,487,2,74),(116,487,5,29),(117,487,4,30),(118,487,7,31),(119,487,6,32),(120,489,1,34),(121,489,3,27),(122,489,2,25),(123,489,5,28),(124,491,1,13),(125,491,3,14),(126,491,2,55),(127,491,5,81),(128,493,1,17),(129,493,3,61),(130,493,2,59),(131,493,5,60),(132,493,4,66),(133,485,1,18),(134,485,2,20),(135,485,3,19),(136,485,4,21),(137,485,5,22),(138,485,6,23),(139,485,7,11),(140,485,8,12),(141,485,9,15),(143,502,1,55),(144,502,2,46),(145,502,4,48),(146,502,3,47),(148,467,1,18),(149,467,3,81),(150,467,2,20),(151,471,1,17),(152,471,3,61),(153,471,2,60),(154,471,5,59),(155,471,4,66),(156,500,1,25),(157,500,3,34),(158,500,2,27),(159,500,5,28),(160,469,1,21),(161,469,3,22),(162,469,2,23),(163,496,1,75),(164,496,3,74),(165,496,2,35),(166,496,5,29),(167,496,4,30),(168,496,7,31),(169,496,6,32),(170,498,1,19),(171,498,3,8),(172,498,2,10),(173,498,5,9),(174,498,4,11),(175,498,7,12),(176,498,6,13),(177,498,9,14),(178,498,8,15),(179,498,11,16),(181,514,1,10),(182,514,3,25),(183,514,2,27),(186,508,1,18),(187,508,3,19),(188,508,2,20),(189,508,5,21),(190,508,4,22),(191,508,7,23),(192,504,1,29),(193,504,3,30),(194,504,2,31),(195,504,5,32),(196,504,4,35),(197,504,7,75),(198,504,6,74),(199,463,1,8),(200,463,3,9),(201,463,2,15),(202,463,5,16),(203,463,4,81),(204,474,7,14),(205,506,1,14),(206,506,3,13),(207,506,2,12),(208,506,5,11),(209,506,4,34),(210,481,1,17),(211,481,3,61),(212,481,2,59),(213,481,5,60),(214,481,4,66),(215,501,1,18),(216,501,3,19),(217,501,2,20),(218,501,5,21),(219,501,4,22),(220,501,7,23),(221,499,1,29),(222,499,3,30),(223,499,2,31),(224,499,5,32),(225,499,4,74),(226,499,7,34),(227,499,6,35),(228,499,9,75),(229,513,1,25),(230,513,3,27),(231,513,2,28),(232,497,1,66),(233,497,3,59),(234,497,2,60),(235,497,5,17),(236,497,4,61),(237,497,7,55),(238,497,6,81),(239,511,1,8),(240,511,3,9),(241,511,2,10),(242,511,5,11),(243,511,4,12),(244,511,7,13),(245,511,6,14),(246,511,9,15),(247,511,8,16),(248,510,1,29),(249,510,3,30),(250,510,2,31),(251,510,5,32),(252,510,4,74),(253,510,7,34),(254,510,6,35),(255,510,9,75),(256,510,8,25),(257,510,11,27),(258,510,10,28),(259,510,13,55),(260,475,1,21),(261,475,3,22),(262,475,2,23),(263,473,1,18),(264,473,3,19),(265,473,2,20),(266,473,5,8),(267,473,4,9),(268,512,1,10),(269,512,3,11),(270,512,2,15),(271,512,5,16),(272,458,1,12),(273,458,3,13),(274,458,2,14),(276,465,1,66),(277,465,3,59),(278,465,2,60),(279,465,5,17),(280,465,4,61),(281,534,1,29),(282,534,3,34),(283,534,2,35),(284,534,5,75),(285,494,1,30),(286,494,3,31),(287,494,2,32),(288,494,5,74),(289,484,1,21),(290,484,3,22),(291,484,2,23),(293,490,1,20),(294,490,3,19),(295,490,2,18),(296,488,1,66),(297,488,3,59),(298,488,2,60),(299,488,5,17),(300,488,4,61),(301,488,7,55),(302,486,1,8),(303,486,3,9),(304,486,2,10),(305,486,5,11),(306,486,4,12),(307,486,7,13),(308,486,6,14),(309,486,9,15),(310,486,8,16),(311,486,11,27),(312,486,10,25),(314,486,12,28),(317,523,1,59),(318,523,3,60),(319,523,2,17),(320,522,1,66),(321,522,3,16),(322,522,2,15),(323,526,1,14),(326,526,2,25),(327,503,1,55),(328,503,2,46),(329,503,3,47),(330,503,4,48),(331,503,5,49),(332,451,1,63),(333,452,1,63),(334,453,1,63),(335,449,1,15),(340,419,1,8),(342,429,1,73),(343,423,1,73),(344,423,2,73),(345,428,1,73),(346,428,2,73),(347,422,1,73),(348,422,2,73),(349,422,3,73),(350,424,1,73),(352,425,1,73),(353,425,2,73),(356,427,1,73),(357,427,2,73),(358,426,1,73),(359,426,2,73),(361,431,1,73),(362,432,1,73),(363,433,1,73),(364,434,1,73),(365,437,1,73),(366,436,1,73),(368,527,1,38),(370,391,1,3),(371,391,2,3),(372,391,3,3),(373,391,4,1),(374,366,1,3),(375,366,2,3),(376,366,3,3),(377,368,1,3),(378,368,2,3),(379,368,3,3),(380,388,1,3),(381,388,2,3),(382,388,3,3),(383,389,1,3),(384,389,2,3),(385,390,1,3),(386,390,2,3),(387,408,1,3),(388,408,2,3),(389,406,1,3),(390,406,2,3),(391,529,1,3),(392,407,1,3),(393,385,1,3),(394,385,2,3),(395,384,1,3),(396,387,1,3),(397,386,1,3),(398,386,2,3),(399,398,1,3),(400,400,1,3),(401,355,1,3),(402,355,2,3),(403,355,3,3),(405,353,1,3),(406,353,2,3),(407,353,3,3),(409,354,1,3),(410,354,2,3),(411,354,3,3),(412,530,1,3),(413,530,2,3),(414,530,3,3),(415,530,4,3),(416,530,5,3),(417,532,1,3),(418,532,2,3),(419,532,3,3),(420,531,1,3),(421,531,2,3),(422,357,1,3),(423,357,2,3),(424,359,1,3),(425,360,1,3),(426,347,1,1),(427,347,2,1),(428,349,1,3),(429,351,1,3),(433,381,1,3),(434,381,2,3),(435,382,1,3),(436,383,1,3),(437,395,1,3),(438,395,2,3),(439,395,3,3),(441,396,1,3),(442,396,2,3),(443,397,1,3),(444,373,1,3),(445,373,2,3),(446,374,1,3),(447,375,1,3),(448,376,1,3),(449,375,2,3),(450,377,1,3),(451,377,2,3),(452,378,1,3),(453,378,2,3),(454,380,1,3),(455,379,1,3),(456,379,2,3),(457,393,1,3),(458,394,1,3),(459,392,1,3),(461,361,1,3),(462,361,2,3),(463,361,3,3),(464,362,1,3),(465,362,2,3),(466,362,3,3),(467,363,1,3),(468,363,2,3),(469,363,3,3),(470,369,1,3),(471,369,2,3),(472,369,3,3),(473,371,1,3),(474,371,2,3),(475,371,3,3),(476,372,1,3),(477,372,2,3),(479,421,1,81),(480,334,1,3),(481,411,1,3),(482,412,1,3),(483,416,1,3),(484,533,1,3),(485,535,1,3),(486,415,1,3),(487,410,1,3),(488,414,1,1),(489,409,1,3),(490,413,1,3),(491,417,1,3),(492,445,1,65),(493,446,1,65),(494,441,1,65),(495,443,1,65),(496,442,1,1),(497,444,1,65),(498,447,1,65),(499,448,1,1),(500,440,1,82),(501,440,2,82),(502,439,1,82),(503,439,2,82),(504,430,1,12),(505,430,2,13),(506,430,3,55),(507,420,1,11),(508,420,2,10),(510,430,4,9),(511,418,1,83),(512,516,1,18),(513,516,2,19),(514,516,3,20),(515,516,4,21),(518,516,5,22),(520,536,1,18),(521,537,1,19),(522,538,1,2),(523,559,1,2),(524,557,1,4),(525,516,6,23),(526,552,1,4),(527,553,1,5),(528,543,1,5),(529,539,1,7),(530,554,1,7),(531,546,1,15),(532,555,1,15),(533,545,1,16),(534,556,1,16),(535,547,1,18),(536,542,1,20),(537,549,1,19),(538,344,1,21),(539,540,1,22),(540,550,1,20),(542,548,1,22),(543,544,1,23),(544,558,1,23),(545,541,1,36),(546,551,1,39),(547,560,1,36),(549,450,1,24),(550,335,1,1),(551,333,1,4),(552,360,2,1),(554,522,4,28),(555,522,5,61),(556,372,3,1),(557,376,2,1),(558,397,2,1),(559,396,3,1),(560,383,2,1),(561,382,2,1),(563,349,2,1),(564,359,2,1),(565,532,4,3),(566,531,3,1),(567,531,4,1),(568,487,8,2),(569,487,9,4),(570,487,10,5),(571,485,10,16),(572,485,11,52),(573,491,6,36),(574,491,7,39),(575,491,8,43),(597,582,1,75),(598,583,1,14),(599,584,1,17),(600,585,1,75),(601,586,1,8),(602,587,1,13),(603,588,1,66),(604,589,1,19),(605,590,1,1),(606,591,1,30),(607,592,1,22),(608,593,1,20),(609,594,1,17),(610,595,1,11),(611,596,1,18),(612,597,1,17),(613,598,1,23),(614,599,1,17),(615,600,1,27),(616,601,1,9),(617,602,1,23),(618,603,1,15),(619,604,1,15),(620,605,1,30),(621,606,1,66),(622,607,1,27),(623,608,1,20),(624,609,1,14),(625,610,1,17),(626,611,1,74),(627,612,1,30),(628,613,1,66),(629,614,1,8),(630,615,1,75),(631,616,1,27),(632,617,1,23),(633,618,1,55),(634,619,1,55),(635,620,1,30),(636,621,1,75),(637,622,1,14),(638,623,1,8),(639,624,1,19),(640,625,1,66),(641,626,1,75),(642,627,1,8),(643,628,1,10),(644,629,1,27),(645,630,1,27),(646,631,1,19),(647,632,1,25),(648,633,1,12),(649,634,1,27),(650,635,1,23),(654,639,1,75),(655,493,6,44),(656,493,7,45),(657,493,8,46),(658,483,4,47),(659,483,5,48),(660,489,6,49),(661,514,4,28),(662,514,5,55),(663,462,6,34),(664,485,12,50),(665,485,13,7),(671,640,1,81),(672,641,1,81),(675,652,1,1),(677,653,1,1),(678,654,1,1),(679,655,1,1),(680,656,1,1),(681,657,1,1),(682,658,1,1),(683,659,1,1);
/*!40000 ALTER TABLE `hold` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `idtemplates`
--

DROP TABLE IF EXISTS `idtemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `idtemplates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `background` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11247 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `indgang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pris` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `type` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `loanevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loanitem_id` int(10) unsigned NOT NULL,
  `type` enum('created','borrowed','returned','finished') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `loanitem` (`loanitem_id`),
  CONSTRAINT `loanevents_ibfk_1` FOREIGN KEY (`loanitem_id`) REFERENCES `loanitems` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanevents`
--


--
-- Table structure for table `loanitems`
--

DROP TABLE IF EXISTS `loanitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `loanitems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanitems`
--


--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `message` varchar(256) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40285 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--


--
-- Table structure for table `lokaler`
--

DROP TABLE IF EXISTS `lokaler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `lokaler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beskrivelse` varchar(256) DEFAULT NULL,
  `omraade` varchar(32) DEFAULT NULL,
  `skole` varchar(64) NOT NULL,
  `kan_bookes` enum('ja','nej') NOT NULL DEFAULT 'ja',
  `sovelokale` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `sovekapacitet` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `mad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori` varchar(64) NOT NULL,
  `pris` int(11) NOT NULL,
  `title_en` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `madtider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mad_id` int(11) NOT NULL,
  `dato` datetime NOT NULL,
  `description_da` varchar(128) NOT NULL DEFAULT '',
  `description_en` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mad_id` (`mad_id`,`dato`),
  CONSTRAINT `madtider_ibfk_1` FOREIGN KEY (`mad_id`) REFERENCES `mad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `notes` (
  `area` enum('shop','boardgames','loans') NOT NULL,
  `note` text NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`area`),
  UNIQUE KEY `area` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--


--
-- Table structure for table `participantidtemplates`
--

DROP TABLE IF EXISTS `participantidtemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `participantidtemplates` (
  `participant_id` int(11) NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`participant_id`),
  KEY `template` (`template_id`),
  CONSTRAINT `participantidtemplates_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `participantidtemplates_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `idtemplates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantidtemplates`
--


--
-- Table structure for table `participantpaymenthashes`
--

DROP TABLE IF EXISTS `participantpaymenthashes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `participantpaymenthashes` (
  `participant_id` int(11) NOT NULL,
  `hash` char(32) NOT NULL,
  PRIMARY KEY (`participant_id`),
  CONSTRAINT `participantpaymenthashes_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantpaymenthashes`
--


--
-- Table structure for table `participantphotoidentifiers`
--

DROP TABLE IF EXISTS `participantphotoidentifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `participantphotoidentifiers` (
  `participant_id` int(11) NOT NULL,
  `identifier` tinytext NOT NULL,
  PRIMARY KEY (`participant_id`),
  CONSTRAINT `participantphotoidentifiers_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantphotoidentifiers`
--


--
-- Table structure for table `participants_sleepingplaces`
--

DROP TABLE IF EXISTS `participants_sleepingplaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `participants_sleepingplaces` (
  `participant_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `starts` datetime NOT NULL,
  `ends` datetime NOT NULL,
  PRIMARY KEY (`participant_id`,`room_id`,`starts`),
  KEY `room_fk` (`room_id`),
  CONSTRAINT `participants_sleepingplaces_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `participants_sleepingplaces_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `lokaler` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants_sleepingplaces`
--


--
-- Table structure for table `paymentfritidlog`
--

DROP TABLE IF EXISTS `paymentfritidlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `paymentfritidlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `participant_id` int(11) NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `cost` int(10) unsigned NOT NULL,
  `fees` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=775 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentfritidlog`
--


--
-- Table structure for table `pladser`
--

DROP TABLE IF EXISTS `pladser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pladser`
--


--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(128) NOT NULL,
  `method` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `roles_privileges` (
  `role_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`privilege_id`),
  KEY `privilege_id` (`privilege_id`),
  CONSTRAINT `roles_privileges_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `roles_privileges_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `schedules_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `code` char(8) NOT NULL,
  `cast_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_code` (`schedule_id`,`code`),
  CONSTRAINT `schedules_votes_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `afviklinger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6423 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules_votes`
--


--
-- Table structure for table `shopevents`
--

DROP TABLE IF EXISTS `shopevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `shopevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('cost','price','stock','sold') NOT NULL,
  `shopproduct_id` int(10) unsigned NOT NULL,
  `amount` decimal(20,5) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`shopproduct_id`),
  CONSTRAINT `shopevents_ibfk_1` FOREIGN KEY (`shopproduct_id`) REFERENCES `shopproducts` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `shopproducts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` varchar(16) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_u` (`name`),
  UNIQUE KEY `code_u` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `smslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nummer` bigint(20) unsigned DEFAULT NULL,
  `deltager_id` int(11) NOT NULL,
  `sendt` datetime NOT NULL,
  `besked` text NOT NULL,
  `return_val` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1724 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smslog`
--


--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(64) NOT NULL,
  `row_id` int(11) NOT NULL,
  `english` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table` (`table`,`field`,`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `disabled` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `password_reset_hash` varchar(32) NOT NULL DEFAULT '',
  `password_reset_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@infosys.local','$2y$10$zpYNppQfVKmJaHoTlx/ClOPii4Iqm0rSJWVn33YWSU6ssU2mPtksO','nej','','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `users_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
CREATE TABLE `wear` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navn` varchar(64) NOT NULL,
  `size_range` varchar(16) NOT NULL,
  `beskrivelse` text,
  `title_en` varchar(64) NOT NULL DEFAULT '',
  `description_en` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4;
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
/*!40101 SET character_set_client =utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=utf8mb4;
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

-- Dump completed on 2018-10-23 20:30:02
