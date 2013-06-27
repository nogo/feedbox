CREATE TABLE IF NOT EXISTS version (
  key varchar(255) PRIMARY KEY
);

INSERT INTO version VALUES ('1368823260');
INSERT INTO version VALUES ('1372086499');

CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  key varchar(255) NOT NULL UNIQUE,
  value TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.unread.sortby', 'oldest', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.unread.count', '50', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.read.sortby', 'newest', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.read.count', '50', DATETIME('now'), DATETIME('now'));

CREATE TABLE IF NOT EXISTS tags (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name varchar(255) NOT NULL UNIQUE,
  color VARCHAR(20),
  unread INTEGER DEFAULT 0,
  created_at DATETIME,
  updated_at DATETIME
);

ALTER TABLE sources ADD COLUMN tag_id INTEGER;
