
-- Cache tables
CREATE TABLE caches (
	cache_id VARCHAR PRIMARY KEY NOT NULL, 
	ttl INTEGER NOT NULL, 
	data TEXT NOT NULL, 
	expiration DATETIME DEFAULT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX idx_caches_on_expiration ON caches ( created_at ASC, expiration DESC );
CREATE INDEX idx_caches_on_created_at ON caches ( created_at ASC );
CREATE INDEX idx_caches_on_updated_at ON caches ( updated_at );

-- Cache triggers
CREATE TRIGGER cache_after_insert AFTER INSERT ON caches FOR EACH ROW 
BEGIN
	UPDATE caches SET 
		expires_at = datetime( strftime('%s','now') + NEW.ttl )
		WHERE rowid = NEW.rowid;
END;


CREATE TRIGGER cache_after_update AFTER UPDATE ON caches FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at AND NEW.ttl = 0
BEGIN
	UPDATE caches SET updated_at = CURRENT_TIMESTAMP 
		WHERE rowid = NEW.rowid;
END;


CREATE TRIGGER cache_after_update_ttl AFTER UPDATE ON caches FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at AND NEW.ttl <> 0
BEGIN
	UPDATE caches SET updated_at = CURRENT_TIMESTAMP, 
		expires_at = datetime( strftime('%s','now') + NEW.ttl )
		WHERE rowid = NEW.rowid;
END;


PRAGMA encoding = "UTF-8";
PRAGMA main.secure_delete = TRUE;
PRAGMA cache_size = 16384;
