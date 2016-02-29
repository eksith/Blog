<?php

namespace Blog\Core;
use Blog\Messaging;

class Router {
	
	private $request;
	
	private static $routes	= array();
	
	public function __construct( Messaging\ServerRequest $request ) {
		$this->request = $request;
	}
	
	public function add( $verb, $path, $route ) {
		if ( !isset( self::$routes[$verb] ) ) {
			self::$routes[$verb] = array();
		}
		self::$routes[$verb][$this->cleanRoute( $path )] = 
			$route;
	}
	
	public function route( array $markers ) {
		$verb	= strtolower( $this->request->getMethod() );
		if ( !isset( self::$routes[$verb] ) ) {
			return;
		}
		
		$found	= false;
		$params	= array();
		$path	= $this->request->getUri()->getPath();
		$k	= array_keys( $markers );
		$v	= array_values( $markers );
		
		foreach ( self::$routes[$verb] as $route => $handler ) {
			$route	= str_replace( $k, $v, $route );
			if ( preg_match( $route, $path, $params ) ) {
				$found = true;
				$this->send( $params, $route, $handler );
			}
		}		
	}
	
	private function send( $params, $route, $handler ) {
		$params = $this->filter( $params );
		
		if ( count( $params ) > 0 ) {
			// Clean parameters
			//array_shift( $params );
		}
		
		$handle = new $handler[0](
				$handler[1],
				$route,
				$this->request
			);
		$handle->route( $params );
	}
	
	/**
	 * Paths are sent in bare. Make them suitable for matching.
	 * 
	 * @param string $route URL path regex
	 */
	private function cleanRoute( $route ) {
		$regex	= str_replace( '.', '\.', $route );
		return '@^/' . $route . '/?$@i';
	}
	
	private function filter( $matches ) {
		return array_intersect_key(
			$matches, 
			array_flip( 
				array_filter( array_keys( $matches ), 'is_string' )
			)
		);
	}
}
