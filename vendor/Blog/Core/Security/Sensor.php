<?php

namespace Blog\Core\Security;
use Blog\Messaging;

class Sensor {
	
	/**
	 * @var array Global variable names
	 */
	private $global_vars = array(
		'$_SERVER',
		'$_GET',
		'$_POST',
		'$_FILES',
		'$_SESSION',
		'$_ENV',
		'$_COOKIE'
	);
	
	/**
	 * @var array Whitelist of acceptable methods
	 */
	private $methods 	= array( 'get', 'head', 'post', 'put' );
	
	/**
	 * @var array Whitelist of acceptable ports
	 */
	private $ports		= array( 80, 443 );
	
	/**
	 * @var ServerRequest 
	 */
	private $request;
	
	/**
	 * @var BrowserProfile 
	 */
	private $browser;
	
	public function __construct(
		Messaging\ServerRequest $request
	) {
		$this->request	= $request;
		#$this->browser	= $request->getBrowserProfile()->browser();
	}
	
	/**
	 * Run firewall
	 */
	public function run() {
		$this->accept( $this->methods );
		$this->checkHeaders();
		$this->requestScan();
		$this->uaScan();
	}
	
	/**
	 * Add to the list of allowed ports
	 */
	public function addPort( $port ) {
		$port = ( int ) $port;
		if ( $port < 1 || $port > 65535 ) {
			$this->end( 'Invalid port number' );
		}
		$this->ports[]	= $port;
		$this->ports	= array_unique( $this->ports );
	}
	
	/**
	 * Detect global variable pollution
	 */
	private function globalInjection() {
		foreach ( $this->global_vars as $pre ) {
			if ( !isset( $pre ) ) {
				continue;
			}
			
			foreach( $pre as $k => $v ) {
				if ( $this->isearch( $pre, $v ) ) {
					return $this->end( 'Global injection' );
				}
			}
		}
	}
	
	/**
	 * Scan request path for anomalies
	 */
	private function requestScan() {
		$uri = $this->request->getUri()->getRawPath();
		# TODO
	}
	
	/**
	 * Scan user agent for anomalies
	 */
	private function uaScan() {
		# TODO
	}
	
	/**
	 * Scan request body for malicious content
	 */
	private function bodyScan() {
		# TODO
	}
	
	// https://eksith.wordpress.com/2013/11/04/firewall-php/
	
	/**
	 * Check sent headers for unusual characteristics
	 */
	private function checkHeaders() {
		$headers  = $this->request->getHeaders();
		/**
		 * Accept missing. Not acceptable.
		 */
		if ( $this->missing( $headers, 'Accept' ) ) {
			return $this->end( 'Invalid header' );
		}
		
		/**
		 * No UA or it's too short
		 */
		if ( $this->missing( $headers, 'User-Agent', 10 ) ) {
			return $this->end( 'Invalid user agent' );
		}
		
		/**
		 * Shouldn't see MSIE *and* Windows ME/XP/2000 in the same 
		 * UA string
		 */
		if ( 
			$this->has( $headers, 'User-Agent', '; MSIE' ) &&
			$this->has( $headers, 'User-Agent', 'Windows 2000' ) || 
			$this->has( $headers, 'User-Agent', 'Windows ME' ) || 
			$this->has( $headers, 'User-Agent', 'Windows XP' ) 
		) {
			return $this->end( 'Invalid user agent' );
		}
	}
	
	/**
	 * Check request method against expected list of methods.
	 * Kills the script on failure.
	 */
	private function accept( $methods ) {
		$request = strtolower( $_SERVER['REQUEST_METHOD'] );
		if ( is_array( $methods ) ) { 
			if ( in_array( $request, $methods ) ) {
				return;
			}
		} elseif ( $request == $methods ) { 
			return;
		}
		$this->end( 'Method rejected' );
	}
	
	/**
	 * Scrub globals
	 */
	private function cleanGlobals() {
		if ( isset( $GLOBALS ) ) {
			foreach ( $GLOBALS as $k => $v ) {
				if ( 0 != strcasecmp( 
					$k, 'GLOBALS' 
				) ) {
					unset( $GLOBALS[$k] );
				}
			}
		}
	}
	
	/**
	 * Check an array for a needle (case insensitive)
	 */
	private function isearch( $ar, $needle ) {
		return in_array(
			strtolower( $needle ), 
			array_map( 'strtolower', $ar )
		);
	}
	
	/**
	 * Check for completely missing headers, headers which contain 
	 * an empty string or is below the minimum length
	 */
	public static function missing( $h, $k, $min = 0 ) {
		if ( array_key_exists( $k, $h ) ) {
			if ( empty( $h[$k] ) ) {
				return true;
			}
			
			$chk = is_array( $h[$k] ) ? $h[$k][0] : $h[$k];
			if ( 
				$min > 0 && 
				mb_strlen( $chk, '8bit' ) < $min 
			) {
				return true;
			}
			return false;
		}
		
		return true;
	}
	
	/**
	 * Helper to see if a key exists in an array, has a component
	 * to search in the value or matches to an optional regular expression
	 */
	public static function has( $h, $k, $v = null, $regex = false ) {
		$has = array_key_exists( $k, $h );
		
		/**
		 * Only checking for key existence
		 */
		if ( null === $v || !$has ) {
			return $has;
		}
		
		if ( is_array( $v ) ) {
			foreach( $v as $name ) {
				if ( false === stripos( $name, $h[$k] ) ) {
					continue;
				} else {
					return true;
				}
			}
			
			/**
			 * Made it this far. The key wasn't in the array
			 */
			return false;
		}
		
		/**
		 * The key value should be a regular expression match
		 */
		if ( $regex ) {
			return preg_match('/\b'. $v .'\b/i', $h[$k] );
		}
		
		$chk = is_array( $h[$k] ) ? $h[$k][0] : $h[$k];
		if ( false === stripos( $chk, $v ) ) {
			return false;
		}
		
		return $has;
	}
	
	/**
	 * Skip output and end the script
	 */
	private function end( $msg = '' ) {
		$this->cleanGlobals();
		ob_start();
		ob_end_clean();
		die( $msg );
	}
}
