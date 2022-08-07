-- MySQL dump 10.13  Distrib 5.5.52, for debian-linux-gnu (i686)
--
-- Host: db    Database: infosys2016
-- ------------------------------------------------------
-- Server version	5.5.52-0+deb7u1-log

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
) ENGINE=InnoDB AUTO_INCREMENT=599 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
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
  `type` enum('braet','rolle','live','figur','workshop','ottoviteter','magic','system') NOT NULL,
  `tids_eksklusiv` enum('ja','nej') NOT NULL DEFAULT 'ja',
  `sprog` enum('dansk','engelsk','dansk+engelsk') NOT NULL DEFAULT 'dansk',
  `replayable` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `updated` datetime NOT NULL,
  `hidden` enum('ja','nej') NOT NULL DEFAULT 'nej',
  `karmatype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8;
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
  `type` enum('created','borrowed','returned','finished') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `boardgame` (`boardgame_id`),
  CONSTRAINT `boardgameevents_ibfk_1` FOREIGN KEY (`boardgame_id`) REFERENCES `boardgames` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2234 DEFAULT CHARSET=utf8;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=442 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brugerkategorier`
--

LOCK TABLES `brugerkategorier` WRITE;
/*!40000 ALTER TABLE `brugerkategorier` DISABLE KEYS */;
INSERT INTO `brugerkategorier` VALUES (1,'Deltager','nej',NULL),(2,'Arrangør','ja',NULL),(3,'Infonaut','ja',NULL),(4,'Forfatter','ja',NULL),(5,'Arrangrhangaround','nej','Arrangr hangaround'),(6,'DirtBuster','ja',NULL),(7,'Freeloaders','nej','Folk, der skal ha gratis ting'),(8,'Brandvagt','ja','Medlem af brandvagten'),(9,'Kioskninja','ja','Vagt i kiosken');
/*!40000 ALTER TABLE `brugerkategorier` ENABLE KEYS */;
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
  `gender` enum('m','k') NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `brugerkategori_id` (`brugerkategori_id`),
  KEY `rel_karma` (`rel_karma`),
  KEY `abs_karma` (`abs_karma`),
  KEY `sovelokale_id` (`sovelokale_id`),
  CONSTRAINT `deltagere_ibfk_1` FOREIGN KEY (`brugerkategori_id`) REFERENCES `brugerkategorier` (`id`),
  CONSTRAINT `deltagere_ibfk_2` FOREIGN KEY (`sovelokale_id`) REFERENCES `lokaler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=878 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gds`
--

LOCK TABLES `gds` WRITE;
/*!40000 ALTER TABLE `gds` DISABLE KEYS */;
INSERT INTO `gds` VALUES (1,'Fest-opsætning','Som navnet antyder så handler det om opsætning til den store Ottofest. Opstilling af borde, stole og pynt.','Kære deltager, din GDS-tjans er Banketopsætning, mødestedet er fællesområdet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-prepping','As the name implies this has to do with the setup of the great Otto party.  Mostly about moving tables and chair, and setting up decorations.',4,'Dear participant, your DIY-job is party-prepping Sunday. Meeting place is the big hall. It will be fun and we\'re happy that you\'ll be joining us - see you there!'),(2,'Brandvagt','En tjans for dig der er over 18 og har mobiltelefonen med. Sørg for at der ikke udbryder brand i sovesalen. Det gør der som regel ikke, så det er muligvis en god ide at have en bog med.','Kære deltager, din GDS-tjans er Brandvagt, mød op i infoen et kvarter før din vagt starter. Det bliver hyggeligt, og på forhånd tak for hjælpen. :)','Night watch','A job for the over-18 with a working mobile. All you need to do is make sure no fires break out - or, if they do, alert everyone and the fire department. You\'re welcome to keep company (in the form of friends, candy, books or all of the above) while on duty.',5,'Dear participant, your DIY-job is fireguard. Meeting place is the information 15 minutes before the shift starts. Thanks very much for your help - we look forward to seeing you!'),(4,'Kiosk','Opfyldning af varer, produktion af toasts og naturligvis kundepleje kan du få udvidet kendskab til i kiosken. En fantastisk måde at møde mennesker på.','Kære deltager, din GDS-tjans er Kiosk, mødestedet er kiosken. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Shop','Stocking goods, producing toasts and obviously taking care of customers are all part of standig in the kiosk. A great way to meet people',2,'Dear participant, your DIY-job is helping out in the kiosk - meeting place is the kiosk. It will be lots of fun and we look forward to seeing you!'),(6,'Madudlevering','Delagtiggør resten af Fastaval i de mirakler køkkenet har frembragt.','Kære deltager, din GDS-tjans er Madudlevering, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Food handout','Make the miracles from the kitchen available to everybody',2,'Dear participant, your DIY-job is food handout. Meeting place is the kitchen. We look forward to seeing - thanks for joining us!'),(7,'Opvask','Den perfekte mulighed for køkkenhyggen, selv hvis du ikke er helt du med madlavning.','Kære deltager, din GDS-tjans er Opvask, mødestedet er madudleveringen. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Dishwashing','The perfect opportunity to take part in the kitchen cheer even if your cooking skill are not all the hot',3,'Dear participant, your DIY-job is dishwashing. Meeting place is where the food is handed out. We look forward to seeing you - thanks for joining!'),(10,'James','Deltag i det stilfulde James live. Høflig servering af dessert og drinks mm.','Kære deltager, din GDS-tjans er James, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Waiter (James)','Take part in the stylish James live. Politely serving dessert and drinks.',6,'Dear participant, your DIY-job is James (waiter). Meeting place is the kitchen. We look forward to seeing you - thanks for joining the fun!'),(11,'Fest-opvask','Opvask af glas fra banketten. Varer to timer.','Kære deltager, din GDS-tjans er Banketopvask, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-dishwashing','Cleaning of glasses after the banquet. The shift is shorter than previous years.',3,'Dear participant, your DIY-job is dishwashing after the party. Meeting place is the kitchen. We look forward to seeing you - thanks for joining!'),(12,'Lokaleoprydning','Lukning og oprydning af lokaler om lørdagen og søndagen. En vigtig tjans','Kære deltager, din GDS-tjans er oprydning, mødestedet er informationen kl.12.00. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Cleaning','Cleaning and closing game rooms on Saturday and Sunday.',3,'Dear participant, your DIY-job is cleaning, meeting place is the information at 12. We look forward to seeing you - it\'ll be fun!'),(13,'Bazar','Hurtig og nem tjans. Mest af alt slæbearbejde med opstilling of borde og den slags.','0','Bazaar','Fast and simple chore. Mostly heavy liftning to move tables back and forth',4,NULL),(17,'Caféhjælp','Hjælp til i caféen, der er brug for hjælp både i køkkenet med praktiske opgaver, som opvask anretning af tapas, men også bag baren til mixing og servering af drinks. Du aftaler selv med caféen om hvad du vil hjælpe med. Min alder 18 år.','Caféen','Café help','Help out in the café by cleaning, washing dishes and supplying drinks. Skills in drinks-mixing not required. You will settle tasks with the Cafe on arriving. Minimum age 18 years.',2,'Café'),(19,'Ungdomslounge','Hjælp til i ungdomsloungen.','Ungdomsloungen','Youth lounge','Help out in the youth lounge',7,'The youth lounge'),(20,'Barhjælp','Bartenderi og generel hjælp i baren','Baren','Barhelp','Helping out tending bar and general bar duties',2,'The bar');
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
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gdsvagter`
--

LOCK TABLES `gdsvagter` WRITE;
/*!40000 ALTER TABLE `gdsvagter` DISABLE KEYS */;
INSERT INTO `gdsvagter` VALUES (5,4,2,'2016-03-24 08:00:00','2016-03-24 11:00:00'),(7,4,2,'2016-03-24 12:00:00','2016-03-24 15:00:00'),(8,4,2,'2016-03-24 16:00:00','2016-03-24 19:00:00'),(9,4,2,'2016-03-24 20:00:00','2016-03-24 23:00:00'),(10,4,2,'2016-03-25 08:00:00','2016-03-25 11:00:00'),(12,4,2,'2016-03-25 12:00:00','2016-03-25 15:00:00'),(13,4,2,'2016-03-25 16:00:00','2016-03-25 19:00:00'),(14,4,2,'2016-03-25 20:00:00','2016-03-25 23:00:00'),(15,4,2,'2016-03-26 08:00:00','2016-03-26 11:00:00'),(17,4,2,'2016-03-26 12:00:00','2016-03-26 15:00:00'),(18,4,2,'2016-03-26 20:00:00','2016-03-26 23:00:00'),(25,4,2,'2016-03-27 08:00:00','2016-03-27 11:00:00'),(26,4,3,'2016-03-27 12:00:00','2016-03-27 15:00:00'),(27,4,2,'2016-03-27 16:00:00','2016-03-27 19:00:00'),(73,13,4,'2016-03-25 15:00:00','2016-03-25 16:00:00'),(74,13,4,'2016-03-25 18:30:00','2016-03-25 19:30:00'),(75,12,4,'2016-03-26 12:00:00','2016-03-26 15:00:00'),(76,12,10,'2016-03-27 12:00:00','2016-03-27 15:00:00'),(79,1,5,'2016-03-27 16:00:00','2016-03-27 19:00:00'),(102,10,12,'2016-03-27 18:00:00','2016-03-27 22:00:00'),(105,1,4,'2016-03-27 10:00:00','2016-03-27 12:00:00'),(106,2,1,'2016-03-24 07:00:00','2016-03-24 09:00:00'),(107,2,1,'2016-03-24 09:00:00','2016-03-24 11:00:00'),(108,2,1,'2016-03-24 11:00:00','2016-03-24 13:00:00'),(109,2,1,'2016-03-24 13:00:00','2016-03-24 15:00:00'),(110,2,1,'2016-03-24 15:00:00','2016-03-24 17:00:00'),(111,2,1,'2016-03-24 17:00:00','2016-03-24 19:00:00'),(112,2,1,'2016-03-24 19:00:00','2016-03-24 21:00:00'),(113,2,1,'2016-03-25 07:00:00','2016-03-25 09:00:00'),(114,2,1,'2016-03-25 09:00:00','2016-03-25 11:00:00'),(115,2,1,'2016-03-25 11:00:00','2016-03-25 13:00:00'),(116,2,1,'2016-03-25 13:00:00','2016-03-25 15:00:00'),(117,2,1,'2016-03-25 15:00:00','2016-03-25 17:00:00'),(118,2,1,'2016-03-25 17:00:00','2016-03-25 19:00:00'),(119,2,1,'2016-03-25 19:00:00','2016-03-25 21:00:00'),(120,2,1,'2016-03-26 07:00:00','2016-03-26 09:00:00'),(121,2,1,'2016-03-26 09:00:00','2016-03-26 11:00:00'),(122,2,1,'2016-03-26 11:00:00','2016-03-26 13:00:00'),(123,2,1,'2016-03-26 13:00:00','2016-03-26 15:00:00'),(124,2,1,'2016-03-26 15:00:00','2016-03-26 17:00:00'),(125,2,1,'2016-03-26 17:00:00','2016-03-26 19:00:00'),(126,2,1,'2016-03-26 19:00:00','2016-03-26 21:00:00'),(127,2,1,'2016-03-27 07:00:00','2016-03-27 09:00:00'),(128,2,1,'2016-03-27 09:00:00','2016-03-27 11:00:00'),(129,2,1,'2016-03-27 11:00:00','2016-03-27 13:00:00'),(130,2,1,'2016-03-27 13:00:00','2016-03-27 15:00:00'),(131,2,1,'2016-03-27 15:00:00','2016-03-27 17:00:00'),(132,2,1,'2016-03-27 17:00:00','2016-03-27 19:00:00'),(133,2,1,'2016-03-27 19:00:00','2016-03-27 21:00:00'),(134,6,3,'2016-03-24 08:00:00','2016-03-24 10:45:00'),(135,6,3,'2016-03-25 08:00:00','2016-03-25 10:45:00'),(136,6,3,'2016-03-26 08:00:00','2016-03-26 10:45:00'),(137,6,3,'2016-03-27 08:00:00','2016-03-27 10:45:00'),(138,6,6,'2016-03-23 17:15:00','2016-03-23 19:45:00'),(139,6,6,'2016-03-24 17:15:00','2016-03-24 19:45:00'),(140,6,6,'2016-03-25 17:15:00','2016-03-25 19:45:00'),(141,6,6,'2016-03-26 17:15:00','2016-03-26 19:45:00'),(142,6,6,'2016-03-27 17:15:00','2016-03-27 19:45:00'),(143,7,5,'2016-03-24 10:00:00','2016-03-24 11:30:00'),(144,7,5,'2016-03-25 10:00:00','2016-03-25 11:30:00'),(145,7,5,'2016-03-26 10:00:00','2016-03-26 11:30:00'),(146,7,5,'2016-03-27 10:00:00','2016-03-27 11:30:00'),(147,7,6,'2016-03-23 18:00:00','2016-03-23 20:00:00'),(148,7,6,'2016-03-24 18:00:00','2016-03-24 20:00:00'),(149,7,6,'2016-03-25 18:00:00','2016-03-25 20:00:00'),(150,7,6,'2016-03-26 18:00:00','2016-03-26 20:00:00'),(151,7,6,'2016-03-27 18:00:00','2016-03-27 20:00:00'),(152,11,5,'2016-03-27 20:30:00','2016-03-27 23:30:00'),(153,4,2,'2016-03-26 16:00:00','2016-03-26 19:00:00'),(156,19,2,'2016-03-24 15:30:00','2016-03-24 16:00:00'),(157,19,2,'2016-03-25 15:30:00','2016-03-25 16:00:00'),(160,19,2,'2016-03-26 15:30:00','2016-03-26 16:00:00'),(161,19,2,'2016-03-27 15:30:00','2016-03-27 16:00:00'),(162,17,1,'2016-03-23 16:00:00','2016-03-23 18:00:00'),(163,17,1,'2016-03-23 18:00:00','2016-03-23 20:00:00'),(164,17,2,'2016-03-24 12:00:00','2016-03-24 14:00:00'),(165,17,2,'2016-03-24 14:00:00','2016-03-24 16:00:00'),(166,17,1,'2016-03-24 16:00:00','2016-03-24 18:00:00'),(167,17,1,'2016-03-24 18:00:00','2016-03-24 20:00:00'),(168,17,2,'2016-03-25 12:00:00','2016-03-25 14:00:00'),(169,17,2,'2016-03-25 14:00:00','2016-03-25 16:00:00'),(170,17,1,'2016-03-25 16:00:00','2016-03-25 18:00:00'),(171,17,1,'2016-03-25 18:00:00','2016-03-25 20:00:00'),(172,17,2,'2016-03-26 12:00:00','2016-03-26 14:00:00'),(173,17,2,'2016-03-26 14:00:00','2016-03-26 16:00:00'),(174,17,1,'2016-03-26 16:00:00','2016-03-26 18:00:00'),(175,17,1,'2016-03-26 18:00:00','2016-03-26 20:00:00'),(176,17,2,'2016-03-27 12:00:00','2016-03-27 14:00:00'),(177,17,2,'2016-03-27 14:00:00','2016-03-27 16:00:00'),(178,17,1,'2016-03-27 16:00:00','2016-03-27 18:00:00'),(179,17,1,'2016-03-27 18:00:00','2016-03-27 20:00:00'),(182,20,1,'2016-03-24 01:30:00','2016-03-24 03:30:00'),(185,20,1,'2016-03-25 01:30:00','2016-03-25 03:30:00'),(188,20,1,'2016-03-26 01:30:00','2016-03-26 03:30:00'),(191,20,1,'2016-03-27 01:30:00','2016-03-27 03:30:00'),(192,1,4,'2016-03-27 14:00:00','2016-03-27 17:00:00'),(193,1,2,'2016-03-27 15:00:00','2016-03-27 18:00:00'),(194,1,2,'2016-03-25 09:00:00','2016-03-25 10:00:00'),(195,12,1,'2016-03-25 10:35:00','2016-03-25 11:00:00'),(196,2,1,'2016-03-25 23:00:00','2016-03-26 01:00:00'),(197,2,1,'2016-03-26 23:00:00','2016-03-27 01:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=649 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hold`
--

LOCK TABLES `hold` WRITE;
/*!40000 ALTER TABLE `hold` DISABLE KEYS */;
/*!40000 ALTER TABLE `hold` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indgang`
--

LOCK TABLES `indgang` WRITE;
/*!40000 ALTER TABLE `indgang` DISABLE KEYS */;
INSERT INTO `indgang` VALUES (1,215,'2016-03-23 00:00:00','Indgang - Partout'),(2,95,'2016-03-23 00:00:00','Indgang - Partout - Alea'),(3,65,'2016-03-23 08:00:00','Indgang - Enkelt'),(4,65,'2016-03-24 08:00:00','Indgang - Enkelt'),(5,65,'2016-03-25 08:00:00','Indgang - Enkelt'),(6,65,'2016-03-26 08:00:00','Indgang - Enkelt'),(7,65,'2016-03-27 08:00:00','Indgang - Enkelt'),(8,190,'2016-03-23 00:00:00','Overnatning - Partout'),(9,65,'2016-03-23 22:00:00','Overnatning - Enkelt'),(10,65,'2016-03-24 22:00:00','Overnatning - Enkelt'),(11,65,'2016-03-25 22:00:00','Overnatning - Enkelt'),(12,65,'2016-03-26 22:00:00','Overnatning - Enkelt'),(13,65,'2016-03-27 22:00:00','Overnatning - Enkelt'),(14,100,'2016-03-23 00:00:00','Leje af madras'),(15,40,'2016-03-23 00:00:00','Indgang - Partout - Alea - Ung'),(16,160,'2016-03-23 00:00:00','Indgang - Partout - Ung'),(19,115,'2016-03-23 00:00:00','Overnatning - Partout - Arrangør'),(20,75,'2016-03-23 00:00:00','Alea medlemskab'),(21,90,'2016-03-27 20:00:00','Ottofest'),(22,160,'2017-03-23 00:00:00','Indgang - Partout - Arrangør'),(23,40,'2017-03-23 00:00:00','Indgang - Partout - Alea - Arrangør'),(24,110,'2016-03-27 00:00:00','Ottofest - Champagne'),(27,0,'2016-03-23 00:00:00','GRATIST Dagsbillet'),(28,0,'2016-03-24 00:00:00','GRATIST Dagsbillet'),(29,0,'2016-03-25 00:00:00','GRATIST Dagsbillet'),(30,0,'2016-03-26 00:00:00','GRATIST Dagsbillet'),(31,0,'2016-03-27 00:00:00','GRATIST Dagsbillet'),(32,0,'2016-03-23 00:00:00','GRATIST Overnatning'),(33,0,'2016-03-24 00:00:00','GRATIST Overnatning'),(34,0,'2016-03-25 00:00:00','GRATIST Overnatning'),(35,0,'2016-03-26 00:00:00','GRATIST Overnatning'),(36,0,'2016-03-27 00:00:00','GRATIST Overnatning'),(37,10,'2016-03-23 00:00:00','Bankoverførselsgebyr'),(38,50,'2016-03-23 00:00:00','Dørbetalingsgebyr'),(39,0,'2016-03-23 00:00:00','Indgang - Partout - Barn'),(40,10,'2016-01-30 00:00:00','Billetgebyr'),(41,80,'2016-03-27 00:00:00','Ottofest - hvidvin'),(42,80,'2016-03-27 00:00:00','Ottofest - rødvin');
/*!40000 ALTER TABLE `indgang` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=56205 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokaler`
--

LOCK TABLES `lokaler` WRITE;
/*!40000 ALTER TABLE `lokaler` DISABLE KEYS */;
INSERT INTO `lokaler` VALUES (1,'Fællesområde','','','ja','nej',0),(2,'Brætspil','','','ja','nej',0),(3,'Samlingssal (Warhammer)','','','ja','nej',0),(4,'A-gang','','','ja','nej',0),(5,'A08 - Butik','Butik','','ja','nej',0),(7,'B32 - Pathfinder','Pathfinder','','ja','nej',0),(8,'B34','','','ja','nej',0),(9,'B36','','','ja','nej',0),(10,'B38','','','ja','nej',0),(11,'B39','','','ja','nej',0),(12,'B41','','','ja','nej',0),(13,'B43','','','ja','nej',0),(14,'B44 ','','','ja','nej',0),(15,'B46 ','','','ja','nej',0),(16,'B47','','','ja','nej',0),(17,'C53','Kælder','','ja','nej',0),(18,'D60','','','ja','nej',0),(19,'D61','','','ja','nej',0),(20,'D62','','','ja','nej',0),(21,'D65','','','ja','nej',0),(22,'D66','','','ja','nej',0),(23,'D67','','','ja','nej',0),(24,'1.01 - Info','Info','','ja','nej',0),(25,'1.02','','','ja','nej',0),(26,'1.03','','','ja','nej',0),(27,'1.04','','','ja','nej',0),(28,'1.05','','','ja','nej',0),(29,'2.01','','','ja','nej',0),(30,'2.02','','','ja','nej',0),(31,'2.03','','','ja','nej',0),(32,'2.04','','','ja','nej',0),(34,'2.06','','','ja','nej',0),(35,'2.09','','','ja','nej',0),(36,'Svømmehal','','','ja','nej',0),(37,'TV','E70','','nej','nej',0),(38,'Lounge','Ungdomslounge','','ja','nej',0),(39,'Baren','','','ja','nej',0),(43,'X Fiktivt lokale 6','','','ja','nej',0),(44,'C58 - Formning','\"begrænset brug\"','','ja','nej',0),(45,'X Fiktivt lokale 5','','','ja','nej',0),(46,'X Fiktivt lokale 1','','','ja','nej',0),(47,'X Fiktivt lokale 2','','','ja','nej',0),(48,'X Fiktivt lokale 3','','','ja','nej',0),(49,'X Fiktivt lokale 4','','','ja','nej',0),(50,'X Fiktivt lokale 7','','','ja','nej',0),(51,'X Fiktivt lokale 8','','','ja','nej',0),(52,'X Fiktivt lokale 9','','','ja','nej',0),(53,'X Fiktivt lokale 10','','','ja','nej',0),(54,'Dragon\'s Lair','B45','','nej','nej',0),(55,'Butik','A 07','','nej','nej',0),(56,'B 30 - Artemis','Artemis','','ja','nej',0),(57,'F 40','Barrak','','ja','nej',0),(58,'F 41','Barrak','','ja','nej',0),(59,'C 50','Kælder','','ja','nej',0),(60,'C 51','Kælder','','ja','nej',0),(61,'C 55','Kælder','','ja','nej',0),(62,'Cykelkælder','','','ja','nej',0),(63,'Caféen','','','ja','nej',0),(64,'Brætspilscafeen','','','ja','nej',0),(65,'D-gang - Magic','Magic','','ja','nej',0),(66,'C48','Brandvagts sovesal','','nej','nej',10),(67,'Arrangørsovesal','','','nej','nej',50),(68,'Ungdomssovesal','','','nej','nej',50),(69,'C58','Hinterlandet','','ja','nej',0),(70,'Sovesal','Idrætscenteret','','nej','nej',362),(71,'E71','','','ja','nej',0),(72,'Vandrehjem/Hostel','','','ja','nej',0),(73,'Fællesområdet udenfor lokale E71 i kælderen','','','ja','nej',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mad`
--

LOCK TABLES `mad` WRITE;
/*!40000 ALTER TABLE `mad` DISABLE KEYS */;
INSERT INTO `mad` VALUES (1,'Aftensmad',55,'Regular'),(2,'Aftensmad - vegetar',55,'Vegetarian'),(4,'Morgenmad',27,'Breakfast');
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
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `madtider`
--

LOCK TABLES `madtider` WRITE;
/*!40000 ALTER TABLE `madtider` DISABLE KEYS */;
INSERT INTO `madtider` VALUES (145,1,'2016-03-23 17:30:00','Lasagne','Lasagne'),(146,1,'2016-03-24 17:30:00','Grillet kyllingebryst med ris og paprikasauce','Grilled chicken breast with rice and paprika sauce'),(147,1,'2016-03-25 17:30:00','Hakkebøf med bløde løg og hvide kartofler','Mincemeat steak with fried onions and boiled potatoes'),(148,1,'2016-03-26 17:30:00','Kylling i karry med løse ris','Chicken curry with rice'),(149,1,'2016-03-27 17:30:00','Skinke og flødekartofler','Ham and creamed potatoes'),(150,2,'2016-03-23 17:30:00','Vegetarlasagne','Vegetarian lasagna'),(151,2,'2016-03-24 17:30:00','Qournbøf med tilbehør','Qournsteak with extras'),(152,2,'2016-03-25 17:30:00','Paneret jordnødderulle','Fried peanut roll'),(153,2,'2016-03-26 17:30:00','Karryret med strimlet qourn','Curry with quorn'),(154,2,'2016-03-27 17:30:00','Linsefrikadeller','Lentil meatlessballs'),(160,4,'2016-03-24 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(161,4,'2016-03-25 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(162,4,'2016-03-26 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(163,4,'2016-03-27 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.');
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
INSERT INTO `migrations` VALUES (1001),(1002),(1003),(1004),(1005),(1006);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `area` enum('shop','boardgames') NOT NULL,
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
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (44,'*','*'),(45,'ActivityController','*'),(46,'ParticipantController','*'),(47,'GdsController','*'),(48,'GroupsController','*'),(49,'IndexController','*'),(50,'LogController','*'),(51,'RoomsController','*'),(52,'FoodController','*'),(53,'WearController','*'),(54,'AdminController','*'),(55,'TodoController','*'),(56,'TodoController','viewTodoList'),(57,'GroupsController','main'),(58,'GroupsController','visHold'),(59,'GroupsController','visAlle'),(60,'ParticipantController','main'),(61,'ParticipantController','karmaStatus'),(62,'ParticipantController','visDeltager'),(63,'ParticipantController','visAlle'),(64,'ParticipantController','listForSchedule'),(65,'ParticipantController','listForGroup'),(66,'ParticipantController','showSearch'),(67,'ParticipantController','updateDeltager'),(68,'ParticipantController','updateDeltagerNote'),(69,'ParticipantController','listGMs'),(70,'ParticipantController','karmaList'),(71,'ParticipantController','showBoughtFood'),(72,'ParticipantController','printList'),(73,'ParticipantController','spillerSedler'),(74,'DeltagerController','economyBreakdown'),(75,'WearController','main'),(76,'WearController','showTypes'),(77,'WearController','wearBreakdown'),(78,'WearController','detailedOrderListPrint'),(79,'WearController','detailedOrderList'),(80,'WearController','detailedMiniList'),(81,'WearController','ajaxGetWear'),(82,'WearController','showWear'),(83,'EntranceController','*'),(84,'FoodController','displayHandout'),(85,'FoodController','displayHandoutAjax'),(86,'ParticipantController','showSearchResult'),(87,'ParticipantController','ajaxParameterSearch'),(88,'ParticipantController','ajaxUserSearch'),(89,'ParticipantController','ajaxlist'),(90,'ActivityController','main'),(91,'ActivityController','visAlle'),(92,'ActivityController','visAktivitet'),(93,'ActivityController','gameStartDetails'),(94,'EconomyController','*'),(95,'ShopController','*'),(97,'GraphController','*'),(98,'BoardgamesController','*'),(99,'ActivityController','showVotingStats');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (7,'admin','Administrator role - all powerful'),(8,'superuser','Access to nigh everything'),(9,'Infonaut','Generel power-role'),(10,'Food-handout','Food handout'),(11,'Activity-admin','Access to activity related stuff'),(12,'Wear-admin','Wear administrator'),(13,'Read-only','Look but not touch'),(14,'Read-only activity','Look but not touch activities'),(15,'Bazar-admin','Can see info on activities and participants'),(16,'Food-admin','Can deal with food stuff'),(17,'Participant admin','Can make changes to participants'),(18,'SMS','Privilege of sending SMS messages'),(19,'Boardgame admin','Handles the boardgame app'),(20,'Setup admin','In charge of Fastaval setup'),(21,'SetupTest','Fastaval Setup+');
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
INSERT INTO `roles_privileges` VALUES (7,44),(8,44),(8,45),(9,45),(11,45),(15,45),(8,46),(9,46),(11,46),(12,46),(15,46),(17,46),(8,47),(9,47),(9,48),(11,48),(8,49),(9,49),(10,49),(11,49),(12,49),(13,49),(14,49),(15,49),(16,49),(19,49),(8,50),(9,50),(11,50),(12,50),(13,50),(14,50),(15,50),(16,50),(8,51),(9,51),(11,51),(20,51),(21,51),(8,52),(9,52),(16,52),(8,53),(9,53),(12,53),(8,56),(9,56),(8,57),(8,58),(8,59),(13,60),(21,60),(13,62),(13,63),(21,63),(13,66),(8,83),(9,83),(10,84),(10,85),(13,86),(21,86),(13,87),(13,88),(21,88),(13,89),(21,89),(14,90),(14,91),(14,92),(14,93),(9,94),(8,95),(13,97),(19,97),(19,98),(14,99);
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
) ENGINE=InnoDB AUTO_INCREMENT=2655 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=5543 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'peter.e.lind@gmail.com','$2y$10$LVCn0lpFfHPVViCjKXzmZ.2lPkAFY0BEaBYIkK7x7sfUCHG4nBxLW','nej','54d0fd83f0eb8f9127985f55af999046','2015-11-23 22:04:05'),
(47,'simonsteen@yahoo.dk','$2y$10$c.Vq1HsdwbZLzckvF23NPuXUGJgh/Rb3MhVJBDzDIusyA0w5n1IIi','nej','9cdea5338609c120cf0762d0ec2185ea','0000-00-00 00:00:00'),
(48,'ego@kristoffermads.dk','$2y$10$sAhXnwbDLZ7ftiJtZwr2/OKj2WDDy4eGHrPmBd1AC5I8gR4qWDxzW','ja','d29c08f446a6d8d37a69bc8d996b8871','0000-00-00 00:00:00'),
(49,'afrostb@hotmail.com','$2y$10$ueY1k9vAqIaOLVx2WAzG8.yc6x5/llPkqCERM48auY2Hh36AyUfcC','ja','5113163d642f35ab323ed611e989f8f6','0000-00-00 00:00:00'),
(52,'ak47@762.dk','$2y$10$CxQPhaZiULgOB.th3/Lm2.ZL4Ub8RxfVhIFb3Kumnxe0lz5dPbkaW','nej','3b6b42502aac593617f3b011b17eef31','0000-00-00 00:00:00'),
(53,'Vinterulven@gmail.com','$2y$10$TTI23col9DJ/zbq6y0RceOzx4dSb8pU/MpKyP9u4MFwU1r9zXxTxS','nej','bf375611c3d301281ae08b9cd0c95ac7','0000-00-00 00:00:00'),
(54,'charlesbn@gmail.com','$2y$10$W3qJTpzBVdVrLYUuQwiqveopcjTfy3BwXekSVu1EbQuVPqhW9UvdS','nej','9925e9d69c87e9b91e97d05907e4d9a8','0000-00-00 00:00:00'),
(55,'pbrichs@gmail.com','$2y$10$6NbjsFPW3w8eSboFF2EKkeeHKfVhYCtMFVASSrtSGLmnw9c8Pg97G','nej','0ed1dd6c02ef8b7ca144d77a7ff5e415','0000-00-00 00:00:00'),
(56,'wombattus@hotmail.com','$2y$10$947dyMwuoUWIExoa7QCYYOZKwklmzI7eJxxOmey14rEe8s.lmNgRS','nej','6085c29dcf39d21278cc88ee4d575ba4','0000-00-00 00:00:00'),
(58,'antonsenchristian@gmail.com','$2y$10$NCx3bEX0MAoR3.4mQXtxR.KIVb3pomF/VMqHRbfR174F38k2E9NSq','ja','350fb46aee47b51385a4eaee7302987d','0000-00-00 00:00:00'),
(59,'shamgeez@gmail.com','$2y$10$uRwXgoNjPjyNeWB3nEjQ1OECSJsJ0sU0VsgEp.l2.7DjpV7skEohu','ja','476791fc45f0fdd372b3b8b3943fda4d','0000-00-00 00:00:00'),
(61,'andreasskovse@gmail.com','$2y$10$XPyrTZ/w2neXah.gYjLhCepnk5XYg7itk81yJ.TJ5MgtPCSuoWDHG','nej','6fc19c4e8853645c18eef315e3dbd784','0000-00-00 00:00:00'),
(85,'mad@fastaval.dk','$2y$10$sMt1opS2qbX11Su5LQI9/OfslrIZkSzx4NnYOccbiFjpDoi3fu1pC','ja','','0000-00-00 00:00:00');
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
INSERT INTO `users_roles` VALUES (1,7),
(47,8),
(49,8),
(52,8),
(53,8),
(54,8),
(55,8),
(56,8),
(58,9),
(59,9),
(61,9),
(47,11),
(49,11),
(85,10),
(59,18);
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wear`
--

LOCK TABLES `wear` WRITE;
/*!40000 ALTER TABLE `wear` DISABLE KEYS */;
INSERT INTO `wear` VALUES (1,'T-Shirt (Royal Blue)','S-3XL','','T-Shirt (Royal Blue)',''),(2,'Crew T-Shirt (Bottle Green)','S-3XL','','Crew T-Shirt (Bottle Green)',''),(3,'Infonaut T-Shirt (orange)','S-3XL','','Infonaut T-Shirt',''),(4,'Kiosk T-Shirt (Burgundy)','S-3XL','','Kiosk T-Shirt (Burgundy)',''),(5,'Badekåbe','S-4XL','','Bathrobe',''),(7,'Hættetrøje','S-2XL','','Hoodie',''),(18,'Fastakruset','M-M','','The Fastaval Mug',''),(19,'Junior T-Shirt','92CM-152CM','','Junior T-Shirt','');
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
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wearpriser`
--

LOCK TABLES `wearpriser` WRITE;
/*!40000 ALTER TABLE `wearpriser` DISABLE KEYS */;
INSERT INTO `wearpriser` VALUES (140,1,1,110),(141,1,2,90),(142,1,8,90),(147,3,3,0),(148,4,9,0),(149,5,1,375),(150,5,2,375),(151,5,8,375),(163,2,2,90),(164,2,8,90),(169,18,1,75),(170,18,2,75),(171,18,8,75),(172,18,6,75),(173,18,4,75),(174,18,3,75),(175,18,9,75),(197,7,1,280),(198,7,2,280),(199,7,8,280),(204,5,3,375),(205,5,6,375),(206,5,4,375),(207,5,9,375),(212,2,6,90),(213,2,4,90),(214,2,3,90),(215,2,9,90),(216,7,3,280),(217,7,6,280),(218,7,4,280),(219,7,9,280),(220,1,3,90),(221,1,6,90),(222,1,4,90),(223,1,9,90),(224,19,1,100),(225,19,2,100),(226,19,8,100),(227,19,6,100),(228,19,4,100),(229,19,3,100),(230,19,9,100);
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

-- Dump completed on 2016-10-09  0:30:01
