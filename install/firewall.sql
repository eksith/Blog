
-- Firewall blocks
CREATE TABLE blocks (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	term TEXT NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	expires_at DATETIME DEFAULT NULL 
);

CREATE INDEX idx_blocks_on_created_at ON blocks ( created_at ASC );
CREATE INDEX idx_blocks_on_updated_at ON blocks ( updated_at );
CREATE INDEX idx_blocks_on_term ON blocks ( term ASC );

CREATE VIRTUAL TABLE block_search USING fts4(
	label, 
	term, 
	tokenize=simple
);


-- Block triggers
CREATE TRIGGER block_after_insert AFTER INSERT ON blocks FOR EACH ROW 
BEGIN
	INSERT INTO block_search ( docid, label, term ) 
		VALUES ( NEW.rowid, NEW.label, NEW.term );
END;

CREATE TRIGGER block_after_update AFTER UPDATE ON blocks FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at
BEGIN
	UPDATE blocks SET updated_at = CURRENT_TIMESTAMP 
		WHERE rowid = NEW.rowid;
	INSERT INTO block_search ( docid, label, term ) 
		VALUES ( NEW.rowid, NEW.label, NEW.term );
END;

CREATE TRIGGER block_before_delete BEFORE DELETE ON blocks FOR EACH ROW 
BEGIN
	DELETE FROM block_search WHERE docid = OLD.rowid;
END;


PRAGMA encoding = "UTF-8";
PRAGMA main.secure_delete = TRUE;
PRAGMA cache_size = 16384;
