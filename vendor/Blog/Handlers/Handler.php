<?php

namespace Blog\Handlers;
use Blog\Events;

class Handler extends Events\Listener {

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
			if ( function_exists( 'fastcgi_finish_request' ) ) {
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
				FILTER_SANITIZE_URL );
		if (
			empty( $path ) || 
			false !== strpos( $path, '://' ) 
		) {
			die(); 
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
	
	/**
	 * Creates a URL safe short code based on numeric id
	 * 
	 * @param int|string $k Key code or numeric id
	 * @param bool $create Creates a short code if true, decodes 
	 * 		if false
	 * @return int|string The original decoded id or a short code
	 */
	protected function urlCode(
		$k, 
		$create	= false 
	) {
		$r = 
		str_split( 
		'3456789abcdefghkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ' 
		);
		$l = strlen( $k );
		$c = 54;
		
		if ( $create ) {
			do {
				$k = $this->rnum( 10, 99 ) . $k . '.0';
				$o = '';
				$c = bcmod( $k, $l );
				$o = $r[$c];
				$k = bcdiv( bcsub( $k, $c ), $l );
			} while ( bccomp( $k, 0 ) > 0 );
			
			return strrev( $o );
		}
		
		$o = 0;
		$a = array_flip( $r );
		for( $i = 0; $i < $l; $i++ ) {
			$c = $k[$i];
			$n = bcpow( $c, $l - $i - 1 );
			$o = bcadd( $o, bcmul( $a[$c], $n ) );
		}
		return substr( $o, 2 );
	}
	
	
	/**
	 * mt_rand Wrapper that fixes some anomalies
	 *
	 * @return int Pseudo-random number (unsafe for crypto!)
	 */
	public function rnum( $min, $max ) {
		$num = 0;
		while ( $num < $min || $num > $max || null == $num ) {
			$num = mt_rand( $min, $max );
		}
		return $num;
	}
	
	
	/* Utilities */
	
	/**
	 * Sanitized cookie by name and maximum size
	 */
	protected function getCookie( $name, $max = 1000 ) {
		if ( !isset( $_COOKIE[$name] ) ) {
			return false;
		}
		$data	= $_COOKIE[$name];
		
		if ( mb_strlen( $data ) > $max || empty( $data ) ) {
			return false;
		}
		
		$data	= base64_decode( $data, true );
		if ( false === $data ) {
			return false;
		}
		
		return filter_var(
				$data, 
				FILTER_SANITIZE_SPECIAL_CHARS | 
				FILTER_FLAG_STRIP_HIGH
			);
	}
	
	/**
	 * Encode and save cookie
	 */
	protected function saveCookie( $data, $name ) {
		$cookie	= base64_encode( $data );
		return setcookie( $name, $cookie, COOKIE_TIME, '/' );
	}
	
	/**
	 * PBK Hash derivation (do not use this implementation for passwords!)
	 */
	protected function pbk(
		$algo, 
		$txt,
		$salt,
		$rounds, 
		$kl
	) {
		if ( function_exists( 'hash_pbkdf2' ) ) {
			return hash_pbkdf2( 
				$algo, $txt, $salt, $rounds, $kl 
			);
		}
		
		$hl	= strlen( hash( $algo, '', true ) );
		$bl	= ceil( $kl / $hl );
		$out	= '';
		
		for ( $i = 1; $i <= $bl; $i++ ) {
			$l = $salt . pack( 'N', $i );
			$l = $x = hash_hmac( $algo, $l, $txt, true );
			for ( $j = 1; $l < $rounds; $j++ ) {
				$x ^= ( $l = 
				hash_hmac( $algo, $l, $txt, true ) );
			}
			$out .= $x;
		}
		
		return bin2hex( substr( $out, 0, $kl ) );
	}

	protected function genPbk(
		$algo	= 'tiger160,4', 
		$txt,
		$salt	= null,
		$rounds	= 1000, 
		$kl	= 128 
	) {
		$rounds	= ( $rounds <= 0 ) ? 1000 : $rounds;
		$kl	= ( $kl <= 0 ) ? 128 : $kl;
		$salt	= empty( $salt ) ? 
				$this->rnd( CXX_SALT ) : $salt;
				
		$key	= $this->pbk( $algo,$txt, $salt, $rounds, $kl );
		$out	= array(
				$algo, $txt, $salt, $rounds, $kl
			);
		return base64_encode( implode( '$', $out ) );
	}
	
	function verifyPbk( $txt, $hash ) {
		$key	= base64_decode( $hash );
		$k	= explode( '$', $key );
		
		if ( empty( $k ) || empty( $txt ) ) {
			return false;
		}
		if ( count( $k ) != 5 ) {
			return false;
		}
		
		if ( !in_array( $algorithm, hash_algos() , true ) ) {
			return false;
		}
		$pbk = $this->pbk( $k[0], $txt, 
				( int ) $k[2], $k[3], $k[4] );
		
		return ( strcmp( $key, $pbk ) === 0 );
	}
	
	function rnd( $size ) {
		return mcrypt_create_iv( $size, MCRYPT_DEV_URANDOM );
	}
}


