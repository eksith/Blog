<?php

namespace Blog\Core;
use Blog\Events;

/**
 * Application configuration
 */
final class Config extends Events\Pluggable {
	
	private $settings;
	private $crypto;
	
	const DECODE_DEPTH	= 5;
	
	public function __construct( Crypto $crypto ) {
		$this->crypto	= $crypto;
		$this->settings = $this->loadConfig();
		
		$this->hook( 'ConfigInit', $this, $crypto );
	}
	
	public function setSetting( $name, $value ) {
		$this->settings[$name] = $value;
	}
	
	public function getSetting( $name ) {
		return isset( $this->settings[$name] ) ? 
			$this->settings[$name] : null;
	}
	
	public function getSettings( array $names = array() ) {
		$values = array();
		foreach ( $names as $name ) {
			$values[$name] = 
				isset( $this->settings[$name] ) ? 
					$this->settings[$name] : null;
		}
		
		return $values;
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
			$data	= trim( html_entity_decode( CONFIG ) );
			$data	= 
			json_decode( 
				$data,
				true, 
				self::DECODE_DEPTH 
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
