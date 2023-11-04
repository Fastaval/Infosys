DROP TABLE IF EXISTS translations;

CREATE TABLE translations (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  label VARCHAR(64),
  lang char(2),
  string TEXT,
  CONSTRAINT UC_translations UNIQUE (label, lang)
) engine=InnoDB DEFAULT CHARSET utf8mb4;

INSERT INTO translations (label, lang, string) VALUES('tickets.category.0', 'da', 'Andet');
INSERT INTO translations (label, lang, string) VALUES('tickets.category.1', 'da', 'Infosys');
INSERT INTO translations (label, lang, string) VALUES('tickets.category.2', 'da', 'Proces');
INSERT INTO translations (label, lang, string) VALUES('tickets.category.3', 'da', 'Arrangør');
INSERT INTO translations (label, lang, string) VALUES('tickets.category.4', 'da', 'Betaling');
INSERT INTO translations (label, lang, string) VALUES('tickets.category.5', 'da', 'App');

INSERT INTO translations (label, lang, string) VALUES('tickets.open', 'da', 'Åben');
INSERT INTO translations (label, lang, string) VALUES('tickets.closed', 'da', 'Lukket');

INSERT INTO translations (label, lang, string) VALUES('tickets.status.open.0', 'da', 'Oprettet');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.open.1', 'da', 'Startet');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.open.2', 'da', 'Skal testes');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.open.3', 'da', 'Venter');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.closed.0', 'da', 'Annulleret');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.closed.1', 'da', 'Godkendt');
INSERT INTO translations (label, lang, string) VALUES('tickets.status.closed.2', 'da', 'Kan/vil ikke lave');

INSERT INTO translations (label, lang, string) VALUES('tickets.priority.0', 'da', 'Meget lav');
INSERT INTO translations (label, lang, string) VALUES('tickets.priority.1', 'da', 'Lav');
INSERT INTO translations (label, lang, string) VALUES('tickets.priority.2', 'da', 'Mellem');
INSERT INTO translations (label, lang, string) VALUES('tickets.priority.3', 'da', 'Høj');