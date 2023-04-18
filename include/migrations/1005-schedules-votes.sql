CREATE TABLE schedules_votes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    code CHAR(8) NOT NULL,
    cast_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    CONSTRAINT FOREIGN KEY schedule (schedule_id) REFERENCES afviklinger (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT UNIQUE schedule_code (schedule_id, code)
) ENGINE=innodb DEFAULT CHARSET utf8mb4;
