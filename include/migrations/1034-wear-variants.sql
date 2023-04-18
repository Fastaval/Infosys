ALTER TABLE wear DROP FOREIGN KEY wear_ibfk_1;
ALTER TABLE wear DROP COLUMN min_size;
ALTER TABLE wear DROP FOREIGN KEY wear_ibfk_2;
ALTER TABLE wear DROP COLUMN max_size;
ALTER TABLE wear CHANGE wear_order position INT(10) unsigned;
ALTER TABLE wear ADD COLUMN max_order INT(10) unsigned;

ALTER TABLE deltagere_wear DROP FOREIGN KEY deltagere_wear_ibfk_3;
ALTER TABLE deltagere_wear DROP COLUMN size;

DROP TABLE wear_sizes;
DROP TABLE wear_color_available;
DROP TABLE wear_colors;

CREATE TABLE wear_attributes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    attribute_type VARCHAR(10),
    desc_da TEXT,
    desc_en TEXT,
    position INT(11),
    PRIMARY KEY (`id`)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

-- Baby sizes;
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 56(0-1 md)', 'Baby 56(0-1 mo)', 1);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 62(2-3 md)', 'Baby 62(2-3 mo)', 2);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 68(3-6 md)', 'Baby 68(3-6 mo)', 3);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 74(6-9 md)', 'Baby 74(6-9 mo)', 4);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 80(9-12 md)', 'Baby 80(9-12 mo)', 5);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Baby 86/92(1-2 år)', 'Baby 86/92(1-2 yr)', 6);
-- Kid sizes;
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Barn 98/104(3-4 år)', 'Child 98/104(3-4 yr)', 7);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Barn 110/116(5-6 år)', 'Child 110/116(5-6 yr)', 8);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Barn 122/128(7-8 år)', 'Child 122/128(7-8 yr)', 9);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Barn 134/146(9-11 år)', 'Child 134/146(9-11 yr)', 10);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'Barn 152/164(12-14 år)', 'Child 152/164(12-14 yr)', 11);
-- Adult sizes;
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'S', 'S', 12);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'M', 'M', 13);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'L', 'L', 14);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'XL', 'XL', 15);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', 'XXL', 'XXL', 16);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', '3XL', '3XL', 17);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', '4XL', '4XL', 18);
INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES ('size', '5XL', '5XL', 19);

CREATE TABLE wear_attribute_available (
    wear_id INT(11) NOT NULL,
    attribute_id INT(11) NOT NULL,
    variant TINYINT(3),
    PRIMARY KEY (`wear_id`,`attribute_id`,`variant`),
    CONSTRAINT `wear_attribute_available_fk_1` FOREIGN KEY (`wear_id`) REFERENCES `wear` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wear_attribute_available_fk_2` FOREIGN KEY (`attribute_id`) REFERENCES `wear_attributes` (`id`) ON DELETE CASCADE
) engine=InnoDB DEFAULT CHARSET utf8mb4;

DROP TABLE deltagere_wear;
CREATE TABLE deltagere_wear_order (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    deltager_id INT(11) NOT NULL,
    wearpris_id INT(11) NOT NULL,
    antal INT(11) NOT NULL,
    received enum('t','f') NOT NULL DEFAULT 'f',
    CONSTRAINT `deltagere_wear_order_fk_1` FOREIGN KEY (`wearpris_id`) REFERENCES `wearpriser` (`id`),
    CONSTRAINT `deltagere_wear_order_fk_2` FOREIGN KEY (`deltager_id`) REFERENCES `deltagere` (`id`) 
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE deltagere_wear_order_attributes (
    order_id INT UNSIGNED NOT NULL,
    attribute_id INT(11) NOT NULL,
    PRIMARY KEY (`order_id`,`attribute_id`),
    CONSTRAINT `deltagere_wear_order_attributes_fk_1` FOREIGN KEY (`order_id`) REFERENCES `deltagere_wear_order` (`id`) ON DELETE CASCADE,
    CONSTRAINT `deltagere_wear_order_attributes_fk_2` FOREIGN KEY (`attribute_id`) REFERENCES `wear_attributes` (`id`)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE wear_image (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    image_file TEXT
) engine=InnoDB DEFAULT CHARSET utf8mb4;

CREATE TABLE wear_image_connection (
    image_id INT UNSIGNED NOT NULL,
    wear_id INT(11) NOT NULL,
    attribute_id INT(11),
    PRIMARY KEY (`image_id`,`wear_id`,`attribute_id`),
    CONSTRAINT `wear_image_connection_fk_1` FOREIGN KEY (`image_id`) REFERENCES `wear_image` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wear_image_connection_fk_2` FOREIGN KEY (`wear_id`) REFERENCES `wear` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wear_image_connection_fk_3` FOREIGN KEY (`attribute_id`) REFERENCES `wear_attributes` (`id`)
) engine=InnoDB DEFAULT CHARSET utf8mb4;
