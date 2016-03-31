<?php
/**
 * @link https://github.com/ircmaxell/random_compat/blob/master/lib/random.php
 */
namespace Blog\Core;

final class Crypto {
	
	const BLOCK_SIZE	= 32;
	const KEY_SIZE		= 32;
	const MERGE_HASH	= 'sha256';
	const OSSL_IV_SIZE	= 'aes-256-cbc';
	const PBK_DELIMETER	= '$';		# Don't use a comma
	const PBK_REGEX		= '/[^a-f0-9\$]+$/i';
	const PBK_MAX		= 255;
	
	private static $rstate;
	
	public function encrypt( $message, $key ) {
		if ( empty( $key ) || empty( $message ) ) {
			return false;
		}
		$key	= $this->keyAdjust( $key );
		
		if ( function_exists( 'openssl_encrypt' ) ) {
			
			$iv		= $this->osslIV();
			$cipher		= 
			\openssl_encrypt(
				$message,
				self::OSSL_IV_SIZE,
				$key,
				\OPENSSL_RAW_DATA,
				$iv
			);
			
			return base64_encode( $iv . $cipher );
			
		} elseif ( function_exists( 'mcrypt_encrypt' ) ) {
			
			$iv		= $this->mcryptIV();
			$message	= $this->pkcsPad( $message );
			$cipher		= 
			\mcrypt_encrypt( 
				\MCRYPT_RIJNDAEL_128,
				$key, 
				$message, 
				'ctr', 
				$iv 
			);
			
			return base64_encode( $iv . $cipher );
		}
		
		die( 'Encryption not supported on this platform' );
	}
	
	public function decrypt( $message, $key ) {
		if ( empty( $key ) || empty( $message ) ) {
			return false;
		}
		$message	= base64_decode( $message, true );
		if ( false === $message ) {
			return false;
		}
		$key		= $this->keyAdjust( $key );
		
		if ( function_exists( 'openssl_decrypt' ) ) {
			
			$this->osslIVCipher( $message, $iv, $cipher );
			return 
			\openssl_decrypt(
				$cipher,
				self::OSSL_IV_SIZE,
				$key,
				\OPENSSL_RAW_DATA,
				$iv
			);
			
		} elseif ( function_exists( 'mcrypt_decrypt' ) ) {
			
			$this->mcryptIVCipher( $message, $iv, $cipher );
			$message = 
			\mcrypt_decrypt( 
				\MCRYPT_RIJNDAEL_128, 
				$key, $cipher, 
				'ctr', 
				$iv 
			);
			
			return $this->pkcsUnpad( $message );
		}
		
		die( 'Encryption not supported on this platform' );
	}
	
	private function osslIV() {
		$strong	= true;
		$ivs	= 
		\openssl_cipher_iv_length( self::OSSL_IV_SIZE );
		if ( $ivs === false || $ivs <= 0 ) {
			die( 'OpenSSL IV length error' );
		}
		return \openssl_random_pseudo_bytes( $ivs, $strong );
	}
	
	private function osslIVCipher(
		$message,
		&$iv		= '',
		&$cipher	= '' 
	) {
		$ivs	= 
		\openssl_cipher_iv_length( self::OSSL_IV_SIZE );
		if ( $ivs === false || $ivs <= 0 ) {
			die( 'OpenSSL IV length error' );
		}
		
		$iv	= 
		mb_substr( $message, 0, $ivs, '8bit' );
		$cipher	= 
		mb_substr( $message, $ivs, null, '8bit' );
	}
	
	private function mcryptIV() {
		$ivs	= \mcrypt_get_iv_size( \MCRYPT_RIJNDAEL_128 );
		if ( $ivs === false || $ivs <= 0 ) {
			die( 'mcrypt IV length error' );
		}
		return \mcrypt_create_iv( $ivs, \MCRYPT_DEV_URANDOM );
	}
	
	private function mcryptIVCipher(
		$message,
		&$iv		= '',
		&$cipher	= '' 
	) {
		$ivs	= 
		\mcrypt_get_iv_size( \MCRYPT_RIJNDAEL_128 );
		if ( $ivs === false || $ivs <= 0 ) {
			die( 'mcrypt IV length error' );
		}
		
		$iv	= 
		mb_substr( $message, 0, $ivs, '8bit' );
		$cipher	= 
		mb_substr( $message, $ivs, null, '8bit' );
	}
	
	private function pkcsPad( $message ) {
		$block		= 
		\mcrypt_get_block_size( \MCRYPT_RIJNDAEL_128 );
		$pad		= 
		$block - ( mb_strlen( $message, '8bit' ) % $block );
		
		return $message . str_repeat( chr( $pad ), $pad );
	}
	
	private function pkcsUnpad( $message ) {
		$block	= 
		\mcrypt_get_block_size( \MCRYPT_RIJNDAEL_128 );
		
		$len 	= mb_substr( $message, '8bit' );
		$pad	= ord( $message[$len-1] );
		
		if ( $pad <= 0 || $pad > $block ) {
			return false;
		}
		
		return mb_substr( $message, 0, $len - $pad, '8bit' );
	}
	
	private function keyAdust( $key ) {
		if ( mb_strlen( $key, '8bit' ) !== self::KEY_SIZE ) {
			return hash( 'sha256', $key, true );
		}
		
		return $key;
	}
	
	
	public function genPbk(
		$algo	= 'tiger160,4', 
		$txt,
		$salt	= null,
		$rounds	= 1000, 
		$kl	= 128 
	) {
		$rounds	= ( $rounds <= 0 ) ? 1000 : $rounds;
		$kl	= ( $kl <= 0 ) ? 128 : $kl;
		$salt	= empty( $salt ) ? 
				bin2hex( $this->bytes( 8 ) ) : $salt;
		
		$key	= \hash_pbkdf2( $algo, $txt, $salt, $rounds, $kl );
		$out	= array(
				$algo, $salt, $rounds, $kl, $key
			);
		
		return 
		base64_encode( implode( self::PBK_DELIMETER, $out ) );
	}
	
	public function verifyPbk( $txt, $hash ) {
		if ( 
			empty( $hash ) || 
			mb_strlen( $hash, '8bit' ) > self::PBK_MAX 
		) {
			return false;
		}
		$key	= base64_decode( $hash, true );
		if ( false === $key ) {
			return false;
		}
		
		$k	= explode( self::PBK_DELIMETER, $key );
		if ( empty( $k ) || empty( $txt ) ) {
			return false;
		}
		if ( count( $k ) != 5 ) {
			return false;
		}
		if ( !in_array( $k[0], \hash_algos() , true ) ) {
			return false;
		}
		
		$pbk	= $this->pbk( 
				$k[0], $txt, $k[1], ( int ) $k[2], 
				( int ) $k[3] 
			);
		
		return \hash_equals( $this->cleanPbk( $k[4] ),  $pbk );
	}
	
	private function cleanPbk( $hash ) {
		return preg_replace( self::PBK_REGEX, '', $hash );
	}
	
	private function ossl( $size ) {
		$strong		= true;
		static::$rstate	= 
		$this->merge( 
			static::$rstate, 
			\openssl_random_pseudo_bytes( $size, $strong ) 
		);
	}
	
	private function frand( $size, $src ) {
		if ( file_exists( $src ) && is_readable( $src ) ) {
			static::$rstate	= 
			$this->merge( 
				static::$rstate, 
				file_get_contents( 
					$src, false, null, -1, $size 
				) 
			);
		}
	}
	
	/**
	 * mt_rand Wrapper that fixes some rapid use anomalies (PHP < 5.4)
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
	
	public function random( $level = 0 ) {
		if ( isset( static::$rstate ) ) {
			static::$rstate	= 
			$this->merge ( 
				static::$rstate, 
				\random_bytes( self::BLOCK_SIZE );
			);
		} else {
			static::$rstate	= 
			\random_bytes( self::BLOCK_SIZE );
		}
		
		
		if ( $level <= 0 ) {
			return static::$rstate;
		}
		
		if ( function_exists( 
			'openssl_random_pseudo_bytes'
		) ) {
			$this->ossl( self::BLOCK_SIZE );
		}
		$this->frand( self::BLOCK_SIZE, '/dev/arandom' );
		
		if ( $level >= 2 ) {
			$this->frand( self::BLOCK_SIZE, '/dev/random' );
		}
		return static::$rstate;
	}
	
	public function bytes( $size, $level = 0 ) {
		$blocks		= 
		max( ceil( $size / self::BLOCK_SIZE ), 1 );
		
		$result		= '';
		for ( $i = 0; $i < $blocks; $i++ ) {
			$result .= $this->random( $level );
		}
		
		static::$rstate	= 
		$this->merge( 
			static::$rstate, substr( $result, $size ) 
		);
		
		return substr( $result, 0, $size );
	}
	
	private function merge( $src1, $src2 ) {
		if ( isset( static::$rstate ) ) {
			$i = ord( static::$rstate ) % 2;
		} else {
			$i = $this->rnum( 0, 255 ) % 2;
		}
		
		if ( $i === 0 ) {
			return
			\hash_hmac( 
				self::MERGE_HASH, $src1, $src2, true
			);
		}
		
		return 
		\hash_hmac( 
			self::MERGE_HASH, $src2, $src1, true 
		);
	}
}

