-- Change foreign key to update if we move an area to a different ID
ALTER TABLE deltagere DROP FOREIGN KEY deltagere_ibfk_3;
ALTER TABLE deltagere ADD CONSTRAINT deltagere_ibfk_3 FOREIGN KEY (work_area) REFERENCES organizer_categories(id) ON UPDATE CASCADE;

-- Insert author and desiger at the beginning of the table, so they're always the first 2 and we don't have to update any refrences to threir IDs later
UPDATE organizer_categories SET id = id+2 ORDER BY id DESC;
INSERT INTO organizer_categories (id, name_da, name_en) VALUES (1,'Scenarieforfatter','Scenario author');
INSERT INTO organizer_categories (id, name_da, name_en) VALUES (2,'Brætspilsdesigner','Board game designer');
