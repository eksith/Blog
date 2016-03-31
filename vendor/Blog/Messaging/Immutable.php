<?php
namespace Blog\Messaging;
use Psr\Http\Message;

/**
 * Helper and foundation for immutable Http Message* classes
 */
class Immutable {
	
	/**
	 * Creates a clone of the given class if one of its properties
	 * already exists and its value is not identical to the new one.
	 * Else, it returns the given class.
	 * 
	 * @param object $class Class object to clone or send as-is
	 * @param string $param Property name
	 * @param string $value Value to check against
	 * @param callable|null $filter Optional filter function
	 * 
	 * @return object
	 */
	protected static function immu( 
		$class, 
		$param, 
		$value,
		$filter = null
	) {
		if ( is_callable( array( $class, $filter ) ) ) {
			$value	= call_user_func_array( 
					array( $class, $filter ), 
					$value 
				);
		}
		
		if ( is_string( $value ) ) {
			if ( 0 === strcmp( $value, $class->{$param} ) ) {
				return $class;
			}
		} elseif ( is_array( $value ) ) {
			$diff = $this->arrayDiff( $value, $class->{$param} );
			if ( empty( $diff ) ) {
				return $class;
			}
		} else {
			if ( $value === $class->{$param} ) {
				return $class;
			}
		}
		
		$new		= clone $class;
		$new->{$param}	= $value;
		return $new;
	}
	
	protected function getAsUri( $uri ) {
		switch( true ) {
			case empty( $uri ) :
				return new Uri();
			
			case is_string( $uri ) :
				return new Uri( $uri );
			
			case $uri instanceof Message\UriInterface :
				return $uri;
		}
		
		return new Uri();
	}
	
	protected function getHttpString(
		Message\MessageInterface $message
	) {
		return 'HTTP/' . $message->getProtocolVersion();
	}
	
	protected function getTargetString(
		Message\MessageInterface $message
	) {
		return trim( $message->getMethod()  . ' ' . 
				$message->getRequestTarget() );
	}
	
	protected function getHostString(
		Message\MessageInterface $message
	) {
		if ( $message->hasHeader('host') ) {
			return '';
		}
		return "\r\nHost: " . $message->getUri()->getHost();
	}
	
	protected function getReasonString(
		Message\MessageInterface $message
	) {
		return $this->getHttpString( $message ) . 
			$message->getStatusCode() . ' ' .
			$message->getReasonPhrase();
	}
	
	protected function getMessageString(
		Message\MessageInterface $message
	) {
		$msg = '';
		switch ( true ) {
			case $message 
				instanceof Message\RequestInterface :
				$msg =	
				$this->getTargetString( $message ) . ' ' . 
				$this->getHttpString( $message ) . 
				$this->getHostString( $message );
				break;
			
			case $message 
				instanceof Message\ResponseInterface :
				$msg = 
				$this->getHttpString( $message ) . ' ' .
				$message->getStatusCode() . ' ' . 
				$message->getReasonPhrase();
		}
		
		if ( empty( $msg ) ) {
			return '';
		}
		
		foreach ( $message->getHeaders() as $name => $values ) {
			$msg .= "\r\n{$name}: " . 
				implode( ', ', $values );
		}

		return "{$msg}\r\n\r\n" . $message->getBody();
	}
	
	protected function arrayDiff( $array1, $array2 ) {
		$diff = array();
		
		if ( empty( $array1 ) && !empty( $array2 ) ) {
			return $array2;
		}
		
		if ( empty( $array2 ) && !empty( $array1 ) ) {
			return $array1;
		}
		
		foreach ( $array1 as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( !isset( $array2[$k] ) || !is_array( $array2[$k] ) ) {
					$diff[$k] = $v;
				} else {
					$ndiff = $this->arrayDiff( $v, $array2[$k] );
					if ( !empty( $ndiff ) ) {
						$diff[$k] = $ndiff;
					}
				}
			} elseif ( !array_key_exists( $k, $array2 ) || $array2[$k] !== $v ) {
				$diff[$k] = $v;
			}
		}
		
		return $diff;
	}
}
