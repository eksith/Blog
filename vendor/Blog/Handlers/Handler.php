<?php

namespace Blog\Handlers;
use Blog\Events;
use Blog\Core;

class Handler extends Events\Listener {
	
	private static $cache	= array();
	private static $isAjax;
	private static $headers;
	private static $html_filter;
	
	protected function getHtmlFilter() {
		if ( !isset( self::$html_filter ) ) {
			self::$html_filter = new Core\HtmlFilter();
		}
		
		return self::$html_filter;
	}
	
	protected function setCache( $key, $data ) {
		self::$cache[$key]	= $data;
	}
	
	protected function decode( $txt, $max = 200 ) {
		if ( mb_strlen( $txt, '8bit' ) > $max ) {
			return false;
		}
		
		return base64_decode( $txt, true );
	}
	
	/**
	 * Shutdown event helper.
	 * Finish the request and optionally send the output buffer.
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
	 * Check for AJAX header
	 */
	protected function isAjax( $re ) {
		if ( isset( self::$isAjax ) ) {
			return self::$isAjax ;
		}
		$re	= $this->getRequest()
				->getHeader( 'X-Requested-With' );
		$isAjax	= false;
		
		if ( empty( $re ) ) {
			return false;
		}
		if ( 'xmlhttprequest' == strtolower( $re[0] ) ) {
			$isAjax = true;
		}
		self::$isAjax = $isAjax;
		return $isAjax;
	}
	
	
	/**
	 * Save an encrypted cookie
	 * 
	 * @link https://paragonie.com/blog/2015/05/using-encryption-and-authentication-correctly
	 */
	protected function saveCookie( $name, $data, $key ) {
		$data	= base64_encode( $data );
		$crypto	= $this->getCrypto();
		$config = $this->getConfig();
		
		$cookie	= $crypto->encrypt( $data, $key );
		$hash	= $crypto->genPbk( 
				$config->getSetting( 'cookie_hash' ), 
				$key . $data 
			);
		
		return 
		setcookie( 
			$name, 
			$hash . '|' . $cookie, 
			time() + $config->getSetting( 'cookie_time' ), 
			$config->getSetting( 'cookie_path' ), 
			$config->getSetting( 'cookie_secure' ), 
			true 
		);
	}
	
	/**
	 * Get sanitized cookie by name and maximum content size
	 */
	protected function getCookie( $name, $key, $max = 2045 ) {
		if ( !isset( $_COOKIE[$name] ) ) {
			return false;
		}
		
		if (
			empty( $_COOKIE[$name] )			|| 
			mb_strlen( $_COOKIE[$name], '8bit' ) > $max	|| 
			false === strrpos( $_COOKIE[$name], '|' )
		) {
			return false;
		}
		
		$data	= explode( '|', $_COOKIE[$name], 2 );
		if ( count( $data ) != 2 ) {
			return false;
		}
		
		$crypto	= $this->getCrypto();
		
		if ( $crypto->verifyPbk( 
			$key . $data[1], $data[0]
		) ) {
			$cookie	= $crypto->decrypt( $data[1], $key );
			return base64_decode( $cookie, true );
		}
		
		return false;
	}
	
	/**
	 * Anti-XSS request token generator
	 */
	protected function getCsrf( $form, Events\Event $event ) {
		$session	= $event->get( 'session_id' );
		$sig		= $this->getSignature();
		$crypto		= $this->getCrypto();
		
		$algo		= $this->getSetting( 'csrf_hash' );
		$rounds		= $this->getSetting( 'csrf_rounds' );
		$size		= $this->getSetting( 'csrf_size' );
		$salt		= $this->getSetting( 'csrf_salt' );
		
		return $crypto->genPbk(
			$algo,
			$form . $session . $sig,
			bin2hex( $crypto->bytes( $salt ) ),
			$rounds,
			$size 
		);
	}
	
	/**
	 * Verify anti-XSS request token against user profile
	 */
	protected function verifyCsrf(
		$hash,
		$form,
		Events\Event $event
	) {
		$session	= $event->get( 'session_id' );
		$sig		= $this->getSignature();
		
		return $this->getCrypto()->verifyPbk(
			$form . $session . $sig, $hash
		);
	}
	
	/**
	 * Safely redirect to another URL in the same domain with 
	 * optional status code
	 * 
	 * @param string $url URL without 'http://' etc...
	 * @param int $code Redirect code
	 */
	protected function redirect( $url, $code = 200 ) {
		if ( headers_sent() ) {
			die();
		}
		
		$base	= $this->getRequest()
				->getUri()
				->getRoot();
		$path	= ltrim( $url, '/\\' );
		$status	= 
		array( 200, 201, 202, 203, 204, 205, 300, 301, 302, 303, 
			304, 401, 403, 404 );
		
		if ( !in_array( $code, $status ) ) {
			$code = 302;
		}
		
		header( "Location: $base/$path", true, $code );
		die();
	}
	
	/**
	 * Create a hashed path based on a name and directory root.
	 * The path will be unique as long as the name is unique.
	 * @example root/c1d/abe/654/0b9/d8a/ac3/a73/c1dabe6540b9d8aac3a73b8ae3b34e121237ee996b966b8d
	 */
	protected static function hashPath( 
		$root, 
		$name, 
		$create	= false, 
		$dlen	= 3, 
		$depth	= 7 
	) {
		$h = hash( 'tiger192,4', $name );
		$p = array_slice( str_split( $h, $dlen ), 0, $depth );
		$t = $root . implode( DIRECTORY_SEPARATOR, $p ) . 
			DIRECTORY_SEPARATOR;
		
		# If we're asked to create this path, do so
		if ( $create && !is_dir( $t ) ) {
			$s = $root;
			# Loop because sometimes the recursion flag has issues with mkdir
			foreach( $p as $d ) {
				$s .= $d . DIRECTORY_SEPARATOR;
				# Already exists? Skip.
				if ( is_dir( $s ) ) { continue; }
				# Archives only need read/write permissions
				mkdir( $s, 0600 );
			}
		}
		return $t . $h;
	}
}
