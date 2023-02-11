CREATE TABLE messages (
  id INT NOT NULL AUTO_INCREMENT,
  text_da TEXT,
  text_en TEXT,
  send_time DATETIME,
  PRIMARY KEY(id)
);

CREATE TABLE participant_messages (
  participant_id INT(11) DEFAULT NULL,
  message_id INT,
  PRIMARY KEY(participant_id, message_id),
  CONSTRAINT `participant_messages_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `messages` (`id`)
);

DROP TABLE smslog;
CREATE TABLE smslog (
  phone_number INT NOT NULL,
  message_id INT NOT NULL,
  PRIMARY KEY (phone_number, message_id),
  CONSTRAINT smslog_messages_ibfk_1 FOREIGN KEY (message_id) REFERENCES messages(id)
);