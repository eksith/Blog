<?php

namespace Blog\Messaging;

// http://www.php-fig.org/psr/psr-7/
class ServerRequest extends Request 
	implements \Psr\Http\Message\ServerRequestInterface {
	
	private $ip;
	
	private $browser;
	
	private $rawsig;
	
	private $sig;
	
	private $parsed_body;
	
	public function __construct(
		$method		= null,
		$uri		= null,
		$body		= null,
		array $headers	= array(),
		$protocol	= null
	) {
		if ( empty( $body ) ) {
			$body	= file_get_contents( 'php://stdin' );
		}
		if ( empty( $headers ) ) {
			$headers	= $this->headers();
		}
		if ( empty( $method ) ) {
			$method		= $_SERVER['REQUEST_METHOD'];
		}
		if ( empty( $protocol ) ) {
			$protocol	= $_SERVER['SERVER_PROTOCOL'];
		}
		
		parent::__construct(
			$method,
			$this->getAsUri( $uri ),
			$body,
			$headers,
			$protocol
		);
		
		$this->signature();
	}
	
	public function getServerParams() {
		// TODO
	}
	
	public function getCookieParams() {
		// TODO
	}
	
	public function withCookieParams( array $cookies ) {
		// TODO
	}
	
	public function getQueryParams() {
		// TODO
	}
	
	public function withQueryParams( array $query ) {
		// TODO
	}
	
	public function getUploadedFiles() {
		// TODO
	}
	
	public function withUploadedFiles( array $uploadedFiles ) {
		// TODO
	}
	
	public function getParsedBody() {
		if ( isset( $this->parsed_body) ) {
			return $this->parsed_body;
		}
		return null;
	}
	
	public function withParsedBody( $data ) {
		return 
		static::immu( $this, 'parsed_body', $data );
	}
	
	public function getAttributes() {
		// TODO
	}
	
	// https://secure.php.net/manual/en/wrappers.php.php
	// Detect content type and parse as application/x-www-form-urlencoded or multipart/form-data
	public function getBodyWithFilter( $filter ) {
		// TODO
	}
	
	public function getSignature( $raw = false ) {
		if ( $raw ) {
			return $this->rawsig;
		} 
		return $this->sig;
	}
	
	public function getBrowserProfile() {
		return $this->browser;
	}
	
	private function signature() {
		if ( !isset( $this->browser ) ) {
			$this->browser = new BrowserProfile();
		}
		
		$this->sig	= $this->browser->getSignature();
		$this->rawsig	= $this->browser->getSignature( true );
	}
	
	private function headers( $key = null ) {
		if ( !isset( $this->sent_headers ) ) {
			if ( function_exists( 'getallheaders' ) ) {
				$this->sent_headers = \getallheaders();
			} else {
				$this->sent_headers = 
					$this->httpHeaders();
			}
		}
		
		if ( null == $key ) {
			return $this->sent_headers;
		}
		return isset( $this->sent_headers[$key] )? 
			$this->sent_headers[$key] : null;
	}
	
	private function httpHeaders() {
		$val = array();
		foreach ( $_SERVER as $k => $v ) {
			if ( strncmp( $k, 'HTTP_', 5 ) ) {
				$a = explode( '_' ,$k );
				array_shift( $a );
				array_walk( $a, function( &$r ) {
					$r = ucfirst( strtolower( $r ) );
				});
				$val[ implode( '-', $a ) ] = $v;
			}
		}
		
		return $val;
	}
}
