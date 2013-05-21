CREATE TABLE IF NOT EXISTS sources (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  uri TEXT NOT NULL,
  icon TEXT,
  active INTEGER DEFAULT 1,
  unread INTEGER DEFAULT 0,
  errors TEXT,
  period TEXT,
  last_update DATETIME,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS items (
  id INTEGER PRIMARY KEY,
  source_id INTEGER,
  read DATETIME DEFAULT NULL,
  starred INTEGER DEFAULT 0,
  title TEXT NOT NULL,
  pubdate DATETIME NOT NULL,
  content TEXT NOT NULL,
  uid TEXT NOT NULL,
  uri TEXT NOT NULL,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (source_id) REFERENCES sources(id)
);
