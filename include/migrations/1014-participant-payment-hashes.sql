DROP TABLE IF EXISTS participantpaymenthashes;
CREATE TABLE participantpaymenthashes (
    participant_id INT(11) NOT NULL PRIMARY KEY,
    hash CHAR(32) NOT NULL,
    CONSTRAINT FOREIGN KEY participant (participant_id) REFERENCES deltagere (id) ON UPDATE CASCADE ON DELETE CASCADE
) engine=innodb default character set utf8mb4;
