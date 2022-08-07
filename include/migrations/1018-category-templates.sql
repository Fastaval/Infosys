DROP TABLE IF EXISTS brugerkategorier_idtemplates;

CREATE TABLE brugerkategorier_idtemplates (
    template_id INT UNSIGNED NOT NULL,
    category_id INT NOT NULL PRIMARY KEY,
    CONSTRAINT FOREIGN KEY category (category_id) REFERENCES brugerkategorier (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY template (template_id) REFERENCES idtemplates (id) ON UPDATE CASCADE ON DELETE CASCADE
) engine=InnoDB character set utf8;
