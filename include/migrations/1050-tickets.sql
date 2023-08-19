CREATE TABLE tickets (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name varchar(64),
  category INT UNSIGNED NOT NULL DEFAULT 0,
  priority INT UNSIGNED NOT NULL DEFAULT 0,
  status INT UNSIGNED NOT NULL DEFAULT 0,
  open BOOL NOT NULL DEFAULT true,
  creator int(11) NOT NULL,
  assignee int(11) NOT NULL,
  CONSTRAINT tickets_ibfk_1 FOREIGN KEY (creator) REFERENCES users(id),
  CONSTRAINT tickets_ibfk_2 FOREIGN KEY (assignee) REFERENCES users(id)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE tickets_messages (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user int(11),
  ticket INT UNSIGNED,
  posted INT UNSIGNED,
  last_edit INT UNSIGNED DEFAULT NULL,
  message TEXT,
  CONSTRAINT tickets_messages_ibfk_1 FOREIGN KEY (ticket) REFERENCES tickets(id),
  CONSTRAINT tickets_messages_ibfk_2 FOREIGN KEY (user) REFERENCES users(id)
) engine=InnoDB DEFAULT CHARSET utf8mb4;