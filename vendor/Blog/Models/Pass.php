<?php

namespace Blog\Models;

/**
 * User authentication pass
 */
class Pass extends Model {
	
	/**
	 * Cookie lookup hash
	 * 
	 * @var string
	 */
	public $lookup;
	
	/**
	 * Cookie token to hash with user hash to verify authentication
	 * 
	 * @var string
	 */
	public $token;
	
	/**
	 * User id
	 * 
	 * @var int
	 */
	public $user_id;
	
	/**
	 * Token expiration
	 * 
	 * @var datetime
	 */
	public $expires_at;
	
	/**
	 * Login lookup selector
	 * 
	 * @var string
	 */
	public $lookup;
	
	/**
	 * Login authorization
	 * 
	 * @var string
	 */
	public $auth;
	
	/**
	 * Cookie token hash
	 * 
	 * @var string
	 */
	public $hash;
	
	public function __construct( $data = array() ) {
		parent::getIsset( $this, $data );
	}
	
	public static function find( $lookup ) {
		$sql = 
		"SELECT lookup, token, user_id, expires_at, 
			u.hash AS hash, u.auth AS auth
		FROM passes WHERE lookup = :lookup AND 
		strftime( '%s', expires_at ) > :exp 
		INNER JOIN users AS u ON user_id = u.id LIMIT 1;";
		$params = 
		array( 
			':lookup'	=> $lookup 
			':exp'		=> 
				time() - 
				parent::getSetting( 'cookie_time' )
		);
		
		return parent::query( $sql, $params, 'class' );
	}
	
	public function authenticate() {
		return 
		parent::getCrypto()->verifyPbk(
			$this->token . $this->hash, $this->auth
		);
	}
	
	public function save() {
		if ( !isset( $this->expires_at ) ) {
			$this->expires_at	= 
			time() + parent::getSetting( 'cookie_time' );
		}
		if ( !isset( $this->token ) ) {
			$this->token		= 
			bin2hex( parent::getCrypto()->bytes(12) );
		}
		if ( !isset( $this->hash ) {
			$this->lookup		= 
			bin2hex( parent::getCrypto()->bytes(
				parent::getSetting( 'user_hash_size' )
			) );
		}
		
		$this->auth = 
		parent::getCrypto()->genPbk(
			parent::getSetting( 'user_hash' ),
			$this->token . $this->hash
		);
		
		$params	= parent::ifIsset( 
			$this, 
			array( 
				'lookup', 'token', 'hash', 'expires_at'
			) 
		);
		
		if ( isset( $this->id ) ) {
			return 
			parent::edit( 'passes', $this->id, $params );
		}
		
		$params['user_id']	= $this->user_id;
		$this->id = parent::put( 'passes', $params );
		
		return $this->id;
	}
}
