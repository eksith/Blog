<?php

namespace Blog\Core;
use Blog\Events;

/**
 * Application configuration
 */
final class Config extends Events\Pluggable {
	
	private $settings;
	private $crypto;
	private $modified	= false;
	
	const DECODE_DEPTH	= 5;
	
	public function __construct( Crypto $crypto ) {
		$this->crypto	= $crypto;
		$this->settings = $this->loadConfig();
		
		$this->hook( 'ConfigInit', $this, $crypto );
	}
	
	public function __destruct() {
		if ( $this->modified ) {
			$this->saveConfig();
		}
	}
	
	/**
	 * Change a saved configuration setting
	 */
	public function setSetting( $name, $value ) {
		$this->settings[$name] = $value;
	}
	
	/**
	 * Get a single configuration setting
	 */
	public function getSetting( $name ) {
		return isset( $this->settings[$name] ) ? 
			$this->settings[$name] : null;
	}
	
	/**
	 * Get multiple configuration settings at once
	 */
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
	 * Load file contents and check for any server-side code
	 */
	private function loadFile( $name ) {
		if ( file_exists( $name ) ) {
			$data = file_get_contents( $name );
			if ( false !== strpos( $data, '<?' ) ) {
				die( 'Server-side code in config. Exiting.' );
			}
			return $data;
			
		}
		return null;
	}
	
	/**
	 * Load configuration file ( JSON formatted )
	 *
	 * @return array
	 */
	private function loadConfig() {
		if ( defined( 'CONFIG' ) ) {
			$file	= loadFile( CONFIG );
			if ( empty( $file ) ) {
				die( 'Unable to load configuration' );
			}
			$data	= trim( html_entity_decode( $file ) );
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
	 * Save configuration file as JSON
	 */
	private function saveConfig() {
		$data	= json_encode( $this->settings, 
				\JSON_HEX_QUOT | \JSON_HEX_TAG | 
				\JSON_PRETTY_PRINT );
		
		file_put_contents( CONFIG, $data );
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
