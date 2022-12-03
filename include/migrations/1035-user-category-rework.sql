-- Delete prices for unused categories
DELETE FROM wearpriser WHERE brugerkategori_id IN (SELECT id FROM brugerkategorier WHERE id <> 2 AND arrangoer = 'ja');

-- Delete unused categories
DELETE FROM brugerkategorier WHERE arrangoer = 'ja' AND id <> 2;
DELETE FROM brugerkategorier WHERE id = 5;