<?php

namespace Blog\Core;

class Request extends Message 
	implements \Psr\Http\Message\RequestInterface {
	
	protected $method;
	protected $target;
	protected $uri;
	private $ip;
	private $rawsig;
	private $sig;
	
	public function __construct(
		$method,
		Uri $uri,
		$body,
		array $headers,
		$protocol
	) {
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
		if ( $preserveHost ) {
			if ( $uri === $this->uri ) {
				return $this;
			}
		}
		
	}
	
	private function updateFromUri( $host ) {
		
	}
	
	private function signature() {
		$sig		= new BrowserSignature();
		$this->sig	= $sig->getSingature();
		$this->rawsig	= $sig->getSingature( true );
	}
}
