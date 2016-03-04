<?php

namespace Blog\Messaging;

class Request extends Message 
	implements \Psr\Http\Message\RequestInterface {
	
	protected $method;
	protected $target;
	protected $uri;
	
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
	
	private function updateFromUri( $host ) {
		if ( $port = $this->uri->getPort() ) {
			$host .= ':' . $port;
		}
		$this->loadHeader( 'host', 'Host', $host );
	}
}
