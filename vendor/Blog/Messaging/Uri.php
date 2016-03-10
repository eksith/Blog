<?php

namespace Blog\Messaging;

// https://github.com/guzzle/psr7/blob/master/src/Uri.php
class Uri extends Immutable implements \Psr\Http\Message\UriInterface {
	
	private static $schemes = array(
		'http'	=> 80,
		'https'	=> 443
	);
	
	
	const RX_URL	= '~^(http|ftp)(s)?\:\/\/((([a-z|0-9|\-]{1,25})(\.)?){2,7})($|/.*$){4,255}$~i';
	
	// Had some problems with this one
	const RX_XSS1	= '/((java)?script|eval|document)/ism';
	
	const RX_XSS2	= '/(<(s(?:cript|tyle)).*?)/ism';
	const RX_XSS3	= '/(document\.|window\.|eval\(|\(\))/ism';
	const RX_XSS4	= '/(\\~\/|\.\.|\\\\|\-\-)/sm';
	
	protected $port		= '';
	protected $query	= '';
	protected $host		= '';
	protected $scheme	= '';
	protected $userInfo	= '';
	protected $fragment	= '';
	protected $path		= '';
	protected $raw_uri	= '';
	
	protected $errors	= array();
	
	public function __construct( $uri = '' ) {
		if ( empty( $uri ) ) {
			$uri = self::fullUri();
		}
		
		$this->raw_uri	= $uri;
		$uri		= self::cleanUrl( $uri );
		$parts		= parse_url( $uri );
		
		if ( false === $parts ) {
			return; // Error
		}
		
		$this->apply( $parts );
	}
	
	public function getScheme() {
		return $this->scheme;
	}
	
	public function getAuthority() {
		if ( empty( $this->host ) ) {
			return '';
		}
		
		$auth	= empty( $this->userInfo ) ? 
			$this->host : $this->userInfo . '@' . $this->host;
		
		return $auth;
	}
	
	public function getUserInfo() {
		return $this->userInfo;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getQuery() {
		return $this->query;
	}
	
	public function getFragment() {
		return $this->fragment;
	}
	
	public function getRoot() {
		$scheme = $this->getScheme();
		if ( empty( $scheme ) ) {
			$scheme = 'http';
		}
		return $this->uriToString(
			$scheme,
			$this->getAuthority() );
	}
	
	public function getRawPath() {
		return $this->raw_uri;
	}
	
	public function withScheme( $scheme ) {
		return static::immu(
			$this, 'scheme', $scheme, 'filterScheme'
		);
	}
	
	public function withUserInfo( $user, $password = null ) {
		$info	= $user;
		if ( null === $password ) {
			$info	.= ':' . $password;
		}
		return static::immu( $this, 'userInfo', $info );
	}
	
	public function withHost( $host ) {
		return static::immu( $this, 'host', $host );
	}
	
	public function withPort( $port ) {
		if ( $this->checkPort( $port ) ) {
			return 
			static::immu( $this, 'port', $port, 'filterPort' );
		}
		
		return '';
	}
	
	public function withPath( $path ) {
		return 
		static::immu(
			$this, 'path', $path, 'filterPath'
		);
	}
	
	public function withQuery( $query ) {
		if ( 
			!is_string( $query ) && 
			!method_exists( $query, '__toString' ) 
		) {
			$this->errors[] = 
			'Querystring must be of type \'string\'';
			return '';
		}
		
		$query = ( string ) $query;
		if ( '?' === substr( $query, 0, 1 ) ) {
			$query = substr( $query, 1 );
		}
		
		return 
		static::immu(
			$this, 'query', $query, 'filterQuery'
		);
	}
	
	public function withFragment( $fragment ) {
		if ( '#' === substr( $fragment, 0, 1 ) ) {
			$query = substr( $fragment, 1 );
		}
		return 
		static::immu( 
			$this, 'fragment', $fragment, 'filterQuery' 
		);
	}
	
	public function __toString() {
		 return 
		 $this->uriToString(
			$this->getScheme(),
			$this->getAuthority(),
			$this->getPath(),
			$this->getQuery(),
			$this->getFragment()
		);
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	protected function uriToString( 
		$scheme		= null,
		$authority	= null,
		$path		= null,
		$query		= null,
		$fragment	= null
	) {
		$uri	= 
		empty( $authority ) ?
			( empty( $scheme ) ? '' : $scheme . '://') : 
			( empty( $scheme ) ? '' : $scheme . '://' . $authority );
			
		$uri	.= 
		empty( $path ) ? '' : 
			( ( substr( $path, 0, 1 ) === '/' ) ? 
				$path : '/' . $path );
		
		$uri	.= empty( $query ) ?	'' : '?' . $query;
		$uri	.= empty( $fragment ) ?	'' : '#' . $fragment;
		
		return $uri;
	}
	
	protected function filterQuery( $part ) {
		$rx = 
		'/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;= :@\/%]+|%(?![A-Fa-f0-9]{2}))/';
		return preg_replace_callback( 
			$rx,
			function( array $match ) {
				return rawurlencode( $match[0] );
			},
			$part
		);
	}
	
	protected function filterPort( $port ) {
		$filter = array(
				'options'	=> 
				array(
					'min-range'	=> 1,
					'max-range'	=> 65535
				)
			);
		
		$val	= 
		filter_var( $port, \FILTER_VALIDATE_INT, $filter );
		
		if ( false === $val ) {
			$this->errors[] = 'Invalid port number';
			return '';
		}
		return ( int )$val;
	}
	
	protected function filterScheme( $scheme ) {
		return rtrim( strtolower( $scheme ), ':/' );
	}
	
	protected function filterPath( $path ) {
		if ( empty( $this->scheme ) ) {
			return $path;
		}
		$path	= filter_var( $path, \FILTER_SANITIZE_URL );
		if ( false === $path ) {
			$this->errors[] = 'Invalid URI path';
			return '';
		}
		return $path;
	}
	
	protected function checkPort( $scheme, $host, $port ) {
		if ( !$scheme && $port ) {
			return true;
		}
		
		if ( !$host || !$port ) {
			return false;
		}
		return !isset( static::$schemes[$scheme] ) ||	
			$port !== static::$schemes[$scheme];
	}
	
	/**
	 * Apply parsed URL parts to class properties
	 * 
	 * @link https://github.com/guzzle/psr7/blob/master/src/Uri.php
	 */
	protected function apply( array $parts ) {
		
		$this->query	= isset( $parts['query'] ) ? 
			$this->filterQuery( $parts['query'] )	: '';
		
		$this->host	= isset( $parts['host'] ) ? 
			$parts['host']				: '';
		
		$this->scheme	= isset( $parts['scheme'] ) ? 
			$this->filterScheme( $parts['scheme'] )	: '';
		
		$this->port	= isset( $parts['port'] ) ? 
			$this->filterPort( $parts['port'] )	: null;
		
		$this->fragment	= isset( $parts['fragment'] ) ? 
			$this->filterQuery( $parts['fragment'] ): '';
		
		$this->path	= isset( $parts['path'] ) ? 
			$this->filterPath( $parts['path'] )	: '';
		
		$this->userInfo = isset( $parts['user'] ) ? 
			$parts['user']				: '';
		
		if ( isset( $parts['pass'] ) ) {
			$this->userInfo .= ':' . $parts['pass'];
		}
	}
	
	/**
	 * Create a link from the current $_SERVER parameters
	 * 
	 * @param bool $usefw Use the HTTP_X_FORWARDED_HOST header
	 * 
	 * @return string
	 * @link https://stackoverflow.com/questions/6768793/get-the-full-url-in-php/8891890#8891890
	 */
	public static function fullUri( $usefw = false ) {
		$ssl		= 
		( !empty( $_SERVER['HTTPS'] ) && 
			$_SERVER['HTTPS'] == 'on' );
		$sp		= 
		strtolower( $_SERVER['SERVER_PROTOCOL'] );
		
		$protocol	= substr( $sp, 0, strpos( $sp, '/' ) ) . 
					( ( $ssl ) ? 's' : '' );
					
		$port		= $_SERVER['SERVER_PORT'];
		$port		= 
		( ( ! $ssl && $port == '80' ) || 
			( $ssl && $port == '443' ) ) ? '' : ':'. $port;
		
		$user		= isset( $_SERVER['PHP_AUTH_USER'] ) ? 
					$_SERVER['PHP_AUTH_USER'] : '';
		
		$user		= 
		( !empty( $user ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) ? 
				$user . $_SERVER['PHP_AUTH_PW'] . 
				'@' : '';
				
		$host		= 
		( $usefw && isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) ? 
			$_SERVER['HTTP_X_FORWARDED_HOST'] : 
			( isset( $_SERVER['HTTP_HOST'] ) ? 
				$_SERVER['HTTP_HOST'] : null );
		
		$host		= !empty( $host ) ? $host : 
					$_SERVER['SERVER_NAME'] . $port;
		
		return $protocol . '://' . $user . $host . 
			$_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Filter URL 
	 * 
	 * @param string $txt Raw URL attribute value
	 */
	public static function cleanUrl( $txt, $xss = true ) {
		if ( empty( $txt ) ) {
			return '';
		}
		
		if ( filter_var( $txt, \FILTER_VALIDATE_URL ) ) {
			if ( $xss ) {
				if ( !preg_match( self::RX_URL, $txt ) ){
					return '';
				}	
			}
			if ( 
				//preg_match( self::RX_XSS1, $txt ) || 
				preg_match( self::RX_XSS2, $txt ) || 
				preg_match( self::RX_XSS3, $txt ) || 
				preg_match( self::RX_XSS4, $txt ) 
			) {
				return '';
			}
			
			return $txt;
		}
		return '';
	}
}
