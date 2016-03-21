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
	 * URL friendly page slug
	 * 
	 * @var string
	 */
	public $slug;
	
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
	 * Content root (main topic) title
	 * 
	 * @var string
	 */
	public $root_title;
	
	/**
	 * Plaintext summary/description of the post root
	 * 
	 * @var string
	 */
	public $root_summary;
	
	/**
	 * Content root status
	 * 
	 * @var int
	 */
	public $root_status;
	
	/**
	 * Reply parent
	 * 
	 * @var int
	 */
	public $parent_id	= 0;
	
	/**
	 * Content parent title
	 * 
	 * @var string
	 */
	public $parent_title;
	
	/**
	 * Plaintext summary/description of the parent post
	 * 
	 * @var string
	 */
	public $parent_summary;
	
	/**
	 * Content parent status
	 * 
	 * @var int
	 */
	public $parent_status;
	
	/**
	 * Published date (empty if unpublished)
	 * 
	 * @var string
	 */
	public $published_at;
	
	
	/**
	 * A an array of ID => title relationships denoting all the 
	 * parents this post has. Should be empty if this is root
	 * 
	 * @var array
	 */
	public $breadcrumb;
	
	/**
	 * Categorization and grouping taxonomy
	 * 
	 * @var array
	 */
	public $taxonomy	= array();
	
	/**
	 * Metadata and associated optional fields
	 * 
	 * @var array
	 */
	public $meta		= array();
	
	public function __get( $name ) {
		switch( $name ) {
			case 'tags':
			case 'categories':
				return 
				isset( $this->taxonomy[$name] ) ?
					$this->taxonomy[$name] : array();
			case 'date':
				return 
				isset( $this->published_at ) ? 
					parent::niceDate( $this->published_at ) : 
					parent::niceDate( \PHP_INT_MAX );
				
			case 'date_u':
				return 
				isset( $this->published_at ) ? 
					parent::utc( $this->published_at ) : 
					parent::utc( \PHP_INT_MAX );
		}
		
		return null;
	}
	
	public function __set( $name, $value ) {
		switch( $name ) {
			case 'raw_breadcrumb':
				break;
		}
	}
	
	public function __construct( $data = array() ) {
		parent::ifIsset( $this, $data );
	}
	
	public static function find( array $filter ) {
		if ( 
			!isset( $filter['search'] ) || 
			!isset( $filter['values'] ) 
		) {
			return null;
		}
		
		parent::baseFilter( $filter, $id, $limit, $page, $sort );
		
		if ( isset( $filter['fields'] ) ) {
			$fields	=	parent::filterFields( 
						$filter['fields'] 
					);
			$cols	= 
			array_map( function( $k ) {
				return 'posts.' . $k . ' AS ' . $k;
			}, explode( ',', $fields ) );
			
			$fields = implode( ',', $cols );
		} else {
			$fields = 
			'posts.body AS body, posts.summary AS summary';
		}
		
		# TODO breadrumbs
		# https://sqlite.org/lang_with.html
		# https://stackoverflow.com/questions/192220/what-is-the-most-efficient-elegant-way-to-parse-a-flat-table-into-a-tree?rq=1
		
		#p.title AS parent_title, p.status AS parent_status, 
		#p.summary AS parent_summary, 
		#r.title AS root_title, r.status AS root_status, 
		#r.summary AS root_summary, 
		
		#LFET JOIN posts AS p ON posts.parent_id = p.id 
		#LFET JOIN posts AS r ON posts.root_id = r.id 
		# GROUP_CONCAT(parent.parent_id AS bread_id, ',')
		
		$params	= array();
		$sql	= 
		"SELECT posts.id AS id, posts.title AS title, 
		posts.slug AS slug,
		posts.root_id AS root_id, posts.parent_id AS parent_id, 
		posts.status AS status, posts.created_at AS created_at, 
		posts.updated_at AS updated_at,	
		posts.reply_count AS reply_count, 
		posts.reply_at AS reply_at, posts.user_id AS user_id, 
		COALESCE( u.display, u.username, 'Anonymous' ) AS author,
		u.username AS username, $fields
		
		FROM posts 
		JOIN post_family AS parent ON posts.id = parent.child_id 
		JOIN post_family AS family ON parent.child_id = family.child_id
		LEFT JOIN posts AS p on parent.parent_id = p.id 
		
		LEFT JOIN users AS u ON posts.user_id = u.id ";
		
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
		return parent::query( $sql, $params, 'class' );
	}
	
	public function save() {
		$params	= parent::ifIsset( 
			$this, 
			array( 
				'title', 'summary', 'raw', 'plain', 
				'body', 'slug', 'published_at', 'status' 
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
