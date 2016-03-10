<?php

namespace Blog\Core\Security;
use Blog\Core;
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
	 * @var string Request method
	 */
	private $method;
	
	/**
	 * @var array Whitelist of acceptable ports
	 */
	private $ports		= array( 80, 443 );
	
	/**
	 * @var object ServerRequest 
	 */
	private $request;
	
	/**
	 * @var object App configuration
	 */
	private $config;
	
	/**
	 * @var object Cryptography
	 */
	private $crypto;
	
	/**
	 * @var object BrowserProfile 
	 */
	private $browser;
	
	/**
	 * @var object IP class
	 */
	private $ip;
	
	public function __construct(
		Messaging\ServerRequest $request,
		Core\Config $config,
		Core\Crypto $crypto
	) {
		$this->request	= $request;
		$this->config	= $config;
		$this->crypto	= $crypto;
		
		$this->ip	= new IP();
		$this->browser	= $request->getBrowserProfile();
		$this->method	= 
			strtolower( $this->request->getMethod() );
		
		$this->sessionCheck();
	}
	
	/**
	 * Run firewall
	 */
	public function run() {
		$this->checkPort();
		$this->accept( $this->methods );
		$this->requestScan();
		
		$headers	= $this->request->getHeaders();
		$hash		= $this->browser->headerHash();
		
		# Prevent redundant scans if it's the same user
		if ( $this->expired( $hash ) ) {
			$this->ipScan();
			$this->checkHeaders();
			$this->uaScan();
			
			$_SESSION[$hash]	= true;
		}
		
		$search	= array( 'put', 'post' );
		if ( in_array( $this->method, $search ) ) {
			$this->bodyScan();
		}
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
			
			foreach ( $pre as $k => $v ) {
				if ( $this->isearch( $pre, $v ) ) {
					$this->end( 'Global injection' );
				}
			}
		}
	}
	
	/**
	 * Check if a scan is needed from the last time using headers
	 */
	private function expired( $hash ) {
		if ( \session_status() === \PHP_SESSION_ACTIVE ) {
			return isset( $_SESSION[$hash] ) ? 
					false : true;
		}
		
		return true;
	}
	
	/**
	 * Scan IP blocklist
	 */
	private function ipScan() {
		$ip	= strtolower( $this->ip->getIP() );
		
		# Running locally? Skip IP check
		if ( !$this->getSetting( 'firewall_local' ) ) {
			if ( !$this->ip->validateIP( $ip ) ) {
				$this->end( 'Invalid source' );
			}
		}
		$host	= trim( strtolower( gethostbyaddr( $ip ) ) );
		
		$this->blacklist( 
			'firewall_ip',
			function( $u ) use ( $ip, $host ) {
				$len	= mb_strlen( $u, '8bit' );
				if ( 0 === strncmp( $ip, $u, $len ) ) {
					$this->end( 'Denied IP' );
				}
				
				if ( 0 === strncmp( $host, $u, $len ) ) {
					$this->end( 'Denied host' );
				}
			}
		);
	}
	
	/**
	 * Scan request path for anomalies
	 */
	private function requestScan() {
		$uri		= $this->request
					->getUri()
					->getRawPath();
		
		$this->blacklist( 
			'firewall_uri',
			function( $u ) use ( $uri ) {
				if ( false !== stripos( $uri, $u ) ) {
					$this->end( 'Invalid URI' );
				}
			}
		);
	}
	
	/**
	 * Scan user agent for anomalies
	 */
	private function uaScan() {
		$ua		= $this->request
					->getHeader( 'User-Agent' );
		$this->blacklist( 
			'firewall_ua',
			function( $u ) use ( $ua ) {
				if ( false !== stripos( $ua[0], $u ) ) {
					$this->end( 'Invalid browser' );
				}
			}
		);
	}
	
	/**
	 * Scan request body for malicious content
	 */
	private function bodyScan() {
		# TODO
		# $body	= $this->request->getBody();
		
	}
	
	/**
	 * Blacklist file loader and filter
	 * 
	 * @param string $file Name of configuration file
	 * @param callable $map Optional filter function callback
	 */
	private function blacklist( $file, $map = null ) {
		$data	= 
		\file( 
			$this->getSetting( $file ),
			\FILE_SKIP_EMPTY_LINES 
		);
		
		$filter	= array();
		foreach ( $data as $u ) {
			$u	= trim( $u );
			if ( empty( $u ) ) {
				continue;
			}
			if ( ';' == substr( $u, 0, 1 ) ) {
				continue; # Skip comments
			}
			
			$filter[] = $u;
		}
		
		if ( is_callable( $map ) ) {
			return array_map( $map, $filter );
		}
		return $filter;
	}
	
	/**
	 * Check request URI port
	 */
	private function checkPort(){
		$port		= $this->request
					->getUri()
					->getPort();
		if ( !in_array( $port, $this->ports ) ) {
			$this->end( 'Invalid port' );
		}
	}
	
	# https://eksith.wordpress.com/2013/11/04/firewall-php/
	
	/**
	 * Check sent headers for unusual characteristics
	 */
	private function checkHeaders() {
		$headers  = $this->request->getHeaders();
		/**
		 * Accept missing. Not acceptable.
		 */
		if ( $this->missing( $headers, 'Accept' ) ) {
			$this->end( 'Invalid header' );
		}
		
		/**
		 * No UA or it's too short
		 */
		if ( $this->missing( $headers, 'User-Agent', 10 ) ) {
			$this->end( 'Invalid user agent' );
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
			$this->end( 'Invalid user agent' );
		}
	}
	
	/**
	 * Check request method against expected list of methods.
	 * Kills the script on failure.
	 */
	private function accept( $methods ) {
		$request = $this->method;
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
	 * Scrub session
	 */
	private function cleanSession() {
		if ( \session_status() === \PHP_SESSION_ACTIVE ) {
			session_unset();
			session_destroy();
			session_write_close();
		}
	}
	
	private function getSignature() {
		return $this->browser->getSignature();
	}
	
	/**
	 * First visit session initialization
	 */
	private function session( $reset = false ) {
		if ( 
			\session_status() === \PHP_SESSION_ACTIVE && 
			!$reset 
		) {
			return;
		}
		
		if ( \session_status() != \PHP_SESSION_ACTIVE ) {
			session_start();
		}
		if ( $reset ) {
			\session_regenerate_id( true );
			foreach ( array_keys( $_SESSION ) as $k ) {
				unset( $_SESSION[$k] );
			}
		}
	}
	
	/**
	 * Check session staleness
	 */
	private function sessionCheck( $reset = false ) {
		$this->session( $reset );
		
		if ( empty( $_SESSION['canary'] ) ) {
			$this->sessionCanary();
			return;
		}
		
		if ( 
			time() > ( int ) $_SESSION['canary']['exp']
		) {
			\session_regenerate_id( true );
			$this->sessionCanary();
		}
	}
	
	/**
	 * Session owner and staleness marker
	 * 
	 * @link https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
	 */
	private function sessionCanary() {
		$key	= $this->config->getSetting( 'visit_key' );
		$time	= $this->config->getSetting( 'session_time' );
		$bytes	= $this->crypto->bytes( $key );
		
		$_SESSION['canary'] = array(
			'exp'	=> time() + $time,
			'visit'	=> bin2hex( $bytes )
		);
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
		$chk = is_array( $h[$k] ) ? $h[$k][0] : $h[$k];
		
		if ( is_array( $v ) ) {
			foreach( $v as $name ) {
				if ( false === stripos( 
					$name, $chk 
				) ) {
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
			return preg_match('/\b'. $v .'\b/i', $chk );
		}
		
		if ( false === stripos( $chk, $v ) ) {
			return false;
		}
		
		return $has;
	}
	
	/**
	 * Get configuration setting
	 */
	private function getSetting( $name ) {
		return $this->config->getSetting( $name );
	}
	
	/**
	 * Skip output and end the script
	 */
	private function end( $msg = '' ) {
		$this->cleanGlobals();
		$this->cleanSession();
		
		ob_start();
		ob_end_clean();
		die( $msg );
	}
}
