DROP TABLE translations;

CREATE TABLE translations (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  label VARCHAR(64),
  lang char(2),
  string TEXT
) engine=InnoDB DEFAULT CHARSET utf8mb4;