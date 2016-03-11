
-- Session table
CREATE TABLE sessions (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	session_id VARCHAR NOT NULL, 
	content TEXT NOT NULL, 
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	skey TEXT NOT NULL
);


CREATE UNIQUE INDEX idx_sessions_on_id ON sessions ( session_id ASC );
CREATE INDEX idx_sessions_on_created_at ON sessions ( created_at ASC );
CREATE INDEX idx_sessions_on_updated_at ON sessions ( updated_at );

-- Session update trigger
CREATE TRIGGER sessions_after_update AFTER UPDATE ON sessions FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at
BEGIN
	UPDATE sessions SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.rowid;
END;



PRAGMA encoding = "UTF-8";
PRAGMA main.secure_delete = TRUE;
PRAGMA cache_size = 16384;
