DROP TABLE IF EXISTS activityageranges;

CREATE TABLE activityageranges (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    age INT UNSIGNED NOT NULL,
    requirementtype ENUM('min', 'max', 'exact') NOT NULL,
    CONSTRAINT FOREIGN KEY activity (activity_id) REFERENCES aktiviteter (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT UNIQUE activity_requirement (activity_id, requirementtype)
) engine=InnoDB character set utf8mb4;
