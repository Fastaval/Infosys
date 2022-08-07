CREATE TABLE `diyageranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diy_id` int(11) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `requirementtype` enum('min','max','exact') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `diy_requirement` (`diy_id`,`requirementtype`),
  CONSTRAINT `diyageranges_ibfk_1` FOREIGN KEY (`diy_id`) REFERENCES `gds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
