<?php

namespace Blog;

class Data {
	
	/* Database */
	
	public function getDb( $name = CONTENT_STORE ) {
		$config = array(
			\PDO::ATTR_TIMEOUT		=> DATA_TIMEOUT,
			\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
			\PDO::ATTR_PERSISTENT		=> false,
			\PDO::ATTR_EMULATE_PREPARES	=> false,
			\PDO::ATTR_ERRMODE		=> 
				\PDO::ERRMODE_EXCEPTION
		);	
		
		$db = new \PDO( $name, null, null, $config );
		$db->exec( 'PRAGMA journal_mode = WAL;' );
		
		return $db;
	}
	
	public function select( $table, $params ) {
		$k = array_keys( $params );
	}
	
	public function parseParams( $params ) {
		$k = array_keys( $params );
		$v = ':' . implode( ',:', $k );
		
		return array_combine( 
			explode( ',',  $v ), 
			array_values( $params ) 
		);
	}
	
	public function insertSql( $table, $params ) {
		$k = array_keys( $params );
		$f = implode( ',', $k );
		$v = ':' . implode( ',:', $k );
		
		return "INSERT INTO $table ( $f ) VALUES ( $v );";
	}
	
	public function updateSql( $table, $params, $cond ) {
		$k = array_keys( $params );
		$v = array_map( function( $f ) {
			return "$f = :$f";
		}, $k );
		
		return "UPDATE $table SET $v WHERE $cond;";
	}
	
	public function inParam(
		&$s	= '',
		&$p	= array(), 
		$data	= array(), 
		$f	= 'param' 
	) {
		$c = count( $data );
		for ( $i = 0; $i < $c; $i++ ) {
			$a	= ':' . $f . '_' . $i;
			$p[$a]	= $ids[$i];
			$s	.= $a . ',';
		}
		
		$s = rtrim( ',', $s );
	}
	
	public function saveContent( $db, $post, $uid, $id = 0 ) {
		$params = array(
			'title'		=> $post['title'],
			'body'		=> $post['body'],
			'raw'		=> $post['raw'],
			'plain'		=> $post['plain'],
			'summary'	=> $post['summary']
		);
		
		$params['status'] = defined( 'MOD' ) ? 
					POST_STATUS_OPEN : 
					$post['reply'];
		
		if( isset( $post['url'] ) ) {
			//$params['url']	= $post['url'] );
		}
		
		if ( $id > 0 ) {
			$params['id']		= $id;
			$sql			= 
			$this->updateSql( 'posts', $params, 'id = :id' );
		} else {
			$params['user_id']	= $uid;
			$params['parent_id']	= $post['parent'];
			$sql			= 
			$this->insertSql( 'posts', $params );
		}
		
		$stm	= $db->prepare( $sql );
		$stm->execute( $this->parseParams( $params ) );
		
		return ( $id > 0 ) ? true : $db->lastInsertId(); 
	}
	
	public function saveAccount( $db, $user, $id = 0 ) {
		$params	= array();
		if ( $id > 0 ) {		
			if ( isset( $user['display'] ) ) {
				$params['display']	=  
					$user['display'];
			}
			if ( isset( $user['password'] ) ) {
				$params['password']	= 
				password_hash( $user['password'] );
			}
			if ( isset( $user['bio'] ) ) {
				$params['bio']		=  
					$user['bio'];
			}
			if ( empty( $params ) ) {
				return true;
			}
			$params['id']		= $id;
			$sql			= 
			$this->updateSql( 'users', $params, 'id = :id' );
		} else {
			$params = array(
				'username'	=> $user['username'],
				'password'	=> 
				password_hash( $user['password'] )
			);
			$sql			= 
				$this->insertSql( 'users', $params );
		}
		
		$stm	= $db->prepare( $sql );
		$stm->execute( $this->parseParams( $params ) );
		
		return ( $id > 0 ) ? true : $db->lastInsertId(); 
	}
	
	public function existingUser( $db, $user ) {
		$sql = "SELECT id, username, password, auth FROM users 
			WHERE username = :username LIMIT 1;";
		$stm	= $db->prepare( $sql );
		$stm->execute( array( ':username' => $user['username'] ) );
		return $stm->fetch();
	}
	
	public function setPostStatus( $db, $status, $ids ) {
		$c = count( $ids );
		$s = '';
		$p = array( ':st' => $status );
		
		$this->inParam( $s, $p, $ids );
		
		$sql = "UPDATE posts SET status = :st WHERE id IN ($s);";
		$stm = $db->prepare( $sql );
		$stm->execute( $p );
	}
	
	public function getIndex(
		$name, 
		$id,
		$page	= 1,
		$limit	= 1,
		$sort	= 'date'
	) {
		$db	= $this->getDb( CONTENT_STORE );
		$page	= $this->getPage( $page );
		switch( strtolower( $name ) ) {
			case 'edit':
				$result = $this->getRaw( $db, $id );
				break;
				
			case 'post':
				$result = 
				$this->getPosts( $db, $id, $page, $limit, $sort );
				break;
			
			case 'trending':
				$result = 
				$this->getPosts( $db, $id, $page, $limit, $sort );
				break;
		
			case 'topic':
				$result = 
				$this->getPosts( $db, $id, $page, $limit, $sort );
				break;
				
			default:
				$result = 
				$this->getPosts( $db, 0, $page, $limit, $sort );
				break;
		}
		
		$db = null;
		return $result;
	}
	
	public function getRaw( $db, $id ) {
		$sql	= 
		"SELECT id, title, parent_id, status, user_id, raw 
			FROM posts WHERE id = :id LIMIT 1;";
		$stm	= $db->prepare( $sql );
		$stm->execute( array( ':id' => $id ) );
		
		$result = $stm->fetch();
		$stm	= null;
		return $result;
	}

	public function getPosts(
		$db,
		$id	= 0,
		$page	= 1,
		$limit	= 1,
		$sort	= 'date'
	) {
		$params	= array();
		$sql	= 
		"SELECT posts.id AS id, posts.title AS title, 
		posts.root_id AS root_id, posts.parent_id AS parent_id, 
		posts.status AS status, posts.created_at AS created_at, 
		posts.updated_at AS updated_at,	
		posts.reply_count AS reply_count, 
		posts.reply_at AS reply_at, posts.user_id AS user_id, 
		COALESCE( u.display, u.username, 'Anonymous' ) AS author ";
		
		$sql .= ( $id > 0 ) ? 
			'posts.body AS body, ' : 
			'posts.summary AS summary, ';
		
		$sql .= 
		"FROM posts INNER JOIN posts as p on posts.parent_id = p.id
		LEFT JOIN users AS u ON posts.user_id = u.id ";
		
		if ( $id > 0 ) {
			$sql .= 'WHERE posts.parent_id = :id ';
			$params[':id'] = $id;
		} else {
			$sql .= 'WHERE posts.parent_id = posts.id ';
		}
		
		$sql .= ' AND posts.status >= :status';
		
		$sql .= ' ORDER BY ';
		switch ( $sort ) {
			case 'new':
				$params[':status'] = POST_STATUS_BURIED;
				
			case 'trending':
				$params[':status'] = POST_STATUS_OPEN;
				$sql .= 
				"posts.reply_at ASC, p.reply_count ASC,
				posts.created_at ASC";
				break;
				
			case 'oldest':
				$params[':status'] = POST_STATUS_OPEN;
				$sql .= 
				'posts.created_at DESC';
				break;
				
				
			default:
				$params[':status'] = POST_STATUS_OPEN;
				if ( $id == 0 ) {
					$sql .= 'posts.created_at DESC';
				} else {
					$sql .= 'posts.created_at ASC';
				}
		}
		
		$sql			.= 
		' LIMIT :limit OFFSET :offset;';
		
		$params[':limit']	= $limit;
		$params[':offset']	= ( $page - 1 ) * $limit;
		
		$stm = $db->prepare( $sql );
		$stm->execute( $params );
		
		$result	=  $stm->fetchAll();
		$stm	= null;
		return $result;
	}
}
