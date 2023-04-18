CREATE TABLE loanitems (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `loanevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loanitem_id` int(10) unsigned NOT NULL,
  `type` enum('created','borrowed','returned','finished') NOT NULL DEFAULT 'created',
  `timestamp` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `loanitem` (`loanitem_id`),
  CONSTRAINT `loanevents_ibfk_1` FOREIGN KEY (`loanitem_id`) REFERENCES `loanitems` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

 ALTER TABLE notes MODIFY COLUMN  area ENUM('shop', 'boardgames', 'loans') NOT NULL;

