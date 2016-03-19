<?php

namespace Blog\Core;
use Blog\Messaging;
use Blog\Events;

class Router extends Events\Pluggable {
	
	private $request;
	private $sender;
	
	private static $routes	= array();
	
	public function __construct(	
		Messaging\ServerRequest $request,
		Events\Dispatcher $sender
	) {
		$this->request	= $request;
		$this->sender	= $sender;
	}
	
	public function add( $verb, $path, $route ) {
		if ( !isset( self::$routes[$verb] ) ) {
			self::$routes[$verb] = array();
		}
		static::$routes[$verb][$this->cleanRoute( $path )] = 
			$route;
		
		$this->hook( 
			'RouteAdded', 
			$this, 
			array( static::$routes ) 
		);
	}
	
	public function route( array $markers ) {
		$this->hook( 'Routing', $this, array( static::$routes ) );
		
		$verb	= strtolower( $this->request->getMethod() );
		if ( !isset( self::$routes[$verb] ) ) {
			$this->hook( 
				'RouteVerbMissing', 
				$this, 
				array( static::$routes, $verb, $markers );
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
		
		if ( !$found ) {
			$this->hook( 
				'RouteNotFound', 
				$this, 
				array( $route, $params )
			);
		}
	}
	
	private function send( $params, $route, $handler ) {
		$params = $this->filter( $params );
		
		if ( count( $params ) > 0 ) {
			// Clean parameters
			//array_shift( $params );
		}
		$this->hook( 
			'RouteFound', 
			$this, 
			array( $route, $params )
		);
		
		$handle = new $handler[0](
				$handler[1],
				$route,
				$this->request,
				$this->sender
			);
		$handle->route( $params );
		$this->hook( 
			'RouteSent', 
			$this, 
			array( $handle, $this->request, $this->sender )
		);
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
				array_filter(
					array_keys( $matches ), 
					'is_string' 
				)
			)
		);
	}
}
