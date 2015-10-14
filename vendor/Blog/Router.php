<?php

namespace Blog;

class Router {
	
	/**
	 * @var array Methods, routes and callbacks
	 */
	private static $routes	= array();
	
	/**
	 * @var object Event dispatcher
	 */
	private $dispatcher;
	
	/**
	 * Router constructor
	 * 
	 * @param	object		$dispatcher Event dispatcher
	 */
	public function __construct( $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}
	
	/**
	 * Add a request method with an accompanying route and callback
	 * 
	 * @param	string		$method Lowercase request method
	 * @param	string		$route Simple regex route path
	 * @param	callable	$callback Function call
	 */
	public static function add( $method, $route, $callback ) {
		# Format the regex pattern
		$route = self::cleanRoute( $route );
		
		# First time we're adding a path to this method?
		if ( !isset( self::$routes[$method] ) ) {
			 self::$routes[$method] = array();
		}
		
		# Add a route to this method and set callback as value
		self::$routes[$method][$route] = $callback;
	}
	
	/**
	 * Sort all sent routes for the current request method, iterate 
	 * through them for a match and trigger the callback function
	 */
	public function route() {
		if ( empty( self::$routes ) ) { # No routes?
			$this->fourOhFour();
		}
		
		# Client request path
		$path	= $_SERVER['REQUEST_URI'];
		
		# Client request method
		$method = strtolower( $_SERVER['REQUEST_METHOD'] );
		
		# Found flag
		$found	= false;
		
		# No routes for this method?
		if ( empty( self::$routes[$method] ) ) {
			$this->fourOhFour();
		}
		
		# For each path in each method, iterate until match
		foreach( self::$routes[$method] as $route => $callback ) {
			
			# Found a match for this method on this path
			if ( preg_match( $route, $path, $params ) ) {
				
				$found = true; # Set found flag
				if ( count( $params ) > 0) {
					# Clean parameters
					array_shift( $params );
				}
				
				# Patch in the event dispatcher
				array_unshift( $params, $this->dispatcher );
				
				# Trigger callback
				return call_user_func_array( 
					$callback, $params 
				);
			}
		}
		
		# We didn't find a path 
		if ( !$found ) {
			$this->fourOhFour();
		}
	}
	
	/**
	 * Paths are sent in bare. Make them suitable for matching.
	 * 
	 * @param	string		$route URL path regex
	 */
	private static function cleanRoute( $route ) {
		$regex	= str_replace( '.', '\.', $route );
		return '@^/' . $route . '/?$@i';
	}
	
	/**
	 * Possible 404 not found handler. 
	 * Something that looks nicer should be used in production.
	 */
	private function fourOhFour() {
		die( "<em>Couldn't find the page you're looking for.</em>" );
	}
}

