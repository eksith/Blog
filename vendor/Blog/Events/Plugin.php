<?php

namespace Blog\Events;

/**
 * Plugin/Hook class. All plugins must extend this class
 * (List of events is still being expanded)
 */
abstract class Plugin {
	
	/**
	 * Hook call
	 */
	public function __call( $method, $args ) {
		$func = 'on'. $method;
		if ( method_exists( $this, $func ) ) {
			call_user_func_array( 
				array( $this, $func ), $args 
			);
		}
	}
	
	/**
	 * Configuration class initialized with crypto class and settings
	 */
	public abstract function onConfigInit( Pluggable $class, array $args );
	
	/**
	 * Navigation route, path, HTTP method verb
	 */
	public abstract function onRouteAdded( Pluggable $class, array $args );
	
	/**
	 * Sent route/verb combination missing
	 */
	public abstract function onRouteVerbMissing( Events\Pluggable $class, array $args );
	
	/**
	 * Route with matching path/verb found
	 */
	public abstract function onRouteFound( Events\Pluggable $class, array $args );
	
	/**
	 * No routes specified for this path
	 */
	public abstract function onRouteNotFound( Events\Pluggable $class, array $args );
	
	/**
	 * A route handler has been initialized
	 */
	public abstract function onRouteInit( Events\Pluggable $class, array $args );
	
	/**
	 * Route parsing has started
	 */
	public abstract function onRouting( Events\Pluggable $class, array $args );
	
	/**
	 * Route handler created and sent
	 */
	public abstract function onRouteSent( Events\Pluggable $class, array $args );
	
	/**
	 * Visitor is being sent to another location
	 */
	public abstract function onRouteRedirect( Events\Pluggable $class, array $args );
}
