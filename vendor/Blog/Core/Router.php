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
		$path	= $this->cleanRoute( $path );
		$this->hook( 
			'RouteAdded', $this, $path, $route
		);
		static::$routes[$verb][$path] = $route;
		
		
	}
	
	public function route( array $markers ) {
		$verb	= strtolower( $this->request->getMethod() );
		
		$this->hook( 
			'Routing', 
			$this, 
			static::$routes, 
			$markers, 
			$verb
		);
		if ( !isset( self::$routes[$verb] ) ) {
			$this->hook( 
				'RouteVerbMissing', 
				$this, 
				static::$routes,
				$markers, 
				$verb 
			);
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
				'RouteNotFound', $this, $params, $route
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
			$params,
			$route, 
			$handler
		);
		
		$handle = new $handler[0](
				$handler[1],
				$route,
				$this->request,
				$this->sender
			);
		
		$this->hook( 
			'RouteInit', $this, $handle
		);
		$handle->route( $params );
		$this->hook( 
			'RouteSent', $this, $handle
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
