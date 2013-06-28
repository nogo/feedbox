CREATE TABLE IF NOT EXISTS version (
  key VARCHAR(255) PRIMARY KEY
);
INSERT INTO version VALUES ('1368823260');
INSERT INTO version VALUES ('1372086499');

CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY,
  key VARCHAR(255) NOT NULL UNIQUE,
  value TEXT,
  created_at DATETIME,
  updated_at DATETIME
);
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.unread.sortby', 'oldest', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.unread.count', '50', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.read.sortby', 'newest', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.read.count', '50', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.starred.sortby', 'newest', DATETIME('now'), DATETIME('now'));
INSERT INTO settings (key, value, created_at, updated_at) VALUES ('view.starred.count', '50', DATETIME('now'), DATETIME('now'));

CREATE TABLE IF NOT EXISTS tags (
  id INTEGER PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  color VARCHAR(20),
  unread INTEGER DEFAULT 0,
  created_at DATETIME,
  updated_at DATETIME
);

ALTER TABLE sources ADD COLUMN tag_id INTEGER REFERENCES tags(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS access (
  user VARCHAR(255) NOT NULL,
  client VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expire DATETIME  NOT NULL,
  UNIQUE (user, client) ON CONFLICT REPLACE
);