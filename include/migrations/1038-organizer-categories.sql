ALTER TABLE deltagere ADD author set('role','board');

CREATE TABLE organizer_categories (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name_da TEXT,
  name_en TEXT
) engine=InnoDB DEFAULT CHARSET utf8mb4;

ALTER TABLE deltagere DROP COLUMN arbejdsomraade;
ALTER TABLE deltagere ADD COLUMN work_area INT UNSIGNED DEFAULT NULL;
ALTER TABLE deltagere ADD CONSTRAINT deltagere_ibfk_3 FOREIGN KEY (work_area) REFERENCES organizer_categories(id);
