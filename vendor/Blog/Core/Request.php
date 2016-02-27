<?php

namespace Blog\Core;

class Request extends Message 
	implements \Psr\Http\Message\RequestInterface {
	
	protected $method;
	protected $target;
	protected $uri;
	protected $sent_headers;
	private $ip;
	private $browser;
	private $rawsig;
	private $sig;
	
	public function __construct(
		$method,
		Uri $uri,
		$body,
		array $headers,
		$protocol
	) {
		if ( empty( $method ) ) {
			$method		= $_SERVER['REQUEST_METHOD'];
		}
		if ( empty( $protocol ) ) {
			$protocol	= $_SERVER['SERVER_PROTOCOL'];
		}
		if ( empty( $headers ) ) {
			$headers	= $this->headers();
		}
		
		parent::__construct( $body, $protocol, $headers );
		$this->method	= strtoupper( $method );
		$this->uri	= $uri;
		
		$this->signature();
	}
	
	public function getRequestTarget() {
		if ( isset( $this->target ) ) {
			return $this->target;
		}
		$target	= $this->uri->getPath();
		if ( empty( $target ) ) {
			$target = '/';
		}
		$query	= $this->uri->getQuery();
		if ( !empty( $query ) ) {
			$target .= '?' . $query;
		}
		
		$this->target = $target;
		return $target;
	}
	
	public function withRequestTarget( $requestTarget ) {
		return static::immu( $this, 'target', $requestTarget );
	}
	
	public function getMethod() {
		return $this->method;
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
	
	public function withMethod( $method ) {
		return 
		static::immu( $this, 'method', strtoupper( $method ) );
	}
	
	public function getUri() {
		return $this->uri;
	}
	
	public function withUri(
		\Psr\Http\Message\UriInterface $uri, 
		$preserveHost = false 
	) {
		if ( $uri === $this->uri ) {
			return $this;
		}
		
		$new		= clone $this;
		$new->uri	= $uri;
		
		if ( $preserveHost ) {
			return $new;
		}
		if ( $host = $uri->getHost() ) {
			$new->updateFromUri( $host );
		}
		
		return $new;
	}
	
	private function headers( $key = null ) {
		if ( !isset( $this->sent_headers ) ) {
			if ( function_exists( 'getallheaders' ) ) {
				$this->sent_headers = \getallheaders();
			} else {
				$this->sent_headers = $this->httpHeaders();
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
	
	private function updateFromUri( $host ) {
		if ( $port = $this->uri->getPort() ) {
			$host .= ':' . $port;
		}
		$this->loadHeader( 'host', 'Host', $host );
	}
	
	private function signature() {
		if ( !isset( $this->browser ) ) {
			$this->browser = new BrowserProfile();
		}
		
		$this->sig	= $this->sigobject->getSingature();
		$this->rawsig	= $this->sigobject->getSingature( true );
	}
}
