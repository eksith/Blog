<?php

namespace Blog\Events;
/**
 * Blog plugin/hook system
 */
class Pluggable {
	
	/**
	 * @var SplObjectStorage Registered list of plugins
	 */
	public static $plugins;
	
	/**
	 * Register a new plugin class
	 * It's recommended to put all plugins in their own folder even 
	 * if some of them consist of a single file
	 */
	public static function register( $plugin ) {
		if ( !isset( static::$plugins ) ) {
			static::$plugins = new \SplObjectStorage();
		}
		
		if ( static::$plugins->contains( $plugin ) ) {
			return;
		}
		
		static::$plugins->attach( $plugin );
	}
	
	/**
	 * Call hook on any registered plugins. 
	 * Note: The hook name should be the first argument passed
	 * 
	 * Avoid calling this on parent classes
	 */
	public function hook() {
		if ( func_num_args() < 2 ) {
			return;
		}
		
		$args	= func_get_args();
		$name	= array_shift( $args );
		
		if ( !isset( static::$plugins ) ) {
			static::$plugins = new \SplObjectStorage();
			return;
		}
		
		foreach( static::$plugins as $plugin ) {
			call_user_func_array( 
				array( $plugin, $name ), $args 
			);
		}
	}
}
