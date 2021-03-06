INSERT INTO version VALUES ('1373895245');

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  password TEXT NOT NULL,
  email TEXT,
  active INTEGER NOT NULL DEFAULT 0,
  superadmin INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME,
  updated_at DATETIME
);

ALTER TABLE items ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE sources ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE tags ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE settings ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE CASCADE;

DROP TABLE access;
CREATE TABLE IF NOT EXISTS access (
  user_id INTEGER NOT NULL,
  client VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expire DATETIME  NOT NULL,
  UNIQUE (user_id, client) ON CONFLICT REPLACE
);