
-- Post tables
CREATE TABLE posts (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	title TEXT DEFAULT NULL,
	url VARCHAR DEFAULT NULL,
	root_id INTEGER DEFAULT 0, 
	parent_id INTEGER DEFAULT 0, 
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	published_at DATETIME DEFAULT NULL, 
	reply_at DATETIME DEFAULT NULL,
	user_id INTEGER DEFAULT 0, 
	summary TEXT NOT NULL, 
	plain TEXT NOT NULL, 
	raw TEXT NOT NULL, 
	body TEXT NOT NULL, 
	reply_count INTEGER DEFAULT 0, 
	quality FLOAT DEFAULT 0,
	status INTEGER DEFAULT -1
);

CREATE INDEX idx_posts_on_created_at ON posts ( created_at ASC );
CREATE INDEX idx_posts_on_updated_at ON posts ( updated_at );
CREATE INDEX idx_posts_on_reply_at ON posts ( reply_at ASC );
CREATE INDEX idx_posts_on_pub ON posts ( created_at ASC, published_at DESC );
CREATE INDEX idx_posts_on_family ON posts ( root_id ASC, parent_id ASC );
CREATE INDEX idx_posts_on_user_id ON posts ( user_id ASC );
CREATE INDEX idx_posts_on_url ON posts ( url ASC );
CREATE INDEX idx_posts_on_status ON posts ( status );

CREATE VIRTUAL TABLE posts_search USING fts4 ( search_data );


CREATE TABLE post_family (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	parent_id INTEGER NOT NULL, 
	child_id INTEGER NOT NULL
);
CREATE INDEX idx_post_family ON post_family ( parent_id ASC, child_id DESC );



-- Post triggers
CREATE TRIGGER post_after_insert AFTER INSERT ON posts FOR EACH ROW 
BEGIN
	--UPDATE posts SET updated_at = CURRENT_TIMESTAMP WHERE rowid = NEW.rowid;
	INSERT INTO posts_search ( docid, search_data ) 
		VALUES ( NEW.rowid, NEW.title || ' ' || NEW.plain );
END;

CREATE TRIGGER post_after_update AFTER UPDATE ON posts FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at
BEGIN
	UPDATE posts SET updated_at = CURRENT_TIMESTAMP WHERE rowid = NEW.rowid;
	INSERT INTO posts_search ( docid, search_data ) 
		VALUES ( NEW.rowid, NEW.title || ' ' || NEW.plain );
END;


CREATE TRIGGER new_root AFTER INSERT ON posts
WHEN NEW.root_id = 0 AND NEW.parent_id = 0
BEGIN
	UPDATE posts SET root_id = NEW.rowid, parent_id = NEW.rowid, 
		reply_at = CURRENT_TIMESTAMP 
		WHERE posts.id = NEW.rowid;
	
	INSERT INTO post_family ( parent_id, child_id ) 
		VALUES ( NEW.rowid, NEW.rowid );
END;

CREATE TRIGGER new_reply AFTER INSERT ON posts
WHEN NEW.root_id <> 0 AND NEW.parent_id = 0
BEGIN
	UPDATE posts SET reply_at = CURRENT_TIMESTAMP WHERE posts.id = NEW.root_id;
	
	INSERT INTO post_family ( parent_id, child_id ) 
		VALUES ( NEW.root_id, NEW.rowid );
END;

CREATE TRIGGER new_child AFTER INSERT ON posts
WHEN NEW.parent_id <> 0
BEGIN
	-- Fix missing root_id by getting it from the parent's root
	UPDATE posts SET root_id = ( 
		SELECT root_id FROM posts WHERE posts.id = NEW.parent_id LIMIT 1
	) WHERE posts.id = NEW.rowid;
	
	-- Update parent reply stats
	UPDATE posts SET reply_count = ( reply_count + 1 ), reply_at = CURRENT_TIMESTAMP  
		WHERE id = NEW.parent_id;
	
	-- Update root reply stats
	UPDATE posts SET reply_count = ( reply_count + 1 ), reply_at = CURRENT_TIMESTAMP  
		WHERE id IN (
			SELECT root_id FROM posts 
			WHERE posts.id = NEW.rowid AND parent_id != NEW.parent_id LIMIT 1
		);
	
	INSERT INTO post_family ( parent_id, child_id ) 
		VALUES ( NEW.parent_id, NEW.rowid );
END;


CREATE TRIGGER post_before_delete BEFORE DELETE ON posts FOR EACH ROW 
BEGIN
	UPDATE posts SET reply_count = ( reply_count - 1 ) 
		WHERE id != OLD.rowid AND ( id = OLD.root_id OR id = OLD.parent_id );
	
	DELETE FROM posts_search WHERE docid = OLD.rowid;
	
	-- This may create orphans
	DELETE FROM post_family 
		WHERE child_id = OLD.rowid OR parent_id = OLD.rowid;
END;







-- User tables
CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	username VARCHAR NOT NULL, 
	password TEXT NOT NULL, 
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	display VARCHAR DEFAULT NULL,
	email VARCHAR DEFAULT NULL, 
	avatar TEXT DEFAULT NULL, 
	bio TEXT DEFAULT NULL, 
	pgp TEXT DEFAULT NULL, 
	auth VARCHAR NOT NULL, 
	hash VARCHAR NOT NULL, 
	status INTEGER DEFAULT 0
);

CREATE UNIQUE INDEX idx_users_on_username ON users ( username ASC );
CREATE INDEX idx_users_on_created_at ON users ( created_at ASC );
CREATE INDEX idx_users_on_updated_at ON users ( updated_at );
CREATE INDEX idx_users_on_auth ON users ( auth );
CREATE INDEX idx_users_on_status ON users ( status );


-- Auth lookup 
-- https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
CREATE TABLE passes (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	lookup VARCHAR NOT NULL, 
	token VARCHAR NOT NULL, 
	user_id INTEGER NOT NULL, 
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	expires_at DATETIME NOT NULL
);


CREATE UNIQUE INDEX idx_passes_auth ON passes ( lookup ASC, token ASC );
CREATE INDEX idx_passes_on_expires_at ON passes ( expires_at DESC );
CREATE INDEX idx_passes_on_user_id ON passes ( user_id ASC );



-- User group tables
CREATE TABLE groups(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	flags VARCHAR NOT NULL
);

CREATE TABLE user_groups(
	user_id INTEGER NOT NULL, 
	group_id INTEGER NOT NULL, 
	PRIMARY KEY ( user_id, group_id ) 
);


-- User triggers
CREATE TRIGGER user_after_update AFTER UPDATE ON users FOR EACH ROW 
WHEN NEW.updated_at < OLD.updated_at
BEGIN
	UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.rowid;
END;


CREATE TRIGGER user_before_delete BEFORE DELETE ON users FOR EACH ROW 
BEGIN
	DELETE FROM user_groups WHERE user_id = OLD.rowid;
END;

CREATE TRIGGER user_after_insert AFTER INSERT ON users FOR EACH ROW 
BEGIN
	DELETE FROM passes WHERE strftime( '%s', expires_at) < 
		strftime( '%s', 'now');
END;




-- Navigation menus
CREATE TABLE menus (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	lang_id INTEGER DEFAULT 0,
	post_id INTEGER DEFAULT 0,
	content TEXT NOT NULL,
	status INTEGER DEFAULT 0
);



-- Taxonomy tables
CREATE TABLE taxonomy (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	term VARCHAR NOT NULL, 
	slug VARCHAR NOT NULL, 
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated_at DATETIME DEFAULT NULL, 
	status INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX idx_taxonomy_on_terms ON taxonomy ( label ASC, term ASC );
CREATE INDEX idx_taxonomy_on_status ON taxonomy ( status );


CREATE TABLE posts_taxonomy (
	post_id INTEGER NOT NULL, 
	taxonomy_id INTEGER NOT NULL, 
	PRIMARY KEY ( post_id, taxonomy_id ) 
);


-- Taxonomy triggers
CREATE TRIGGER taxonomy_after_insert AFTER INSERT ON taxonomy FOR EACH ROW 
BEGIN
	UPDATE taxonomy SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.rowid;
END;

CREATE TRIGGER taxonomy_after_update AFTER UPDATE ON taxonomy FOR EACH ROW 
BEGIN
	UPDATE taxonomy SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.rowid;
END;

CREATE TRIGGER taxonomy_before_delete BEFORE DELETE ON taxonomy FOR EACH ROW 
BEGIN
	DELETE FROM posts_taxonomy WHERE taxonomy_id = OLD.rowid;
END;





-- Meta data tables
CREATE TABLE meta (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	render_as VARCHAR NOT NULL, 
	content TEXT NOT NULL
);

CREATE TABLE user_meta (
	meta_id INTEGER NOT NULL, 
	user_id INTEGER NOT NULL, 
	PRIMARY KEY ( meta_id, user_id ) 
);

CREATE TABLE post_meta (
	meta_id INTEGER NOT NULL, 
	post_id INTEGER NOT NULL, 
	PRIMARY KEY ( meta_id, post_id ) 
);



-- Localization
CREATE TABLE languages (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label VARCHAR NOT NULL, 
	local_term VARCHAR NOT NULL, 
	direction VARCHAR NOT NULL, 
	content TEXT NOT NULL,
	status INTEGER DEFAULT 0
);








PRAGMA encoding = "UTF-8";
PRAGMA main.secure_delete = TRUE;
PRAGMA cache_size = 16384;
