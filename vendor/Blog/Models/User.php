<?php

namespace Blog\Models;

class User extends Model {
	
	/**
	 * Login username
	 * 
	 * @var string
	 */
	public $username;
	
	/**
	 * Hashed password and salt
	 * 
	 * @var string
	 */
	public $password;
	
	/**
	 * Email (max 180 characters)
	 * 
	 * @var string
	 */
	public $email;
	
	/**
	 * Displayed name
	 * 
	 * @var string
	 */
	public $display;
	
	/**
	 * Avatar image (gravatar)
	 * 
	 * @var string
	 */
	public $avatar;
	
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
	
	public function save() {
		$params	= parent::ifIsset( 
			$this, 
			array( 'password', 'email', 'display', 'bio', 
				'auth', 'hash', 'status' ) 
		);
		
		if ( isset( $params['password'] ) ) {
			$params['password'] = 
				$this->password( $params['password'] );
		}
		
		if ( isset( $this->id ) ) {
			return parent::edit( 
				'users', $this->id, $params 
			);
		} else {
			$params['username']	= $this->username;
			return parent::put( 'users', $params );
		}
	}
	
	public function matchPassword( $password ) {
		if ( !isset( $this->password ) ) {
			return false;
		}
		
		return $this->verify( $password, $this->password );
	}
	
	/**
	 * Hash password securely and into a storage safe format
	 * 
	 * @link https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 */
	private function password( $password ) {
		return 
		\password_hash(
			base64_encode(
				hash( 'sha384', $password, true )
			),
			\PASSWORD_DEFAULT
		);
	}
	
	
	/**
	 * Verify user provided password against stored one
	 */
	private function verify( $password, $stored ) {
		return 
		\password_verify(
			base64_encode( 
				hash( 'sha384', $password, true )
			),
			$stored
		);
	}
}
