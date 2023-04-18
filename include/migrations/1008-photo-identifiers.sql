CREATE TABLE participantphotoidentifiers (
  `participant_id` int(11) NOT NULL PRIMARY KEY,
  `identifier` TEXT(32) NOT NULL,
  CONSTRAINT FOREIGN KEY participant (participant_id) REFERENCES deltagere (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
