<?php

namespace Blog\Models;

class Post extends Model {
	
	/**
	 * Content title (255 characters)
	 * 
	 * @var string
	 */
	public $title;
	
	/**
	 * Plaintext summary/description
	 * 
	 * @var string
	 */
	public $summary;
	
	/**
	 * Plaintext without formatting
	 * 
	 * @var string
	 */
	public $plain;
	
	/**
	 * Content exactly as entered
	 * 
	 * @var string
	 */
	public $raw;
	
	/**
	 * HTML formatted text
	 * 
	 * @var string
	 */
	public $body;
	
	/**
	 * Formatted user display name if specified
	 * 
	 * @var string
	 */
	public $author;
	
	/**
	 * Associated username
	 * 
	 * @var string
	 */
	public $username;
	
	/**
	 * User id if registered
	 * 
	 * @var int
	 */
	public $user_id		= 0;
	
	/**
	 * Content root (forum, blog etc...)
	 * 
	 * @var int
	 */
	public $root_id;
	
	/**
	 * Reply parent
	 * 
	 * @var int
	 */
	public $parent_id	= 0;
	
	/**
	 * Published date (empty if unpublished)
	 * 
	 * @var string
	 */
	public $published_at;
	
	/**
	 * Categorization and grouping taxonomy
	 * 
	 * @var array
	 */
	public $taxonomy;
	
	/**
	 * Metadata and associated optional fields
	 * 
	 * @var array
	 */
	public $meta;
	
	public function __get( $name ) {
		switch( $name ) {
			case 'tags':
			case 'categories':
				return 
				isset( $taxonomy[$name] ) ?
					$taxonomy[$name] : array();
				
			case 'date_u':
				return
				isset( $this->published_at ) ? 
					$this->myTime( strtotime(
						$this->published_at
					) ) : 
					$this->myTime( \PHP_INT_MAX );
		}
		
		return null;
	}
	
	public function __construct( $data = array() ) {
		parent::ifIsset( $this, $data );
	}
	
	public static function find( array $filter ) {
		if ( 
			!isset( $filter['search'] ) || 
			!isset( $filter['value'] ) 
		) {
			return null;
		}
		
		parent::baseFilter( $filter, $id, $limit, $page, $sort );
		
		if ( isset( $filter['fields'] ) ) {
			$fields	= parent::filterFields( $filter['fields'] );
		} else {
			$fields = 'body,summary';
		}
		
		$params	= array();
		$sql	= 
		"SELECT posts.id AS id, posts.title AS title, 
		posts.root_id AS root_id, posts.parent_id AS parent_id, 
		posts.status AS status, posts.created_at AS created_at, 
		posts.updated_at AS updated_at,	
		posts.reply_count AS reply_count, 
		posts.reply_at AS reply_at, posts.user_id AS user_id, 
		COALESCE( u.display, u.username, 'Anonymous' ) AS author,
		u.username AS username, $fields 
		
		FROM posts INNER JOIN posts AS p ON posts.parent_id = p.id
		LEFT JOIN users AS u ON posts.user_id = u.id";
		
		if ( $id > 0 ) {
			$sql .= 'WHERE posts.parent_id = :id ';
			$params[':id'] = $id;
		} else {
			$sql .= 'WHERE posts.parent_id = posts.id ';
		}
		
		$params[':limit']	= $limit;
		$params[':offset']	= ( $page - 1 ) * $limit;
		$sql			.= 
		' LIMIT :limit OFFSET :offset;';
		
		if ( $id > 0 ) {
			return parent::query( $sql, $params, 'class' );
		}
		return parent::query( $sql, $params, new Post() );
	}
	
	public function save() {
		$params	= parent::ifIsset( 
			$this, 
			array( 
				'title', 'summary', 'raw', 'plain', 
				'body', 'published_at', 'status' 
			) 
		);
		
		if ( isset( $this->id ) ) {
			return 
			parent::edit( 'posts', $this->id, $params );
		}
		
		$params['parent_id']	= $this->parent_id;
		$params['user_id']	= $this->user_id;
		$this->id = parent::put( 'posts', $params );
		
		return $this->id;
	}
}
