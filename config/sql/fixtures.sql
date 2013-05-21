INSERT INTO sources (name, uri, created_at, updated_at) VALUES ('Hartware.net News', 'http://www.hartware.de/xml/newsread.rdf', datetime('now'), datetime('now'));

INSERT INTO items (source_id, read, title, content, uid, uri, created_at, updated_at) VALUES (1, NULL, 'Microsoft Surface mit 7,5 Zoll', 'Hersteller plant neue Tablet-Variante', 'tt', 'http://www.hartware.de/news_57693.html', datetime('now'), datetime('now'));
INSERT INTO items (source_id, read, title, content, uid, uri, created_at, updated_at) VALUES (1, datetime('now'), 'Microsoft Surface mit 7,5 Zoll', 'Hersteller plant neue Tablet-Variante', 'tt', 'http://www.hartware.de/news_57693.html', datetime('now'), datetime('now'));
