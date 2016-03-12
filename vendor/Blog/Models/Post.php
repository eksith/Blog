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
