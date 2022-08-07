-- MySQL dump 10.13  Distrib 5.5.58, for debian-linux-gnu (i686)
--
-- Host: DB    Database: infosys2017
-- ------------------------------------------------------
-- Server version	5.5.58-0+deb7u1-log

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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityageranges`
--
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
) ENGINE=InnoDB AUTO_INCREMENT=325 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `afviklinger_multiblok`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aktiviteter`
--


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
) ENGINE=InnoDB AUTO_INCREMENT=2617 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgameevents`
--


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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1016 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boardgames`
--

LOCK TABLES `boardgames` WRITE;
/*!40000 ALTER TABLE `boardgames` DISABLE KEYS */;
INSERT INTO `boardgames` VALUES (653,'','1244 The Fall of Montsegour','Ragnvald','',0),(654,'','7 wonders','Jeanette','',0),(655,'','7 wonders duel','Jeanette','',0),(656,'','7 wonders duel 2','Fastaval','',0),(657,'','A Dishonest Adventure','Stefen Nordborg Erikson et al','',0),(658,'','A feast for Odin','Martin Lindhart','',0),(659,'','A feast for Odin','Kat','',0),(660,'','A Study in Emerald','Ragnvald','',0),(661,'','Abyss','Kat','',0),(662,'','Adrenaline','Thomas','',0),(663,'','Aeons End','Muyten','',0),(664,'','Age of steam','Ragnvald','',0),(665,'','Agricola','Muyten','',0),(666,'','Alcatraz','Ragnvald','',0),(667,'','Alchemists','Ragnvald','',0),(668,'','Alhambra','Ragnvald','',0),(669,'','Alien uprising','Ragvald','',0),(670,'','All wound Up','Ragnvald','',0),(671,'','Among nobles','Muyten','',1),(672,'','Among the Stars','Thomas','',0),(673,'','And then the sky','Ragnvald','',1),(674,'','Anima','Ragnvald','',0),(675,'','Antike','Ragnvald','',0),(676,'','Aquaretto','Ragnvald','',0),(677,'','Arkham horror the card game 1','Thomas','',0),(678,'','Arkham horror the card game 2','Thomas','',0),(679,'','Armageddon','Muyten','',0),(680,'','Armor of the Dwarven King','Sven Strandbygaard','',1),(681,'','Asgard','Ragnvald','',0),(682,'','Atlantis','Jeanette','',0),(683,'','Attika','Ragnvald','',0),(684,'','Avalon','Martin Lindhart','',0),(685,'','Avalon','Muyten','',0),(686,'','Avalon','Ragnvald','',0),(687,'','Balloon cup','Ragnvald','',0),(688,'','Bandu (bausak)','Ragnvald','',0),(689,'','Bang The dice game','Ragnvald','',0),(690,'','Battle line','Ragnvald','',0),(691,'','Battlestar Galactica','Ragnvald','',0),(692,'','Beasty bar','Kat','',0),(693,'','Berserker Halflings from the Dungeon of Dragons','Ragnvald','',0),(694,'','Betrayal at house on the hill','Jeanette','',0),(695,'','Biblios','Jeanette','',0),(696,'','Black fleet','Ragnvald','',0),(697,'','Blades, Bows & Fireballs','Allan Kirkeby','',1),(698,'','Blood blow team manager the card game','Martin Lindhart','',0),(699,'','Blood bowl tema manager','Muyten','',0),(700,'','Bogstav huskespil','Jeanette','',0),(701,'','Bohnanza (lille)','Ragnvald','',0),(702,'','Bohnanza stor','Ragnvald','',0),(703,'','Brandvagten the card game','Ragnvald','',0),(704,'','Brass','Ragnvald','',0),(705,'','Bravest warriors','Ragnvald','',0),(706,'','Brittania(ny)','Ragnvald','',0),(707,'','burning rome','Ragnvald','',1),(708,'','Cacao','Ragnvald','',0),(709,'','Cafe life','Fastaval','',1),(710,'','Camel up','Ragnvald','',0),(711,'','Cape Horn','Ragnvald','',0),(712,'','Captain sonar','Thomas','',0),(713,'','Carcasonne','Ragnvald','',0),(714,'','Carcasonne - Die Jäger und Sammler','Ragnvald','',0),(715,'','Carcassonne: Die Burg','Ragnvald','',0),(716,'','Cards Against Humanity','Fastaval','',0),(717,'','Cash and Guns','Ragnvald','',0),(718,'','Cash and Guns (Lille)','Ragnvald','',0),(719,'','Castle','Ragnvald','',0),(720,'','Castle panic','Ragnvald','',0),(721,'','Cave Troll(nyt)','Ragnvald','',0),(722,'','Caverna','Ragnvald','',0),(723,'','Cavum','Ragnvald','',0),(724,'','Caylus','Ragnvald','',0),(725,'','Chaos Arena','Ragnvald','',0),(726,'','Chaos in the Old World','Muyten','',0),(727,'','Children of Fire','Ragnvald','',0),(728,'','China Moon (kvæk)','Ragnvald','',0),(729,'','Chinatown','Ragnvald','',0),(730,'','Citadels','Ragnvald','',0),(731,'','Clank!','Ragnvald','',0),(732,'','Clans','Ragnvald','',0),(733,'','Cluedo','Ragnvald','',0),(734,'','Codenames DK','Ragnvald','',0),(735,'','Codenames pictures','Kat','',0),(736,'','Colonies (L)','Ragnvald','',0),(737,'','Colossal Arena','Jeanette','',0),(738,'','Colosseum','Ragnvald','',0),(739,'','Colt Express','Ragnvald','',0),(740,'','Compounded','Jeanette','',0),(741,'','Counter Defense','Fastaval','',1),(742,'','Coup','Thomas','',0),(743,'','Crash by Crash','Ragnvald','',0),(744,'','Cry Havoc','Thomas','',0),(745,'','Cthulhu Fluxx','Thomas','',0),(746,'','Dead of winter','Muyten','',0),(747,'','Deception','Ragnvald','',0),(748,'','Der Hexer von Salem','Jeanette','',0),(749,'','Der Turmbau zu Babel','Ragnvald','',0),(750,'','Det dårlige selskab','Ragnvald','',0),(751,'','Det dårlige selskab udvidelse 1','Fastaval','',0),(752,'','Det dårlige selskab udvidelse 2','Fastaval','',0),(753,'','Deus','Kat','',0),(754,'','Dice Brewing','Ragnvald','',0),(755,'','Die Kutschfahrt zur Teufelsburg + udvidelser','Jeanette','',0),(756,'','Die Sterne Stehen Richtig','Jeanette','',0),(757,'','Dobble','Ragnvald','',0),(758,'','Dos Rios','Ragnvald','',0),(759,'','Dr Eureka','Ragnvald','',0),(760,'','Draco & co','Muyten','',0),(761,'','Dragons Gold','Ragnvald','',0),(762,'','Dungeon Lords','Ragnvald','',0),(763,'','Dungeon lords','Thomas','',0),(764,'','Dungeon Twister - the card game','Ragnvald','',0),(765,'','Eight-Minute Empire: Legends','Kat','',0),(766,'','Einauge','Jeanette','',0),(767,'','Eldritch horror','Muyten','',0),(768,'','Elk Fest','Ragnvald','',0),(769,'','En garde!','Ragnvald','',0),(770,'','Escape','Ragnvald','',0),(771,'','Evolution: Climate','Thomas','',0),(772,'','Expedition','Ragnvald','',0),(773,'','Familienbande','Jeanette','',0),(774,'','Family Business','Ragnvald','',0),(775,'','Family Business','Ragnvald (labich)','',0),(776,'','Firefly','Muyten','',0),(777,'','Fjorde','Ragnvald','',0),(778,'','Flamme Rouge','Ragnvald','',0),(779,'','Flash Point + udvidelser','Jeanette','',0),(780,'','Fluxx','Jeanette','',0),(781,'','Formula Dé','Ragnvald','',0),(782,'','Formula Motor Racing','Jeanette','',0),(783,'','Fossil','Ragnvald','',0),(784,'','Fresco','Ragnvald','',0),(785,'','Fürsten von Florence','Ragnvald','',0),(786,'','Fuzzy Tiger','Ragnvald','',0),(787,'','Galaxy Trucker','Martin Lindhart','',0),(788,'','Gangland','Ragnvald','',0),(789,'','Glen More','Martin Lindhart','',0),(790,'','Gloom','Jeanette','',0),(791,'','Goblins inc','Ragnvald','',0),(792,'','Gold Digger','Ragnvald','',0),(793,'','Great western trail','Martin Lindhart','',0),(794,'','Great western trail','Ragnvald','',0),(795,'','Great western trail','Muyten','',0),(796,'','Guards of Atlantis','Thomas','',0),(797,'','Halli Cups','Ragnvald','',0),(798,'','Halli galli','Ragnvald','',0),(799,'','Hamburgum','Muyten','',0),(800,'','Hanabi 1','Ragnvald','',0),(801,'','Hanabi 2','Ragnvald','',0),(802,'','Hansa','Ragnvald','',0),(803,'','Himalaya','Ragnvald','',0),(804,'','Hivemind','Ragnvald','',1),(805,'','Honour among Thieves','Ragnvald','',1),(806,'','Horse Fever','Martin Lindhart','',0),(807,'','Horus','Ragnvald','',0),(808,'','Ia! Ia!','Martin Bødker Enghof','',1),(809,'','Innovation','Muyten','',0),(810,'','Java','Ragnvald','',0),(811,'','Jumping Frog','Lars Wagner Larsen','',1),(812,'','Jungle Speed','Ragnvald','',0),(813,'','Junkyard Races','Ragnvald','',0),(814,'','King of New York','Thomas','',0),(815,'','King of tokyo','Ragnvald','',0),(816,'','kingdomino','Martin Lindhart','',0),(817,'','Kingsburg','Ragnvald','',0),(818,'','Kodama','Kat','',0),(819,'','Kohle & Kolonie','Ragnvald','',0),(820,'','Kolejka','Jeanette','',0),(821,'','Kosmonauter i Krise','Morten Brøsted','',1),(822,'','Kroket','Ragnvald','',1),(823,'','Kung Fu Fighting','Ragnvald','',0),(824,'','La Citta','Ragnvald','',0),(825,'','Las Vegas','Ragnvald','',0),(826,'','Le Havre','Ragnvald','',0),(827,'','Lemming Mafia','Martin Lindhart','',0),(828,'','Letters from whitechapel','Thomas','',0),(829,'','Loch Ness','Jeanette','',0),(830,'','Long Live the King','Jeanette','',0),(831,'','Loopin Louie','Ragnvald','',0),(832,'','Lords of Waterdeep + udvidelse','Ragnvald','',0),(833,'','Lost Cities','Jeanette','',0),(834,'','Love letter','Martin Lindhart','',0),(835,'','Love letter','Ragnvald','',0),(836,'','Love Sick','Ragnvald','',1),(837,'','Luna','Martin Lindhart','',0),(838,'','Macho Koro','Thomas','',0),(839,'','Mage Knight','Martin Lindhart','',0),(840,'','Maginor','Ragnvald','',0),(841,'','Mamma mia','Ragnvald','',0),(842,'','Mammoth Mambo','Jeanette','',0),(843,'','Manno Monster','Ragnvald','',0),(844,'','Mansions of Madness 2. ed','Ragnvald','',0),(845,'','Mansions of Madness 2. ed','Thomas','',0),(846,'','Masters of the univers','Ragnvald','',0),(847,'','Mastersmith','Ragnvald','',1),(848,'','Match of the penguins','Jeanette','',0),(849,'','Metro Mess','Jeanette','',1),(850,'','Mice and mystics','Kat','',0),(851,'','Midsummer','Nathan Hook','',1),(852,'','Monkey Arena','Jeanette','',0),(853,'','Mother','Håkan Almer','',1),(854,'','Mr. Jack Pocket','Ragnvald','',0),(855,'','Munchkin 1','Ragnvald','',0),(856,'','Munchkin 2','Ragnvald','',0),(857,'','Munchkin 3','Ragnvald','',0),(858,'','Munchkin Bites','Ragnvald','',0),(859,'','My happy farm','Kat','',0),(860,'','Mykerinos','Jeanette','',0),(861,'','Mysterum','Ragnvald','',0),(862,'','Nations','Muyten','',0),(863,'','New angeles','Muyten','',0),(864,'','New Salem','Ragnvald','',0),(865,'','No Siesta!','Kat','',0),(866,'','Not alone','Muyten','',0),(867,'','Nuclear War','Ragnvald','',0),(868,'','Once Upon A Time','Jeanette','',0),(869,'','Orbit','Troels','',1),(870,'','Oregon','Ragnvald','',0),(871,'','Panamax','Ragnvald','',0),(872,'','Pandemic','Jeanette','2 udvidelser',0),(873,'','Panic Lab','Jeanette','',0),(874,'','Patchwork','Ragnvald','',0),(875,'','Perfect Alibi','Ragnvald','',0),(876,'','Pirate Fluxx','Jeanette','',0),(877,'','Pirates Cove','Jeanette','',0),(878,'','Pokemon Jr.','Ragnvald','',0),(879,'','Port Royal','Ragnvald','',0),(880,'','Powergrid','Ragnvald','',0),(881,'','Puerto Rico','Ragnvald','',0),(882,'','Qin','Ragnvald','',0),(883,'','Qwirkle','Martin Lindhart','',0),(884,'','Qwixx','Ragnvald','',0),(885,'','Qwixx (XL)','Martin Lindhart','',0),(886,'','Race for the Galaxy','Martin Lindhart','',0),(887,'','Raja','Ragnvald','',0),(888,'','Rampage','Thomas','',0),(889,'','Red November','Jeanette','',0),(890,'','Rhino Hero','Ragnvald','',0),(891,'','River Dragons','Jeanette','',0),(892,'','Robinson Crusoe','Ragnvald','',0),(893,'','Rock Paper Wizard','Ragnvald','',0),(894,'','Room 25','Martin Lindhart','',0),(895,'','Ruse and Bruise','Ragnvald','',0),(896,'','Russian Railroads','Thomas','',0),(897,'','Saboteur','Jeanette','',0),(898,'','Sail to india','Kat','',0),(899,'','Samurai(ny)','Ragnvald','',0),(900,'','San Gimignano','Ragnvald','',0),(901,'','Scotland Yard','Ragnvald','',0),(902,'','Scythe','Thomas','',0),(903,'','Scythe','Muyten','',0),(904,'','Secret Hitler','Thomas','',0),(905,'','Set!','Ragnvald','',0),(906,'','Settlers of Catan','Ragnvald','missing pieces',0),(907,'','Shadow hunters','Ragnvald','',0),(908,'','Shadow hunters','Ragnvald (realocation)','',0),(909,'','Shadows over Camelot + Merlin','Jeanette','',0),(910,'','Sheriff of Nottingham','Ragnvald','',0),(911,'','Siege Perilous','Ragnvald','',0),(912,'','Skåål','Ragnvald','',0),(913,'','Skull(s and roses)','Ragnvald','',0),(914,'','Smallworld','Muyten','',0),(915,'','Smash Up','Martin Lindhart','',0),(916,'','Sneglefart','Ragnvald','',1),(917,'','Snow Tails','Ragnvald','',0),(918,'','Snowdonia','Martin Lindhart','',0),(919,'','Snowdonia','Ragnvald','',0),(920,'','Soul Hunters','Muyten','',0),(921,'','Space Beans','Ragnvald','',0),(922,'','Space Cadets Dice Duel','Ragnvald','',0),(923,'','Spank the Monkey','Ragnvald','',0),(924,'','Spillekort 1','Ragnvald','',0),(925,'','Spillekort 2','Ragnvald','',0),(926,'','Splatter Shoot','Ragnvald','',0),(927,'','Splatter Shoot','Ragnvald','',0),(928,'','Splendor','Ragnvald','',0),(929,'','Spyfall','Ragnvald','',0),(930,'','Star Munchkin','Ragnvald','',0),(931,'','Star realms','Ragnvald','',0),(932,'','Star Wars Armada','Ragnvald','',0),(933,'','Star Wars: X-wing','Ragnvald','',0),(934,'','Starship Catan','Ragnvald','',0),(935,'','Steampunk Rally','Thomas','',0),(936,'','Stick Stack','Kåre Kjær & Mads Havshøj','',1),(937,'','Stone Age','Jeanette','',0),(938,'','Sunken City','Ragnvald','',0),(939,'','Tag 6','Ragnvald','',0),(940,'','Takenoko','Jeanette','',0),(941,'','Talisman','Ragnvald','',0),(942,'','Targi','Kat','',0),(943,'','Tempurra','Kat','',0),(944,'','Terra Mystica','Martin Lindhart','',0),(945,'','Terra Mystica','Ragnvald','',0),(946,'','Terraforming mars','Muyten','',0),(947,'','The Bridges of Shangri-La','Ragnvald','',0),(948,'','The game','Martin Lindhart','',0),(949,'','The Grasshopper the ant','Ragnvald','',0),(950,'','The Manhattan Project','Ragnvald','',0),(951,'','The Red Dragon Inn','Jeanette','',0),(952,'','The Scepter of Zavandor','Jeanette','',0),(953,'','The Scurvy Musketeers of the spanish Main','Ragnvald','',0),(954,'','Through the Ages: A Story of Civilization','Muyten','',0),(955,'','Thunderstone','Muyten','',0),(956,'','Ticket to Ride europe','Jeanette','',0),(957,'','Ticket to Ride: The Card game','Ragnvald','',0),(958,'','Tikal','Ragnvald','',0),(959,'','Tikitopple','Kat','',0),(960,'','Tiny Epic Western','Kat','',0),(961,'','Toc Toc Wood man','Ragnvald','',0),(962,'','Tokaido','Thomas','',0),(963,'','Tomorrow','Muyten','',0),(964,'','Torres','Ragnvald','',0),(965,'','Twilight Struggle','Muyten','',0),(966,'','Twin Tin Bots','Ragnvald','',0),(967,'','Two rooms and a boom','Thomas','',0),(968,'','Tzolkin','Muyten','',0),(969,'','Tzolkin + udvidelse','Ragnvald','',0),(970,'','Ultimate Werewolf','Ragnvald','',0),(971,'','Vikings','Ragnvald','',0),(972,'','Village','Muyten','',0),(973,'','Waka tanka','Muyten','',0),(974,'','Walk the Plank!','Muyten','',0),(975,'','Welcome back to the Dungeon','Muyten','',0),(976,'','Wiz-War','Ragnvald','',0),(977,'','Wizards Academy','Muyten','',0),(978,'','Wrong Chemistry','Jeanette','',0),(979,'','Wurfel Bohnanza','Martin Lindhart','',0),(980,'','Xena: Warrior Princess','Ragnvald','',0),(981,'','Yggdrasil','Jeanette','',0),(982,'','Zombie Fluxx','Jeanette','',0),(983,'','Zug Um Zug','Ragnvald','',0),(984,' ','Roll for the Galaxy','Tue','',0),(985,' ','Dixit','Jeanette','',0),(986,' ','Ragnarok','Sven Strandbygaard','',1),(987,' ','sky cities','Allan Kirkeby','',1),(988,' ','Box Quiz Tegnefilm','Mathias Tågholt','',0),(989,' ','Trivial Pursuit World of Harry Potter','Michael Drud','',0),(990,' ','claim to fame','morten jaeger','',1),(991,' ','13 Days','Michael Drud','',0),(992,' ','Madness at Midnight','Mads L. Brynnum','',1),(993,' ','Z - How will you survive?','Mads L. Brynnum','',1),(994,' ','Match Madness','Jeppe Norsker','',0),(995,' ','Snake Shadows','Jeppe Norsker','',1),(996,' ','Magic Maze','Kasper Lapp','',1),(997,' ','Dværge og diamanter','Sofie Liv Stovelbæk','',1),(998,' ','Skull',' ','',0),(999,' ','Architects of Reality','Morten Brøsted','',1),(1000,' ','Air Traffic Control','Morten Lund','',1),(1001,' ','Pizza Delivery','Pizza Delivery Guy','',1),(1002,' ','Cup Cake','Jeanette','',0),(1003,' ','Huske Spil Med Monstre','Jeanette','',0),(1004,'  ','Codenames (english)','Ragnvald','',0),(1005,' ','Rise to Power','Mathias Tågholt','',0),(1006,' ','Holiday resort','Kåre Werner Storgaard','',1),(1007,' ','Crazy Coconuts','Morten Lund','',0),(1008,' ','Kingmaker','Kåre Torndahl Kjær','',1),(1009,' ','Domino','Jeanette','',0),(1010,' ','Champions of Midgard','Fastaval','Mette Finderup kom med det efter Fastaval Junior og sagde Fastaval havde fået det af ham, der har lavet det?',0),(1011,' ','Clayorama','Fastaval','',0),(1012,' ','Dværge og diamanter','Sofie Liv Stovelbæk','',1),(1013,' ','Le Mans Team Manager','Morten Lund','',1),(1014,' ','Alcubierre','Designeren','',1),(1015,' ','El Grande','Jeanette','',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brugerkategorier`
--

LOCK TABLES `brugerkategorier` WRITE;
/*!40000 ALTER TABLE `brugerkategorier` DISABLE KEYS */;
INSERT INTO `brugerkategorier` VALUES (1,'Deltager','nej',NULL),(2,'Arrangør','ja',NULL),(3,'Infonaut','ja',NULL),(4,'Forfatter','ja',NULL),(5,'Arrangrhangaround','nej','Arrangr hangaround'),(6,'DirtBuster','ja',NULL),(7,'Freeloaders','nej','Folk, der skal ha gratis ting'),(8,'Brandvagt','ja','Medlem af brandvagten'),(9,'Kioskninja','ja','Vagt i kiosken'),(10,'Juniordeltager','nej','Junior deltager'),(11,'Kaffekrotjener','ja','Tjener i kaffekroen');
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
INSERT INTO `brugerkategorier_idtemplates` VALUES (4,1),(19,2),(6,3),(21,4),(4,5),(20,6),(4,7),(19,8),(23,9),(4,10),(22,11);
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
  PRIMARY KEY (`id`),
  KEY `brugerkategori_id` (`brugerkategori_id`),
  KEY `rel_karma` (`rel_karma`),
  KEY `abs_karma` (`abs_karma`),
  KEY `sovelokale_id` (`sovelokale_id`),
  CONSTRAINT `deltagere_ibfk_1` FOREIGN KEY (`brugerkategori_id`) REFERENCES `brugerkategorier` (`id`),
  CONSTRAINT `deltagere_ibfk_2` FOREIGN KEY (`sovelokale_id`) REFERENCES `lokaler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=942 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deltagere`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestarts`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamestartschedules`
--


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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gds`
--

LOCK TABLES `gds` WRITE;
/*!40000 ALTER TABLE `gds` DISABLE KEYS */;
INSERT INTO `gds` VALUES (1,'Fest-opsætning','Som navnet antyder så handler det om opsætning til den store Ottofest. Opstilling af borde, stole og pynt.','Kære deltager, din GDS-tjans er Banketopsætning, mødestedet er fællesområdet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-prepping','As the name implies this has to do with the setup of the great Otto party.  Mostly about moving tables and chair, and setting up decorations.',4,'Dear participant, your DIY-job is party-prepping Sunday. Meeting place is the big hall. It will be fun and we\'re happy that you\'ll be joining us - see you there!'),(2,'Brandvagt','En tjans for dig der er over 18 og har mobiltelefonen med. Sørg for at der ikke udbryder brand i sovesalen. Det gør der som regel ikke, så det er muligvis en god ide at have en bog med.','Kære deltager, din GDS-tjans er Brandvagt, mød op i infoen et kvarter før din vagt starter. Det bliver hyggeligt, og på forhånd tak for hjælpen. :)','Night watch','A job for the over-18 with a working mobile. All you need to do is make sure no fires break out - or, if they do, alert everyone and the fire department. You\'re welcome to keep company (in the form of friends, candy, books or all of the above) while on duty.',5,'Dear participant, your DIY-job is fireguard. Meeting place is the information 15 minutes before the shift starts. Thanks very much for your help - we look forward to seeing you!'),(4,'Kiosk','Opfyldning af varer, produktion af toasts og naturligvis kundepleje kan du få udvidet kendskab til i kiosken. En fantastisk måde at møde mennesker på.','Kære deltager, din GDS-tjans er Kiosk, mødestedet er kiosken. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Shop','Stocking goods, producing toasts and obviously taking care of customers are all part of standig in the kiosk. A great way to meet people',2,'Dear participant, your DIY-job is helping out in the kiosk - meeting place is the kiosk. It will be lots of fun and we look forward to seeing you!'),(6,'Madudlevering','Delagtiggør resten af Fastaval i de mirakler køkkenet har frembragt.','Kære deltager, din GDS-tjans er Madudlevering, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Food handout','Make the miracles from the kitchen available to everybody',2,'Dear participant, your DIY-job is food handout. Meeting place is the kitchen. We look forward to seeing - thanks for joining us!'),(7,'Opvask','Den perfekte mulighed for køkkenhyggen, selv hvis du ikke er helt du med madlavning.','Kære deltager, din GDS-tjans er Opvask, mødestedet er madudleveringen. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Dishwashing','The perfect opportunity to take part in the kitchen cheer even if your cooking skill are not all the hot',3,'Dear participant, your DIY-job is dishwashing. Meeting place is where the food is handed out. We look forward to seeing you - thanks for joining!'),(10,'James','Deltag i det stilfulde James live. Høflig servering af dessert og drinks mm.','Kære deltager, din GDS-tjans er James, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Waiter (James)','Take part in the stylish James live. Politely serving dessert and drinks.',6,'Dear participant, your DIY-job is James (waiter). Meeting place is the kitchen. We look forward to seeing you - thanks for joining the fun!'),(11,'Fest-opvask','Opvask af glas fra banketten. Varer to timer.','Kære deltager, din GDS-tjans er Banketopvask, mødestedet er køkkenet. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Party-dishwashing','Cleaning of glasses after the banquet. The shift is shorter than previous years.',3,'Dear participant, your DIY-job is dishwashing after the party. Meeting place is the kitchen. We look forward to seeing you - thanks for joining!'),(12,'Lokaleoprydning','Lukning og oprydning af lokaler om lørdagen og søndagen. En vigtig tjans','Kære deltager, din GDS-tjans er oprydning, mødestedet er informationen kl.12.00. Det bliver hyggeligt, vi glæder os til at du kommer, og på forhånd tak for hjælpen. :)','Cleaning','Cleaning and closing game rooms on Saturday and Sunday.',3,'Dear participant, your DIY-job is cleaning, meeting place is the information at 12. We look forward to seeing you - it\'ll be fun!'),(13,'Bazar','Hurtig og nem tjans. Mest af alt slæbearbejde med opstilling of borde og den slags.','0','Bazaar','Fast and simple chore. Mostly heavy liftning to move tables back and forth',4,NULL),(17,'Caféhjælp','Hjælp til i caféen, der er brug for hjælp både i køkkenet med praktiske opgaver, som opvask anretning af tapas, men også bag baren til mixing og servering af drinks. Du aftaler selv med caféen om hvad du vil hjælpe med. Min alder 18 år.','Caféen','Café help','Help out in the café by cleaning, washing dishes and supplying drinks. Skills in drinks-mixing not required. You will settle tasks with the Cafe on arriving. Minimum age 18 years.',2,'Café'),(20,'Barhjælp','Bartenderi og generel hjælp i baren','Baren','Barhelp','Helping out tending bar and general bar duties',2,'The bar'),(21,'Kaffekro-hjælp','Hjælp til i kaffekroen med forefaldende arbejde','Kaffekroen','Coffee inn help','Help out in the coffee inn',2,'Coffee inn'),(22,'Fastaval Junior hjælp','Hjælp til opstilling og klargøring til Fastaval Junior','Fælleslokalet på D-gangen','Fastaval Junior setup','Setting up and prepping for Fastaval Junior',2,'The common area in the D-block'),(23,'Toasthalla - Lord of the Toast','Lord of the toast - Der skal laves toast og det skal være fedt!. Der skal skæres lidt grøntsager og ellers skal der klappes toast. Vi ses i køkkenet!','Køkkenet på gymnasiet','Toasthalla - Lord of the Toast','Lord of the toast - We\'re making toast and it will be epic! Vegetables need cutting and ingredients put together. See you in the kitchen1',2,'The kitchen'),(24,'Sushi-forberedelse','Sushi - så skal der rulles sushi! Vi sætter musik på og så står vi i køkkenet og ruller lækker sushi, når der ikke er mere sushi så stopper vagten, det bliver super hygge!','Køkkenet','Sushi-making','Sushi - let\'s roll some sushi! We\'ll put on some music and rock\'n\'roll some delicious sushi - when we\'re out of ingredients we\'re all done. It\'ll be great fun!',2,'The kitchen');
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
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gdsvagter`
--

LOCK TABLES `gdsvagter` WRITE;
/*!40000 ALTER TABLE `gdsvagter` DISABLE KEYS */;
INSERT INTO `gdsvagter` VALUES (5,4,2,'2018-03-28 08:00:00','2018-03-28 11:00:00'),(7,4,3,'2018-03-28 12:00:00','2018-03-28 15:00:00'),(8,4,3,'2018-03-28 16:00:00','2018-03-28 19:00:00'),(9,4,3,'2018-03-28 20:00:00','2018-03-28 23:00:00'),(10,4,2,'2018-03-29 08:00:00','2018-03-29 11:00:00'),(12,4,3,'2018-03-29 12:00:00','2018-03-29 15:00:00'),(13,4,3,'2018-03-29 16:00:00','2018-03-29 19:00:00'),(14,4,3,'2018-03-29 20:00:00','2018-03-29 23:00:00'),(15,4,2,'2018-03-30 08:00:00','2018-03-30 11:00:00'),(17,4,3,'2018-03-30 12:00:00','2018-03-30 15:00:00'),(18,4,3,'2018-03-30 20:00:00','2018-03-30 23:00:00'),(25,4,2,'2018-03-31 08:00:00','2018-03-31 11:00:00'),(26,4,3,'2018-03-31 12:00:00','2018-03-31 15:00:00'),(27,4,3,'2018-03-31 16:00:00','2018-03-31 19:00:00'),(73,13,4,'2018-03-29 15:00:00','2018-03-29 16:00:00'),(74,13,4,'2018-03-29 18:30:00','2018-03-29 19:30:00'),(75,12,4,'2018-03-30 12:00:00','2018-03-30 15:00:00'),(76,12,10,'2018-03-31 12:00:00','2018-03-31 15:00:00'),(79,1,5,'2018-03-31 16:00:00','2018-03-31 19:00:00'),(102,10,12,'2018-03-31 18:00:00','2018-03-31 22:00:00'),(105,1,4,'2018-03-31 10:00:00','2018-03-31 12:00:00'),(134,6,3,'2018-03-28 08:00:00','2018-03-28 10:45:00'),(135,6,3,'2018-03-29 08:00:00','2018-03-29 10:45:00'),(136,6,3,'2018-03-30 08:00:00','2018-03-30 10:45:00'),(137,6,3,'2018-03-31 08:00:00','2018-03-31 10:45:00'),(138,6,6,'2017-04-12 17:15:00','2017-04-12 19:45:00'),(139,6,6,'2018-03-28 17:15:00','2018-03-28 19:45:00'),(140,6,6,'2018-03-29 17:15:00','2018-03-29 19:45:00'),(141,6,6,'2018-03-30 17:15:00','2018-03-30 19:45:00'),(142,6,6,'2018-03-31 17:15:00','2018-03-31 19:45:00'),(143,7,5,'2018-03-28 10:00:00','2018-03-28 11:30:00'),(144,7,5,'2018-03-29 10:00:00','2018-03-29 11:30:00'),(145,7,5,'2018-03-30 10:00:00','2018-03-30 11:30:00'),(146,7,5,'2018-03-31 10:00:00','2018-03-31 11:30:00'),(147,7,6,'2017-04-12 18:00:00','2017-04-12 20:00:00'),(148,7,6,'2018-03-28 18:00:00','2018-03-28 20:00:00'),(149,7,6,'2018-03-29 18:00:00','2018-03-29 20:00:00'),(150,7,6,'2018-03-30 18:00:00','2018-03-30 20:00:00'),(151,7,6,'2018-03-31 18:00:00','2018-03-31 20:00:00'),(152,11,5,'2018-03-31 20:30:00','2018-03-31 23:30:00'),(153,4,3,'2018-03-30 16:00:00','2018-03-30 19:00:00'),(162,17,1,'2017-04-12 16:00:00','2017-04-12 18:00:00'),(163,17,1,'2017-04-12 18:00:00','2017-04-12 20:00:00'),(164,17,2,'2018-03-28 12:00:00','2018-03-28 14:00:00'),(165,17,2,'2018-03-28 14:00:00','2018-03-28 16:00:00'),(166,17,1,'2018-03-28 16:00:00','2018-03-28 18:00:00'),(167,17,1,'2018-03-28 18:00:00','2018-03-28 20:00:00'),(168,17,2,'2018-03-29 12:00:00','2018-03-29 14:00:00'),(169,17,2,'2018-03-29 14:00:00','2018-03-29 16:00:00'),(170,17,1,'2018-03-29 16:00:00','2018-03-29 18:00:00'),(171,17,1,'2018-03-29 18:00:00','2018-03-29 20:00:00'),(172,17,2,'2018-03-30 12:00:00','2018-03-30 14:00:00'),(173,17,2,'2018-03-30 14:00:00','2018-03-30 16:00:00'),(174,17,1,'2018-03-30 16:00:00','2018-03-30 18:00:00'),(175,17,1,'2018-03-30 18:00:00','2018-03-30 20:00:00'),(176,17,2,'2018-03-31 12:00:00','2018-03-31 14:00:00'),(177,17,2,'2018-03-31 14:00:00','2018-03-31 16:00:00'),(178,17,1,'2018-03-31 16:00:00','2018-03-31 18:00:00'),(179,17,1,'2018-03-31 18:00:00','2018-03-31 20:00:00'),(182,20,1,'2018-03-28 01:30:00','2018-03-28 03:30:00'),(185,20,1,'2018-03-29 01:30:00','2018-03-29 03:30:00'),(188,20,1,'2018-03-30 01:30:00','2018-03-30 03:30:00'),(191,20,1,'2018-03-31 01:30:00','2018-03-31 03:30:00'),(192,1,4,'2018-03-31 14:00:00','2018-03-31 17:00:00'),(193,1,2,'2018-03-31 15:00:00','2018-03-31 18:00:00'),(194,1,0,'2018-03-29 09:00:00','2018-03-29 10:00:00'),(195,12,1,'2018-03-29 10:35:00','2018-03-29 11:00:00'),(196,2,1,'2018-03-29 23:00:00','2018-03-30 01:00:00'),(197,2,1,'2018-03-30 23:00:00','2018-03-31 01:00:00'),(198,21,1,'2018-03-28 10:00:00','2018-03-28 12:00:00'),(199,21,1,'2018-03-29 10:00:00','2018-03-29 12:00:00'),(200,21,1,'2018-03-30 10:00:00','2018-03-30 12:00:00'),(201,21,1,'2018-03-31 10:00:00','2018-03-31 12:00:00'),(202,21,1,'2017-04-12 16:00:00','2017-04-12 18:00:00'),(203,21,1,'2018-03-28 16:00:00','2018-03-28 18:00:00'),(204,21,1,'2018-03-29 16:00:00','2018-03-29 18:00:00'),(205,21,1,'2018-03-30 16:00:00','2018-03-30 18:00:00'),(206,21,1,'2017-04-12 19:00:00','2017-04-12 21:00:00'),(207,21,1,'2018-03-28 19:00:00','2018-03-28 21:00:00'),(208,21,1,'2018-03-29 19:00:00','2018-03-29 21:00:00'),(209,21,1,'2018-03-30 19:00:00','2018-03-30 21:00:00'),(210,22,2,'2018-03-29 10:30:00','2018-03-29 13:30:00'),(211,24,3,'2018-03-28 10:00:00','2018-03-28 14:00:00'),(212,24,3,'2018-03-30 10:00:00','2018-03-30 14:00:00'),(213,23,3,'2018-03-29 12:00:00','2018-03-29 16:00:00'),(214,2,1,'2018-03-28 08:45:00','2018-03-28 12:00:00'),(215,2,1,'2018-03-30 14:45:00','2018-03-30 18:00:00'),(216,2,1,'2018-03-28 11:45:00','2018-03-28 15:00:00'),(217,2,1,'2018-03-28 14:45:00','2018-03-28 18:00:00'),(218,2,1,'2018-03-28 17:45:00','2018-03-28 21:00:00'),(219,2,1,'2018-03-28 20:45:00','2018-03-28 23:00:00'),(220,2,1,'2018-03-29 08:45:00','2018-03-29 12:00:00'),(221,2,1,'2018-03-29 11:45:00','2018-03-29 15:00:00'),(222,2,1,'2018-03-29 14:45:00','2018-03-29 18:00:00'),(223,2,1,'2018-03-29 17:45:00','2018-03-29 21:00:00'),(224,2,1,'2018-03-29 20:45:00','2018-03-29 23:00:00'),(225,2,1,'2018-03-30 08:45:00','2018-03-30 12:00:00'),(226,2,1,'2018-03-30 11:45:00','2018-03-30 15:00:00'),(227,2,1,'2018-03-30 17:45:00','2018-03-30 21:00:00'),(228,2,1,'2018-03-31 08:45:00','2018-03-31 12:00:00'),(229,2,1,'2018-03-31 11:45:00','2018-03-31 15:00:00'),(230,2,1,'2018-03-31 14:45:00','2018-03-31 18:00:00'),(231,2,1,'2018-03-31 17:45:00','2018-03-31 21:00:00'),(232,2,1,'2018-03-31 20:45:00','2018-03-31 23:00:00'),(233,2,1,'2018-03-30 20:45:00','2018-03-30 23:00:00'),(234,17,1,'2018-03-31 23:00:00','2018-04-01 01:00:00'),(235,17,5,'2018-04-01 01:00:00','2018-04-01 03:00:00'),(236,17,2,'2018-03-31 19:00:00','2018-03-31 23:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=623 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hold`
--


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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `idtemplates`
--

LOCK TABLES `idtemplates` WRITE;
/*!40000 ALTER TABLE `idtemplates` DISABLE KEYS */;
INSERT INTO `idtemplates` VALUES (4,'Deltager','/uploads/id_4_bg.png'),(6,'Infonaut','/uploads/id_6_bg.png'),(19,'Arrangør','/uploads/id_19_bg.png'),(20,'Dirtbuster','/uploads/id_20_bg.png'),(21,'Scenarie','/uploads/id_21_bg.png'),(22,'Kaffekrotjener','/uploads/id_22_bg.png'),(23,'Kioskninja','/uploads/id_23_bg.png'),(24,'Brætspil','/uploads/id_24_bg.png'),(25,'Special','/uploads/id_25_bg.png'),(26,'Grafiker og tema','/uploads/id_26_bg.png');
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
) ENGINE=InnoDB AUTO_INCREMENT=5320 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `idtemplates_items`
--

LOCK TABLES `idtemplates_items` WRITE;
/*!40000 ALTER TABLE `idtemplates_items` DISABLE KEYS */;
INSERT INTO `idtemplates_items` VALUES (4867,4,'barcode',315,472,350,100,0,''),(4868,4,'text',195,315,600,50,0,'name'),(4869,4,'text',390,180,200,50,0,'id'),(5220,20,'photo',138,276,213,295,0,''),(5221,20,'text',260,185,200,50,0,'id'),(5222,20,'text',125,643,500,50,0,'group'),(5223,20,'text',125,750,500,50,0,'workarea'),(5224,20,'barcode',192,881,350,100,0,''),(5230,21,'photo',138,276,213,295,0,''),(5231,21,'text',260,185,200,50,0,'id'),(5232,21,'text',125,643,500,50,0,'doctorname'),(5233,21,'text',126,750,500,50,0,'scenario'),(5234,21,'barcode',192,881,350,100,0,''),(5255,22,'photo',138,276,213,295,0,''),(5256,22,'text',260,185,200,50,0,'id'),(5257,22,'text',125,643,500,50,0,'doctorname'),(5258,22,'text',125,750,500,50,0,'workarea'),(5259,22,'barcode',192,881,350,100,0,''),(5265,23,'photo',138,276,213,295,0,''),(5266,23,'text',260,185,200,50,0,'id'),(5267,23,'text',125,643,500,50,0,'doctorname'),(5268,23,'text',125,750,500,50,0,'workarea'),(5269,23,'barcode',192,881,350,100,0,''),(5275,24,'photo',138,276,213,295,0,''),(5276,24,'text',260,185,200,50,0,'id'),(5277,24,'text',125,643,500,50,0,'doctorname'),(5278,24,'text',125,750,500,50,0,'scenario'),(5279,24,'barcode',192,881,350,100,0,''),(5285,25,'photo',138,276,213,295,0,''),(5286,25,'text',260,185,200,50,0,'id'),(5287,25,'text',125,643,500,50,0,'doctorname'),(5288,25,'text',125,750,500,50,0,'workarea'),(5289,25,'barcode',192,881,350,100,0,''),(5295,26,'photo',138,276,213,295,0,''),(5296,26,'text',260,185,200,50,0,'id'),(5297,26,'text',125,643,500,50,0,'doctorname'),(5298,26,'text',125,750,500,50,0,'workarea'),(5299,26,'barcode',192,881,350,100,0,''),(5300,6,'photo',138,276,213,295,0,''),(5301,6,'text',260,185,200,50,0,'id'),(5302,6,'text',125,643,500,50,0,'doctorname'),(5303,6,'text',126,750,500,50,0,'workarea'),(5304,6,'barcode',192,881,350,100,0,''),(5315,19,'photo',138,277,213,295,0,''),(5316,19,'text',260,185,200,50,0,'id'),(5317,19,'text',125,643,500,50,0,'doctorname'),(5318,19,'text',127,750,500,50,0,'workarea'),(5319,19,'barcode',192,881,350,100,0,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indgang`
--

LOCK TABLES `indgang` WRITE;
/*!40000 ALTER TABLE `indgang` DISABLE KEYS */;
INSERT INTO `indgang` VALUES (1,215,'2016-03-23 00:00:00','Indgang - Partout'),(2,95,'2016-03-23 00:00:00','Indgang - Partout - Alea'),(3,65,'2016-03-23 08:00:00','Indgang - Enkelt'),(4,65,'2016-03-24 08:00:00','Indgang - Enkelt'),(5,65,'2016-03-25 08:00:00','Indgang - Enkelt'),(6,65,'2016-03-26 08:00:00','Indgang - Enkelt'),(7,65,'2016-03-27 08:00:00','Indgang - Enkelt'),(8,190,'2016-03-23 00:00:00','Overnatning - Partout'),(9,65,'2016-03-23 22:00:00','Overnatning - Enkelt'),(10,65,'2016-03-24 22:00:00','Overnatning - Enkelt'),(11,65,'2016-03-25 22:00:00','Overnatning - Enkelt'),(12,65,'2016-03-26 22:00:00','Overnatning - Enkelt'),(13,65,'2016-03-27 22:00:00','Overnatning - Enkelt'),(14,100,'2016-03-23 00:00:00','Leje af madras'),(15,40,'2016-03-23 00:00:00','Indgang - Partout - Alea - Ung'),(16,160,'2016-03-23 00:00:00','Indgang - Partout - Ung'),(19,115,'2016-03-23 00:00:00','Overnatning - Partout - Arrangør'),(20,75,'2016-03-23 00:00:00','Alea medlemskab'),(21,90,'2016-03-27 20:00:00','Ottofest'),(22,160,'2017-03-23 00:00:00','Indgang - Partout - Arrangør'),(23,40,'2017-03-23 00:00:00','Indgang - Partout - Alea - Arrangør'),(24,110,'2016-03-27 00:00:00','Ottofest - Champagne'),(27,0,'2016-03-23 00:00:00','GRATIST Dagsbillet'),(28,0,'2016-03-24 00:00:00','GRATIST Dagsbillet'),(29,0,'2016-03-25 00:00:00','GRATIST Dagsbillet'),(30,0,'2016-03-26 00:00:00','GRATIST Dagsbillet'),(31,0,'2016-03-27 00:00:00','GRATIST Dagsbillet'),(32,0,'2016-03-23 00:00:00','GRATIST Overnatning'),(33,0,'2016-03-24 00:00:00','GRATIST Overnatning'),(34,0,'2016-03-25 00:00:00','GRATIST Overnatning'),(35,0,'2016-03-26 00:00:00','GRATIST Overnatning'),(36,0,'2016-03-27 00:00:00','GRATIST Overnatning'),(37,10,'2016-03-23 00:00:00','Bankoverførselsgebyr'),(38,50,'2016-03-23 00:00:00','Dørbetalingsgebyr'),(39,0,'2016-03-23 00:00:00','Indgang - Partout - Barn'),(40,15,'2016-01-30 00:00:00','Billetgebyr'),(41,80,'2016-03-27 00:00:00','Ottofest - hvidvin'),(42,80,'2016-03-27 00:00:00','Ottofest - rødvin'),(43,190,'2017-04-12 00:00:00','Campingvogn'),(44,115,'2017-04-12 00:00:00','Campingvogn - Arrangør'),(45,100,'2017-04-12 00:00:00','Indgang - Partout - Junior');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanevents`
--


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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loanitems`
--

LOCK TABLES `loanitems` WRITE;
/*!40000 ALTER TABLE `loanitems` DISABLE KEYS */;
INSERT INTO `loanitems` VALUES (1,'100','Wookie 1','Fastaval',''),(2,'1','Wookie12','Info',''),(3,'8435213716905','Cherryblommetomater','Nynne','Nynnes fåcking tomater'),(4,'17002899','Nynne arrangørkort','Nynne','?');
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
) ENGINE=InnoDB AUTO_INCREMENT=34984 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--


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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokaler`
--

LOCK TABLES `lokaler` WRITE;
/*!40000 ALTER TABLE `lokaler` DISABLE KEYS */;
INSERT INTO `lokaler` VALUES (1,'Fællesområde','','','ja','nej',0),(2,'Brætspil','','','ja','nej',0),(3,'Samlingssal (Ottos kaffekro ved den hvide tavle)','','','ja','nej',0),(4,'A-gang','','','ja','nej',0),(5,'A08','','','ja','nej',0),(7,'B32','','','ja','nej',0),(8,'B34','','','ja','nej',0),(9,'B36','','','ja','nej',0),(10,'B38','','','ja','nej',0),(11,'B39','','','ja','nej',0),(12,'B41','','','ja','nej',0),(13,'B43','','','ja','nej',0),(14,'B44 ','','','ja','nej',0),(15,'B46 ','','','ja','nej',0),(16,'B47','','','ja','nej',0),(17,'C53','Kælder','','ja','nej',0),(18,'D60','','','ja','nej',0),(19,'D61','','','ja','nej',0),(20,'D62','','','ja','nej',0),(21,'D65','','','ja','nej',0),(22,'D66','','','ja','nej',0),(23,'D67','','','ja','nej',0),(24,'1.01 - Info','Info','','ja','nej',0),(25,'1.02','','','ja','nej',0),(27,'1.04','','','ja','nej',0),(28,'1.05','','','ja','nej',0),(29,'2.01','','','ja','nej',0),(30,'2.02','','','ja','nej',0),(31,'2.03','','','ja','nej',0),(32,'2.04','','','ja','nej',0),(34,'2.06','','','ja','nej',0),(35,'2.09','','','ja','nej',0),(36,'Svømmehal','','','ja','nej',0),(37,'E70','TV','','nej','nej',0),(38,'FastaWar','','','ja','nej',0),(39,'Baren','','','ja','nej',0),(43,'X Fiktivt lokale 6','','','ja','nej',0),(44,'C58 - Formning','\"begrænset brug\"','','ja','nej',0),(45,'X Fiktivt lokale 5','','','ja','nej',0),(46,'X Fiktivt lokale 1','','','ja','nej',0),(47,'X Fiktivt lokale 2','','','ja','nej',0),(48,'X Fiktivt lokale 3','','','ja','nej',0),(49,'X Fiktivt lokale 4','','','ja','nej',0),(50,'X Fiktivt lokale 7','','','ja','nej',0),(51,'X Fiktivt lokale 8','','','ja','nej',0),(52,'X Fiktivt lokale 9','','','ja','nej',0),(53,'X Fiktivt lokale 10','','','ja','nej',0),(54,'B45','','','nej','nej',0),(55,'A 07','','','ja','nej',0),(56,'B 30 - Artemis','Artemis','','ja','nej',0),(59,'C 50','Kælder','','ja','nej',0),(60,'C 51','Kælder','','ja','nej',0),(61,'C 55','Kælder','','ja','nej',0),(62,'Cykelkælder','','','ja','nej',0),(63,'Caféen','','','ja','nej',0),(64,'Brætspilscafeen','','','ja','nej',0),(65,'D-gang - Magic','Magic','','ja','nej',0),(66,'C48','Brandvagts sovesal','','nej','nej',10),(67,'Arrangørsovesal','','','nej','nej',50),(68,'Ungdomssovesal','','','nej','nej',50),(69,'C58','Hinterlandet','','ja','nej',0),(70,'Sovesal','Idrætscenteret','','nej','nej',362),(71,'E71','','','ja','nej',0),(72,'Vandrehjem/Hostel','','','ja','nej',0),(73,'Pathfinder','','','ja','nej',0),(74,'2.05','','','ja','nej',0),(75,'2.24','','','ja','nej',0),(76,'C57','','','nej','nej',0),(77,'B40','','','nej','nej',0),(78,'A10','','','nej','nej',0),(80,'B40','HInterlandet','','ja','nej',0);
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
INSERT INTO `mad` VALUES (1,'Aftensmad',55,'Regular'),(2,'Aftensmad - vegetar',55,'Vegetarian'),(4,'Morgenmad',25,'Breakfast');
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
INSERT INTO `madtider` VALUES (145,1,'2017-04-12 17:30:00','Lasagne','Lasagne'),(146,1,'2018-03-28 17:30:00','Grillet kyllingebryst med ris og paprikasauce','Grilled chicken breast with rice and paprika sauce'),(147,1,'2018-03-29 17:30:00','Hakkebøf med bløde løg og hvide kartofler','Mincemeat steak with fried onions and boiled potatoes'),(148,1,'2018-03-30 17:30:00','Kylling i karry med løse ris','Chicken curry with rice'),(149,1,'2018-03-31 17:30:00','Skinke og flødekartofler','Ham and creamed potatoes'),(150,2,'2017-04-12 17:30:00','Vegetarlasagne','Vegetarian lasagna'),(151,2,'2018-03-28 17:30:00','Qournbøf med tilbehør','Qournsteak with extras'),(152,2,'2018-03-29 17:30:00','Paneret jordnødderulle','Fried peanut roll'),(153,2,'2018-03-30 17:30:00','Karryret med strimlet qourn','Curry with quorn'),(154,2,'2018-03-31 17:30:00','Squashtærte med æg og ost','Squashquiche with egg and cheese'),(160,4,'2018-03-28 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(161,4,'2018-03-29 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(162,4,'2018-03-30 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.'),(163,4,'2018-03-31 08:00:00','Klassisk buffet med fx. cornflakes,havregrød, youghurt, frugt, grovbrød og franskbrød, pålæg, ost, kaffe og te.','Buffet: cornflakes, oatmeal, yogurt, fruit, wholegrain + wheat bread, butter, cold cuts, cheese, coffee, tea.');
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
INSERT INTO `migrations` VALUES (1001),(1002),(1003),(1004),(1005),(1006),(1007),(1008),(1009),(1010),(1011),(1012),(1013),(1014),(1015),(1016),(1017),(1018),(1019),(1020),(1021);
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
INSERT INTO `notes` VALUES ('boardgames','Samlet udlån under FastavalStats står til 0 hele tiden (det andet ser rigtigt ud).\n\nGod idé hvis vi kan se brætspilsaktiviteter under brætspilsfanen - også designerspil - så vi f.eks. kan se hvor mange tilmeldte der er til de forskellige ting. Aktivitetsblokken på fanen har ikke vist noget i år (det gjorde den sidste år).\n\nMan kan tilsyneladende ikke rette i ejer eller kommentarer af/til spil i spillisten?\n\n\nDer skal laves en tjekliste til brætspil. Når vi hver morgen tjekker om alle spil er hjemme, så tager det lang tid at gå lisen igennem som det er nu. En afkrydsningsliste af en art vil døre alt meget lettere.\n','2018-03-30 19:26:12');
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
) ENGINE=InnoDB AUTO_INCREMENT=695 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentfritidlog`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=3032 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules_votes`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=5055 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smslog`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'peter.e.lind@gmail.com','$2y$10$LVCn0lpFfHPVViCjKXzmZ.2lPkAFY0BEaBYIkK7x7sfUCHG4nBxLW','nej','54d0fd83f0eb8f9127985f55af999046','2015-11-23 22:04:05'),(47,'simonsteen@yahoo.dk','$2y$10$c.Vq1HsdwbZLzckvF23NPuXUGJgh/Rb3MhVJBDzDIusyA0w5n1IIi','nej','9cdea5338609c120cf0762d0ec2185ea','0000-00-00 00:00:00'),(48,'ego@kristoffermads.dk','$2y$10$AgYJtphpQj3iCZOBUDdUWu7ONdbgwBy6HC76WC3xLHH9K1panbqOO','ja','02c949c1fe95d3e76f3333231284d407','0000-00-00 00:00:00'),(49,'afrostb@hotmail.com','$2y$10$ueY1k9vAqIaOLVx2WAzG8.yc6x5/llPkqCERM48auY2Hh36AyUfcC','ja','5113163d642f35ab323ed611e989f8f6','0000-00-00 00:00:00'),(52,'ak47@762.dk','$2y$10$M24UFSMOeAvdaPnpI6oX.uJBrENygOmmYzOgcuEuP/NI9.kg53UhK','nej','2ef0d9d8f23ed5af6874edbaa3e5b9a3','0000-00-00 00:00:00'),(53,'Vinterulven@gmail.com','$2y$10$Ilvgmzq4g4gpvFaxWVtJte3HCbcHDvxf5BmNXqmTC3WBMh3gTMon.','nej','1af6af319078f0e1cb5cba98b9d8696c','0000-00-00 00:00:00'),(54,'charlesbn@gmail.com','$2y$10$W3qJTpzBVdVrLYUuQwiqveopcjTfy3BwXekSVu1EbQuVPqhW9UvdS','nej','9925e9d69c87e9b91e97d05907e4d9a8','0000-00-00 00:00:00'),(55,'pbrichs@gmail.com','$2y$10$6NbjsFPW3w8eSboFF2EKkeeHKfVhYCtMFVASSrtSGLmnw9c8Pg97G','nej','0ed1dd6c02ef8b7ca144d77a7ff5e415','0000-00-00 00:00:00'),(56,'wombattus@hotmail.com','$2y$10$ifkCQ9KZ7gub/aKKVWURJeg5MWfABmAoj7XBd8ao6K.26xS1XWtg6','nej','91bef3ed9012bac5596d28f6eba09bfa','0000-00-00 00:00:00'),(58,'antonsenchristian@gmail.com','$2y$10$1zJUSXd9yciPcDJrAN1eNOGZlAGbReo.bdclxHxbovSswIu7XaPie','nej','8b280aa136a698a9fad7da39e1cfa38e','0000-00-00 00:00:00'),(59,'shamgeez@gmail.com','$2y$10$uRwXgoNjPjyNeWB3nEjQ1OECSJsJ0sU0VsgEp.l2.7DjpV7skEohu','nej','476791fc45f0fdd372b3b8b3943fda4d','0000-00-00 00:00:00'),(61,'andreasskovse@gmail.com','$2y$10$l6Q9nnwszpaY6ezrfqjBPOduZBNsMhglnS821xV4p9xXAEKYLp65m','nej','98a2e7908571f10014f69d13e179b0bf','0000-00-00 00:00:00'),(85,'mad@fastaval.dk','$2y$10$sMt1opS2qbX11Su5LQI9/OfslrIZkSzx4NnYOccbiFjpDoi3fu1pC','ja','','0000-00-00 00:00:00'),(86,'jonas.rassing@gmail.com','$2y$10$osw3c3xEb9Y5vM3i/Rl1Vurhmthh1bacUUBDRN8bCgiqbA6kCVzd.','ja','fea7e671370960f6cc844dc860690bb5','0000-00-00 00:00:00'),(87,'johsbusted@gmail.com','$2y$10$6b9lvct49ZN8fKElg15P.OIt9777Q8d8X/ton5kSLnmbaOrCNCRO2','ja','582538122a3e303991c35d953e788ff1','0000-00-00 00:00:00'),(89,'karenkbs@hotmail.com','$2y$10$6XSW0PkUWjhP5JQhC.9av.M1/hP9J8f6ylN8weRbBOjJPqMYFOLey','ja','6282dea22c0ac141cd1024b12f532483','0000-00-00 00:00:00'),(90,'realmoller@gmail.com','$2y$10$fg4K/nmein2IpwcJzx.FbeE4x.YF2slx/2JXUYroH2r91e653/xjC','ja','ee9078c0cfb65263b2df56defd55292a','0000-00-00 00:00:00'),(91,'flakesdk@gmail.com','$2y$10$BxIFN6tv.QFpY.u52hyhZOXpV2ldhxo9s96YUAMBkfFj7F9mqpgg2','ja','2ad3d81404d7f71d8453d00a6886e496','0000-00-00 00:00:00'),(92,'ragnvald42@gmail.com','$2y$10$TGkn5u9o9phbJ0zbnjD8WOA3KVkjd6Nf9atRRQmOZ.kMX6uloX84y','nej','8f9568ccead58d657555f11d4aebd6c3','0000-00-00 00:00:00'),(93,'pixi.ixiq@gmail.com','$2y$10$HYEFLJ1xm9voDzb5J9yNgOp82rpvagDXcCyCHMgSU94urhMPU99hS','ja','be7e45d5ad3efbe779bcd25af2cdbcd3','0000-00-00 00:00:00'),(95,'nynnebrandt0125@gmail.com','$2y$10$QNIAg19IctXF4Kl6qMNUcuTQ9vCrvOadnV1pef5w5qL.VYlemjgjS','ja','8aba46cfc89d5a0cc1470106b1f56263','0000-00-00 00:00:00'),(96,'martin@hornpedersen.dk','$2y$10$0Xs0gABWFOWhar7LoQcabuH3IXn1kfm2MTL/z.A4em.GX90g3KU4a','ja','1d693b5a4b7123e32e36e361bebc1e04','0000-00-00 00:00:00'),(97,'edinsumar@gmail.com','f28160278d0b48d0d71ff50d8a07ab80','ja','','0000-00-00 00:00:00'),(100,'testinfonaut@test.test','$2y$10$zljGmo9JBWzBroHHN3l14.q8uuWY47clVjQ1INiCb98jF3uaXxO6y','ja','','0000-00-00 00:00:00'),(102,'gdsreadonly@fastaval.dk','$2y$10$sLWIQe1HnNa1j1cyTNsu/u4C2EWkbLzW2GQHcSdUOKz0sIitQTdva','ja','','0000-00-00 00:00:00'),(103,'mendeskmc@gmail.com','$2y$10$b/jm8tMjgXpMkwrxiFDUwOdvpKuS06wz4ORX8H4pe.OaRMl.X2ABe','ja','cf811e92fe6dc6575f58df4e8151a5ce','0000-00-00 00:00:00'),(104,'jonasjpg@hotmail.com','$2y$10$GqnMSbnSIDt4YRYbUiW.2e4TJQuwDQRrKg0x49kL7Nsf4aqHpgRJu','ja','8db2dde0917ed1e5f3e8a860c6761980','0000-00-00 00:00:00'),(107,'ninakongsdal88@hotmail.com','$2y$10$2AW49z3Gy2.BFqMVxj/4WeFQK1wLVY9dCY4M4FPF2Amg81sinICAS','ja','5f256187767e73bba1a2d1629500aa67','0000-00-00 00:00:00'),(109,'kalle.hunnerup@gmail.com','$2y$10$6B7.PvwWWb8akrxfIEiF8.gHG5jDn7DSABGpZ.gR22MSktl3L2oAi','ja','b2bd67ee928129253af9c944b661159f','0000-00-00 00:00:00'),(110,'markbuje@gmail.com','$2y$10$rE0PVYrOs5OrqFnSgEmiWezQdpr2tA5sUQ36zi7R.9tzzEmzAJoky','ja','7b94f6f88a570407cf722b9f0fe86862','0000-00-00 00:00:00'),(111,'Love-kvik@hotmail.com','$2y$10$vc60AfSjYMyuvz2xMIbGheSKqK1OlE3LVcJlXO9JRD9rJkxWugLOe','ja','5c85aeb91dde3e131c1ce1b4c7c8ae42','0000-00-00 00:00:00'),(112,'Magnusklaesoe@gmail.com','$2y$10$/y6b.CRtudCFcdFXq5m15eFNihEU.mKbtJNrNfi5Eg.c0Z.enc4Pi','ja','32f41c6f99ee6c7084d971f85c51131c','0000-00-00 00:00:00'),(114,'kierans@live.dk','$2y$10$zVPsdELVPGwx25uLbthJcetetrOphzRsICKm2Bs8f8FHXJ/5CXRw.','ja','f1e202446c657b291f9fa1760c8af7bb','0000-00-00 00:00:00'),(115,'mikkel@onielsen.dk','$2y$10$IzHClnJeGs7/VmSTbfz/2e3UznFMNpvkFNwtWiPl74SozAu4Y1z6K','ja','fbdae5c1391bccdf5c2f533376e4d5cc','0000-00-00 00:00:00'),(117,'Regitsej@gmail.com','$2y$10$bzF2h1IwHksQeaJbTHjZie..yZ2hYnfw2SyAbRa7OWBbtm/MQ49Re','ja','7c9a2de854830187473c4fb376636a8d','0000-00-00 00:00:00'),(118,'braetspilscafeen@fastaval.dk','$2y$10$J/L7RdH7uLhtCtVv16lbne7cEz05TSjNM5hHVi.9xDlmkGFBIhuNm','ja','','0000-00-00 00:00:00'),(119,'sunel92@gmail.com','$2y$10$N/tYhS/40V.7Bg0KC.fNPuNtRG1znFSwLyYlNy3mVFSEtt2H6O1RO','ja','69ab256a5e0da92ff2c98546ead91d04','0000-00-00 00:00:00'),(120,'mail@alacho.no','$2y$10$C9K5XnNYN3lJwwTQWGWPV.t6gKb4nudQKXer/I.19vPaRK29miJR.','ja','5f3ef46f7fa9297209420be22620afd4','0000-00-00 00:00:00'),(121,'Cecilietorp02@gmail.com','$2y$10$Tk09zWih4RZErcAzeVHlouZn68nLrh0A/NTmwK2YqfJxPWHiDc4lS','ja','1a6848000373e67b5f168dd7c0468ec8','0000-00-00 00:00:00'),(122,'Christian.ursell.smith@gmail.com','$2y$10$BFXWY2gRQth9sloKNF/AN.H1QFv/4SRgh0KzuSwaShfoyRPEUNYEK','ja','370df8309e57ab3bc499d42eea1cd57b','0000-00-00 00:00:00'),(123,'ponsgaard@gmail.com','$2y$10$7hJCWbmV.du02vFtrw7tyOrApuYrILs1ZKr9lWx.Y4jpkRXP3SF5G','ja','ff66806471453a651eca765ce0410af8','0000-00-00 00:00:00'),(124,'larsvilhelmsen@gmail.com','$2y$10$opxots/9ByWgfpkhR6cnW.Jzriwl12ySBFWLz7FIA0I/JTBBpRzM2','ja','0dbb4aaeba7498d4afb508d6df6e8528','0000-00-00 00:00:00'),(125,'1@makey.biz','$2y$10$qI6ESgX4AjuVEgI4JFcZfuNkmquxo9zw.F4lqvPHfyl390oGPVCHC','nej','2d8c78091b7e92f8d5f71d9585839551','0000-00-00 00:00:00'),(126,'abel.kathrine@gmail.com','96ad2c87af8ffd7b7c917454b8737de7','nej','','0000-00-00 00:00:00'),(127,'comfnug@gmail.com','25a7ffaab1b6f2feda4714b7adf13a63','nej','','0000-00-00 00:00:00');
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
INSERT INTO `users_roles` VALUES (1,7),(48,7),(58,7),(91,7),(47,8),(49,8),(52,8),(53,8),(54,8),(55,8),(56,8),(126,8),(127,8),(58,9),(59,9),(61,9),(86,9),(89,9),(90,9),(93,9),(95,9),(100,9),(103,9),(104,9),(107,9),(109,9),(110,9),(111,9),(112,9),(114,9),(115,9),(117,9),(119,9),(120,9),(121,9),(122,9),(85,10),(47,11),(49,11),(87,11),(96,11),(97,11),(123,11),(126,11),(127,11),(86,13),(91,13),(124,13),(125,13),(91,14),(59,17),(58,18),(59,18),(93,18),(95,18),(110,18),(92,19),(118,19),(86,20),(86,22),(102,22);
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wear`
--

LOCK TABLES `wear` WRITE;
/*!40000 ALTER TABLE `wear` DISABLE KEYS */;
INSERT INTO `wear` VALUES (1,'T-Shirt (dame)','XS-3XL','','T-Shirt (ladies)',''),(2,'Crew T-Shirt (dame)','XS-3XL','','Crew T-Shirt (ladies)',''),(3,'Infonaut T-Shirt','XS-3XL','','Infonaut T-Shirt',''),(4,'Kiosk T-Shirt (dame)','XS-3XL','','Kiosk T-Shirt (ladies)',''),(7,'Hættetrøje','XXS-2XL','','Hoodie',''),(18,'Fastakruset','M-M','','The Fastaval Mug',''),(19,'Junior T-Shirt (Dame)','2ÅR-JuniorXS','','Junior T-Shirt (Female)',''),(20,'Mulepose','M-M','Indkøbsnet/mulepose','Tote bag','Fastaval branded tote bag'),(21,'T-Shirt (herre)','XS-3XL','','T-Shirt (mens)',''),(22,'Crew T-Shirt (herre)','XS-3XL','','Crew T-Shirt (mens)',''),(23,'Kiosk T-Shirt (herre)','XS-3XL','','Kiosk T-Shirt (mens)',''),(24,'Kaffekro T-Shirt','XS-3XL','','CoffeInn T-Shirt',''),(25,'Junior T-Shirt (Mand)','2ÅR-JuniorXS','','Junior T-Shirt (Male)',''),(26,'Junior T-Shirt (pige)','2ÅR-JuniorXS','','Junior T-Shirt (girl)',''),(27,'Junior T-Shirt (dreng)','2ÅR-JuniorXS','','Junior T-Shirt (boy)','');
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
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wearpriser`
--

LOCK TABLES `wearpriser` WRITE;
/*!40000 ALTER TABLE `wearpriser` DISABLE KEYS */;
INSERT INTO `wearpriser` VALUES (140,1,1,125),(141,1,2,95),(142,1,8,95),(147,3,3,0),(148,4,9,0),(163,2,2,95),(164,2,8,95),(169,18,1,65),(170,18,2,65),(171,18,8,65),(172,18,6,65),(173,18,4,65),(174,18,3,65),(175,18,9,65),(197,7,1,275),(198,7,2,275),(199,7,8,275),(212,2,6,95),(213,2,4,95),(214,2,3,95),(215,2,9,95),(216,7,3,275),(217,7,6,275),(218,7,4,275),(219,7,9,275),(220,1,3,95),(221,1,6,95),(222,1,4,95),(223,1,9,95),(224,19,1,115),(225,19,2,95),(226,19,8,95),(227,19,6,95),(228,19,4,95),(229,19,3,95),(230,19,9,95),(231,7,10,275),(232,18,10,65),(233,18,11,65),(234,7,11,275),(235,20,9,65),(236,20,2,65),(237,20,8,65),(238,20,1,65),(239,20,6,65),(240,20,4,65),(241,20,3,65),(242,20,10,65),(243,20,11,65),(244,1,11,95),(245,1,10,95),(246,21,9,95),(247,21,2,95),(248,21,8,95),(249,21,1,125),(250,21,6,95),(251,21,4,95),(252,21,3,95),(253,21,10,95),(254,21,11,95),(255,22,9,95),(256,22,2,95),(257,22,8,95),(259,22,6,95),(260,22,4,95),(261,22,3,95),(263,22,11,95),(264,2,11,95),(267,23,9,0),(268,24,11,0),(269,19,11,95),(270,19,10,115),(271,25,9,95),(272,25,2,95),(273,25,8,95),(274,25,1,115),(275,25,6,95),(276,25,4,95),(277,25,3,95),(278,25,10,115),(279,25,11,95),(280,26,10,0),(281,27,10,0);
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

-- Dump completed on 2017-10-22 11:20:15
