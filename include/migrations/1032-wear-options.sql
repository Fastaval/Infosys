CREATE TABLE wear_sizes (
  size_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  size_order INT UNSIGNED,
  size_name_da VARCHAR(32),
  size_name_en VARCHAR(32)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

-- No size;
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Ingen størrelse', 'No size');
-- Baby sizes;
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 56(0-1 md)', 'Baby 56(0-1 mo)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 62(2-3 md)', 'Baby 62(2-3 mo)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 68(3-6 md)', 'Baby 68(3-6 mo)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 74(6-9 md)', 'Baby 74(6-9 mo)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 80(9-12 md)', 'Baby 80(9-12 mo)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Baby 86/92(1-2 år)', 'Baby 86/92(1-2 yr)');
-- Kid sizes;
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Barn 98/104(3-4 år)', 'Child 98/104(3-4 yr)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Barn 110/116(5-6 år)', 'Child 110/116(5-6 yr)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Barn 122/128(7-8 år)', 'Child 122/128(7-8 yr)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Barn 134/146(9-11 år)', 'Child 134/146(9-11 yr)');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('Barn 152/164(12-14 år)', 'Child 152/164(12-14 yr)');
-- Adult sizes;
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('S', 'S');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('M', 'M');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('L', 'L');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('XL', 'XL');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('XXL', 'XXL');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('3XL', '3XL');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('4XL', '4XL');
INSERT INTO wear_sizes (size_name_da, size_name_en) VALUES ('5XL', '5XL');
SET @rn = 0; UPDATE wear SET wear_order = (@rn:=@rn+1);

CREATE TABLE wear_colors (
  color_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  color_name_da VARCHAR(32),
  color_name_en VARCHAR(32)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE wear_color_available (
  wear_id INT,
  color_id INT UNSIGNED,
  FOREIGN KEY (wear_id) REFERENCES wear(id),
  FOREIGN KEY (color_id) REFERENCES wear_colors(color_id)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

ALTER TABLE wear DROP COLUMN size_range;
ALTER TABLE wear ADD min_size INT UNSIGNED DEFAULT 1;
ALTER TABLE wear ADD FOREIGN KEY (min_size) REFERENCES wear_sizes(size_id);
ALTER TABLE wear ADD max_size INT UNSIGNED DEFAULT 1;
ALTER TABLE wear ADD FOREIGN KEY (max_size) REFERENCES wear_sizes(size_id);
ALTER TABLE wear ADD wear_order INT UNSIGNED;
SET @rn = 0; UPDATE wear SET order = (@rn:=@rn+1);

ALTER TABLE deltagere_wear DROP COLUMN size;
ALTER TABLE deltagere_wear ADD size INT UNSIGNED;
UPDATE deltagere_wear SET size = 1;
ALTER TABLE deltagere_wear ADD FOREIGN KEY (size) REFERENCES wear_sizes(size_id);
