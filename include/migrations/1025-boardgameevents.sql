ALTER TABLE boardgameevents MODIFY COLUMN type  enum('created','borrowed','returned','finished','present', 'not-present') NOT NULL DEFAULT 'created';
