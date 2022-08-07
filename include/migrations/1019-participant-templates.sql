DROP TABLE IF EXISTS participantidtemplates;

CREATE TABLE participantidtemplates (
    participant_id INT NOT NULL PRIMARY KEY,
    template_id INT UNSIGNED NOT NULL,
    CONSTRAINT FOREIGN KEY participant (participant_id) REFERENCES deltagere (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY template (template_id) REFERENCES idtemplates (id) ON UPDATE CASCADE ON DELETE CASCADE
) engine=InnoDB character set utf8;
