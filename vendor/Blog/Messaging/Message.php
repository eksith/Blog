<?php

namespace Blog\Messaging;

class Message extends Immutable 
	implements \Psr\Http\Message\MessageInterface {
	
	protected $headers	= array();
	protected $raw_headers	= array();
	protected $body		= '';
	protected $protocol	= '1.1';
	protected $stream;
	
	public function __construct(
		$body,
		$protocol,
		array $headers
	) {
		$this->protocol	= $protocol;
		$this->body	= $body;
		$this->setHeaders( $headers );
	}
	
	public function getProtocolVersion() {
		return $this->protocol;
	}
	
	// https://stackoverflow.com/questions/31360786/psr7-http-message-why-immutable
	public function withProtocolVersion( $protocol ) {
		return static::immu( $this, 'protocol', $protocol );
	}
	
	public function getHeaders() {
		return $this->raw_headers;
	}
	
	public function hasHeader( $name ) {
		return isset( $this->headers[strtolower( $name )] );
	}
	
	public function getHeader( $name ) {
		$name = strtolower( $name );
		return isset( $this->headers[$name] ) ? 
			$this->headers[$name] : [];
	}
	
	public function getHeaderLine( $name ) {
		return implode( ', ', $this->getHeader( $name ) );
	}
	
	public function withHeader( $name, $value ) {
		$org = $this->getHeader( $name );
		if ( !empty( $org ) ) {
			
		}
		
	}
	
	public function withAddedHeader( $name, $value ) {
		
	}
	
	public function withoutHeader( $name ) {
		if ( !$this->hasHeader( $name ) ) {
			return $this;
		}
		
		$new	= clone $this;
		$name	= strtolower( $name );
		
		unset( $new->headers[$name] );
		
		foreach ( array_keys( $new->raw_headers ) as $key ) {
			if ( 0 === strcasecmp( $name, $key ) ) {
				unset( $new->raw_headers[$key] );
			}
		}
		
		return $new;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function withBody( 
		\Psr\Http\Message\StreamInterface $body
	) {
		if ( $body === $this->stream ) {
			return $this;
		}
		
		$new = clone $this;
		$new->stream = $body;
		return $new;
	}
	
	protected function setHeaders( array $headers ) {
		foreach ( $headers as $header => $value ) {
			$header	= trim( $header );
			$name	= strtolower( $header );
			if ( is_array( $value ) ) {
				foreach ( $value as $v ) {
					$this->loadHeader( 
						$name, $header, $v
					);
				}
			} else {
				$this->loadHeader( 
					$name, $header, $value
				);
			}
		}
	}
	
	protected function loadHeader( $name, $header, $value ) {
		$value				= trim( $value );
		$this->headers[$name][]		= $value;
		$this->raw_headers[$header][]	= $value;
	}
}