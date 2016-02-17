<?php

namespace Blog\Handlers;
use Blog\Events;

class Handler extends Events\Listener {
	
	private static $filter;
	
	protected function getFilter() {
		if ( !isset( self::$filter ) ) {
			self::$filter = new \Blog\Filter();
		}
		
		return self::$filter;
	}
	
	// https://paragonie.com/blog/2015/06/preventing-xss-vulnerabilities-in-php-everything-you-need-know

	/**
	 * Checks if current connection is secure
	 *
	 * @return bool True if secure; false if not
	 */
	protected function is_https() {
		return ( 
			!empty($_SERVER['HTTPS'] )		&& 
			$_SERVER['HTTPS']	!== 'off'	|| 
			$_SERVER['SERVER_PORT']	== 443 
		) ? true : false;
	}
	
	/**
	 * Checks connection port
	 *
	 * @return int
	 */
	protected function port() {
		return $_SERVER['SERVER_PORT'];
	}
	
	/**
	 * Base request URL path including protocol and path
	 *
	 * @return string Full URL base
	 */
	protected function baseURL() {
		$host	= $_SERVER['SERVER_NAME'];
		$proto	= $this->is_https() ? 'https' : 'http';
		$port	= $this->port();
		
		if ( $port != '80' || $port != '443' ) {
			return "$proto://$host:$port";
		}
		return "$proto://$host";
	}
	
	/**
	 * Shutdown event helper.
	 * Finish the request and optionally send the output buffer.
	 * Calls the 'end' events from the dispatcher if any.
	 * 
	 * @param bool $flush If true, prevents any content from being 
	 * 		sent to the user
	 */
	protected function finish( $flush = true ) {
		if ( $flush ) {
			if ( function_exists( 
				'fastcgi_finish_request' 
			) ) {
				fastcgi_finish_request();
			}
			flush();
		}
		
		if ( !$flush ) { // Nothing should be printed
			ob_start();
			ob_end_clean(); 
		}
		exit();
	}
	
	/**
	 * Safely redirect to another URL in the same domain with 
	 * optional status code
	 * 
	 * @param string $path URL without 'http://' etc...
	 * @param int $code Redirect code
	 */
	protected function redirect( 
		$path, 
		$code	= 302 
	) {
		if ( headers_sent() ) {
			$this->finish( false );
		}
		
		// Check for possible attack vectors
		$path	= filter_var( trim( $path ), 
				\FILTER_SANITIZE_URL );
		if (
			empty( $path ) || 
			false !== strpos( $path, '://' ) 
		) {
			$this->finish( false ); 
		}
		
		$status	= array( 200, 201, 202, 203, 204, 205, 300, 301, 
			302, 303, 304 );
		if ( in_array( $code, $status ) ) {
			$code = 302;
		}
	
		$url	= $this->baseURL();
		$path	= ltrim( $path, '/\\' );
		
		header( "Location: $url/$path", true, $code );
		$this->finish( false );
	}
	
	/* Utilities */
	
	protected function csrf( Events\Event $event, $form ) {
		$key	= $form . '_csrf';
		$csrf	= $event->get( $key );
		
		if ( !empty( $csrf ) ) {
			return $csrf;
		}
		
		$xss	= filter_input( 
				\INPUT_POST, 
				'csrf', 
				\FILTER_SANITIZE_FULL_SPECIAL_CHARS 
			);
		
		$csrf	= $this->verifyXSS( $form, $xss );
		$event->set( $key, $csrf );
		
		return $csrf;
	}
	
	protected function genXSS( $form ) {
		return $this->crypto()->genPbk( 
			CXX_HASH, 
			$form . session_id(), 
			bin2hex( $this->crypto()->bytes( CXX_SIZE ) ), 
			CXX_ROUNDS 
		);
	}
	
	protected function verifyXSS( $form, $xss ) {
		if ( empty( $xss ) || mb_strlen( $xss, '8bit' ) > 200 ) {
			return false;
		}
		return $this->crypto()->verifyPbk( 
				$form . session_id(), $xss 
			);
	}
	
	protected function saveCookie( $name, $data, $key ) {
		$data	= base64_encode( $data );
		$cookie	= $this->crypto()->encrypt( $data, $key );
		$hash	= $this->crypto()->genPbk( 
				COOKIE_CHECKSUM, $data 
			);
		
		return setcookie( 
			$name, 
			$hash . '|' . $cookie, 
			time() + COOKIE_TIME, 
			COOKIE_PATH, 
			COOKIE_SECURE, 
			true 
		);
	}
	
	/**
	 * Sanitized cookie by name and maximum content size
	 */
	protected function getCookie( $name, $key, $max = 2045 ) {
		if ( !isset( $_COOKIE[$name] ) ) {
			return false;
		}
		
		if (
			empty( $_COOKIE[$name] )			|| 
			mb_strlen( $_COOKIE[$name], '8bit' ) > $max	|| 
			strrpos( $_COOKIE[$name], '|' ) === false
		) {
			return false;
		}
		
		$data	= explode( '|', $_COOKIE[$name], 2 );
		if ( count( $data ) != 2 ) {
			return false;
		}
		
		$cookie	= $this->crypto()->decrypt( $data[1], $key );
		if ( $this->crypto()->verifyPbk( $cookie, $data[0] ) ) {
			return base64_decode( $cookie, true );
		}
		
		return false;
	}
	
	/**
	 * Create a URL based on the name and date
	 * @example /2015/02/26/name
	 */
	protected function datePath( 
		$name, 
		$time	= null 
	) {
		$p = ( null == $time ) ? 
			date( 'Y/m/d' ) : date( 'Y/m/d', $time );
		return $p . '/' . $name;
	}
}

