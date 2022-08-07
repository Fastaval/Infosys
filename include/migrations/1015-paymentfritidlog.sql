DROP TABLE IF EXISTS paymentfritidlog;
CREATE TABLE paymentfritidlog (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    participant_id INT(11) NOT NULL,
    amount INT UNSIGNED NOT NULL,
    cost INT UNSIGNED NOT NULL,
    fees INT UNSIGNED NOT NULL,
    timestamp DATETIME NOT NULL
) engine=innodb default character set utf8;
