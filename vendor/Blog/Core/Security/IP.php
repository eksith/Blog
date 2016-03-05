<?php

namespace Blog\Core\Security;

/**
 * IP address detection and parsing
 */
class IP {
	
	private $ip;
	
	/**
	 * Best effort IP address retrieval
	 */
	public function getIp() {
		if ( isset( $this->ip ) ) {
			return $this->ip;
		}
		
		$vars = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);
		
		foreach( $vars as $v ) {
			if ( !array_key_exists( $v, $_SERVER ) ) {
				continue;
			}
			
			$range = array_reverse( explode( ',', $v ) );
			
			foreach( $range as $i ) {
				if ( $this->validateIP( $i ) ) {
					$this->ip = $i ;
					break;
				}
			}
		}
		
		# Fallback
		if ( !isset( $this->ip ) ) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}
		return $this->ip;
		
	}
	
	/**
	 * Checks a given IP range in CIDR format
	 */
	private function rangeScan( $ips = array() ) {
		$out = false;
		foreach( $ips as $ip ) {
			if ( $out = $this->cidr( $ip, $this->ip ) ) {
				# IP in the given list  Exit loop
				break;
			}
		}
		return $out;
	}
	
	private function formatIP4( $ip, $pad = '0' ) {
		$ip	= str_replace( '*', $pad, $ip );
		$bits	= null;
		$p	= strpos( $ip, '/' );
		if ( false !== $p ) { 
			$bits	= substr( $ip, $p, strlen( $ip ) - 1 );
			$ip	= substr( $ip, 0, $p );
		}
		
		$sr	= explode( '.', $ip );
		while( count( $sr ) < 4) {
			$sr[] = $pad;
		}
		$ip	= implode('.', $sr );
		
		return $ip . $bits;
	}
	
	private function matchIP4StartToEnd( $start, &$end ) {
		if ( empty( $end ) ) {
			$end    = array();
			$d  = explode( '.', $start );
			$c  = count( $d );
			
			for( $i = 0; $i < $c; $i++ ) {
				if ( empty( $d[$i] ) ) {
					$end[$i] = '255';
				} else {
					$end[$i] = $d[i];
				}
			}
		} else {
			$end = str_replace( '*', '255', $end );
		}
		
		$end = $this->formatIP4( $end, '255' );
	}
	
	/**
	 * Checks if an IP is between an IPv4 range
	 */
	public function ip4Range( $start, $end, $ip ) {
		$start  = $this->formatIP4( $start, '0' );
		
		# Bits E.G.'/16' was present. Send to CIDR validation
		if ( false !== strpos( $start, '/' ) ) {
			return $this->cidr( $start, $ip );
		}
		
		$this->matchIP4StartToEnd( $start, $end );
		
		$start	= ip2long( $start );
		$ip	= ip2long( $ip );
		$end	= ip2long( $end );
		
		if ( $start <= $ip && $end >= $ip ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * TODO: Create IPv6 matching
	 */
	private function ip6Range( $start, $end, $ip ) {
		return false;
	}
	
	/**
	 * CIDR format IP matching
	 */
	private function cidr( $r, $ip ) {
		list( $sub, $bits ) = explode( '/', $r );
		
		$ip	= ip2long( $ip );
		$sub	= ip2long( $sub );
		$mask	= ( -1 << ( 32 - $bits ) );
		
		$sub	&= $mask; # Fix inconsistencies
         
		return ( $ip & $mask ) == $sub;
	}
	
	/**
	 * Converts an IP4 address to IP6.
	 * Convenient to store as a single format
	 */
	private function ip4Toip6( $ip ) {
		if ( filter_var( $ip, 
			\FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6 ) ) {
			return $this->cleanIPv6( $ip ); # Already IPv6
		}
		
		$ia = array_pad( explode( '.', $ip ), 4, 0 );
		$b1 = base_convert( ($ia[0] * 256 ) + $ia[1], 10, 16 );
		$b2 = base_convert( ($ia[2] * 256 ) + $ia[3], 10, 16 );
		
		return "0000:0000:ffff:$b1:$b2";
	}
      
	/**
	 * Expand IPv6 to proper storage
	 * 
	 * @link http://php.net/manual/en/function.inet-pton.php
	 */
	private function cleanIPv6( $ip ) {
		$h  = unpack( "H*hex", inet_pton( $ip ) );
		$ip = preg_replace( '/([A-f0-9]{4})/', "$1:", $hex['hex'] );
		
		return substr( $ip , 0, -1 );
	}
	
	/**
	 * Checks for martians E.G. 10.0.0.0/8
	 * These should really be blocked at the router/switch
	 */
	private function validateIP( $ip ) {
		if ( filter_var(
			$ip,
			\FILTER_VALIDATE_IP,
			\FILTER_FLAG_NO_PRIV_RANGE | 
			\FILTER_FLAG_NO_RES_RANGE
		) ) {
			return true;
		}
		return false;
	}
}
