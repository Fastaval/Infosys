CREATE TABLE participants_sleepingplaces (
    participant_id INT NOT NULL,
    room_id INT NOT NULL,
    starts DATETIME NOT NULL,
    ends DATETIME NOT NULL,
    PRIMARY KEY participant_room_time (participant_id, room_id, starts),
    CONSTRAINT FOREIGN KEY participant_fk (participant_id) REFERENCES deltagere (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT FOREIGN KEY room_fk (room_id) REFERENCES lokaler (id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=innodb DEFAULT CHARSET utf8mb4;
