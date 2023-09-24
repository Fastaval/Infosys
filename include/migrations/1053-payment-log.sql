DROP TABLE IF EXISTS paymentfritidlog;
DROP TABLE IF EXISTS participantpaymenthashes;

CREATE TABLE payment_log (
  id INT NOT NULL AUTO_INCREMENT,
  participant_id INT NOT NULL,
  amount INT NOT NULL,
  payment_id CHAR(36) NOT NULL,
  auth_id CHAR(36) DEFAULT NULL,
  created INT,
  completed INT DEFAULT NULL,
  token CHAR(40),
  status ENUM('pending', 'cancelled', 'confirmed', 'failed') DEFAULT 'pending',
  PRIMARY KEY(id),
  UNIQUE (payment_id),
  UNIQUE (auth_id),
  UNIQUE (token),
  CONSTRAINT `payment_log_ibfk` FOREIGN KEY (`participant_id`) REFERENCES `deltagere` (`id`)
) engine=InnoDB DEFAULT CHARSET utf8mb4;