CREATE TABLE messages (
  id INT NOT NULL AUTO_INCREMENT,
  text_da TEXT,
  text_en TEXT,
  send_time DATETIME,
  PRIMARY KEY(id)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE participant_messages (
  participant_id INT(11) NOT NULL,
  message_id INT,
  PRIMARY KEY(participant_id, message_id),
  CONSTRAINT `participant_messages_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`),
  CONSTRAINT `participant_messages_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

DROP TABLE smslog;
CREATE TABLE smslog (
  phone_number INT NOT NULL,
  message_id INT NOT NULL,
  PRIMARY KEY (phone_number, message_id),
  CONSTRAINT smslog_messages_ibfk_1 FOREIGN KEY (message_id) REFERENCES messages(id)
) engine=InnoDB DEFAULT CHARSET utf8mb4;
