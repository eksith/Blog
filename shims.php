<?php

/**
 * Compatibility shims for older PHP versions
 */

/**
 * Check if a function exists ( Suhosin compatible )
 * 
 * @param string $func Function name
 * @return boolean true If the function exists
 */
function missing( $func ) {
	if ( \extension_loaded( 'suhosin' ) ) {
		$exts = ini_get( 'suhosin.executor.func.blacklist' );
		if ( !empty( $exts ) ) {
			$blocked	= explode( ',', strtolower( $exts ) );
			$blocked	= array_map( 'trim', $blocked );
			$search		= strtolower( $func );
			
			return ( 
				false	== \function_exists( $func ) && 
				true	== array_search( $search, $blocked ) 
			);
		}
	}
	
	return !\function_exists( $func );
}

if ( missing( 'preg_replace_callback_array' ) ) {
	function preg_replace_callback_array( 
		$filters, 
		$html, 
		$limit = -1  
	) {
		$i = $limit;
		foreach( $filters as $regex => $handler ) {
			if ( $limit != -1 && $i <= 0 ) {
				break;
			}
			$html =	preg_replace_callback( 
				$regex, $handler, $html
			);
			
			$i--;
		}
		
		return $html;
	}
}

if ( missing( 'hash_equals' ) ) {
	function hash_equals( $str1, $str2 ) { 
		return 
		substr_count( $str1 ^ $str2, "\0" ) * 2 === 
			strlen( $str1 . $str2 );
	}
}

if ( missing( 'hash_pbkdf2' ) ) {
	function hash_pbkdf2( 
		$algo, 
		$txt, 
		$salt, 
		$rounds, 
		$kl		= 0,
		$raw		= false
	) {
		$hl	= strlen( $hash( $algo, '', true ) );
		
		$kl	= empty( $kl ) ? 
				( $raw ? $hl : $hl * 2 ) : $kl;
		
		$bc	= ceil( $kl / $hl );
		$hash	= '';
		
		for ( $i = 0; $i < $bc; $i++ ) {
			$last = $salt . pack( 'N', $i );
			$last = $xor = 	
				\hash_hmac( $algo, $last, $txt, true );
			
			for ( $j = 1; $j < $rounds; $j++ ) {
				$xor ^= 
				\hash_hmac( $algo, $last, $txt, true );
			}
			$hash .= $xor;
		}
		
		$hash = mb_substr( $hash, 0, $kl );
		
		return ( $raw )? $hash : base64_encode( $hash );
	}
}

if ( missing( 'random_bytes' ) ) {
	function random_bytes( $len ) {
		$bytes = '';
		
		if ( missing ( 'openssl_random_pseudo_bytes' ) ) {
			if ( missing( 'mcrypt_create_iv' ) ) {
				
				# Last chance
				$src = '/dev/urandom';
				if ( is_readable( $src ) ) {
					$bytes = 
					file_get_contents( 
						$src, false, null, 
						-1, $len 
					);
				}
				
			} else {
				$bytes = 
				\mcrypt_create_iv( 
					$len, \MCRYPT_DEV_URANDOM 
				);
			}
		} else {
			$bytes = \openssl_random_pseudo_bytes( $len );
		}
		
		if ( empty( $bytes ) ) {
			die( 'Unable to find random source' );
		}
		
		return $bytes;
	}
}

if ( missing( 'random_int' ) ) {
	/**
	 * @link https://paragonie.com/blog/2015/07/how-safely-generate-random-strings-and-integers-in-php
	 */
	function random_int( $min, $max ) {
		$mask	= 0;
		$bits	= 0;
		$bytes	= 0;
		$shift	= 0;
		$tries	= 0;
		$range	= $max - $min;
		$fail	= 'Could not generate random integer';
		
		while( $range > 0 ) {
			if ( $bits % 8 === 0 ) {
				++$bytes;
			}
			++$bits;
			$range >>= 1;
			$mask	= $mask << 1 | 1;
			
		}
		
		$shift	= $min;
		do {
			if ( $tries > 128 ) {
				die( $fail );
			}
			
			$rnd	= \random_bytes( $bytes );
			if ( $rnd === false ) {
				die( $fail );
			}
			
			$num	= 0;
			for( $i = 0; $i < $bytes; ++$i ) {
				$num |= ord( $rnd[$i] ) << ( $i * 8 );
			}
			
			$num	&= $mask;
			$num	+= $shift;
			++$tries;
			
		} while ( 
			$num < $min || 
			$num > $max || 
			!is_int( $num ) 
		);
		
		return $num;
	}
}
