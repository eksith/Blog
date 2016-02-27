<?php

namespace Blog\Core;

/**
 * Application configuration
 */
final class Config {
	
	private $settings;
	private $crypto;
	
	const DECODE_DEPTH	= 5;
	
	public function __construct( Crypto $crypto ) {
		$this->crypto	= $crypto;
		$this->settings = $this->loadConfig();
	}
	
	public function getSetting( $name ) {
		return isset( $this->settings[$name] ) ? 
			$this->settings[$name] : null;
	}
	
	/**
	 * Load application configuration settings from a defined 
	 * variable that is JSON formatted or get it from an encrypted
	 * ini setting. Decryption key is split between config file
	 * and ini settings
	 * 
	 * @return array
	 */
	private function loadConfig() {
		if ( defined( 'CONFIG' ) ) {
			$data	= html_entity_decode( CONFIG );
			$data	= 
			json_decode( 
				preg_replace( '/\s+/', '', $data ),
				true, 
				self::DECODE_DEPTH 
			);
		} elseif (
			defined( 'KEY_PART1' ) && 
			defined( 'KEY_PART2' ) && 
			defined( 'ENCRYPTED_CONFIG' )
		) {
			$key2	= ini_get( KEY_PART2 );
			$data	= $this->crypto->decrypt( 
					CONFIG, KEY_PART1 . $key2
				);
		} else {
			die( 'Could not load application settings' );
		}
		
		return $this->parse( $data );
	}
	
	/**
	 * Convert relative |PATH| markers to actual app path
	 * 
	 * @return array
	 */
	private function parse( $data ) {
		if ( empty( $data ) ) {
			return array();
		}
		
		foreach ( $data as $k => $v ) {
			if ( is_array( $v ) ) {
				$this->parse( $v );
				continue;
			}
			
			if ( false !== strpos( $v, '|PATH|' ) ) {
				$data[$k] = 
				str_replace( '|PATH|', PATH, $v );
			}
		}
		return $data;
	} 
}
