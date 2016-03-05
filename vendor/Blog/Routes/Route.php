<?php

namespace Blog\Routes;
use Blog\Messaging;
use Blog\Core;
use Blog\Events;
use Blog\Handlers;

/**
 * Blog route called by the core router 
 */
class Route {
	
	/**
	 * @var object Event dispatcher
	 */
	protected $sender;
	
	/**
	 * @var object Blog named event
	 */
	protected $event;
	
	private static $secure_routes	= array();
	private static $register_route;
	private static $login_route;
	private static $logout_route;
	
	/**
	 * Create a new route with a given request
	 */
	public function __construct(
		$name,
		$route,
		Messaging\ServerRequest $request
	) {
		$this->sender	= new Events\Dispatcher( $request );
		$this->event	= 
			new Events\Event( $name, $this->sender );
		
		$this->sender->attach( 'route', $this->event );
	}
	
	public static function addSecureRoute( $route ) {
		static::$secure_routes[]	= $route;
		static::$secure_routes	= 
			array_unique( static::$secure_routes );
	}
	
	public static function setRegisterRoute( $route ) {
		static::$register_route	= $route;
	}
	
	public static function setLoginRoute( $route ) {
		static::$login_route	= $route;
	}
	
	public static function setLogoutRoute( $route ) {
		static::$logout_route	= $route;
	}
	
	/**
	 * Add a route handler
	 */
	public function add( Handlers\Handler $handler ) {
		$this->event->attach( $handler );
	}
	
	/**
	 * Apply event variable map (comes route from path)
	 */
	public function route( array $map = array() ) {
		foreach ( $map as $k => $v ) {
			if ( empty( $k ) ) {
				continue;
			}
			$this->event->set( $k, $v );
		}
		
		// Authorization handler always gets added first
		$this->add( new Core\Auth( $this->sender ) );
	}
	
	private function isSecureRoute( $route ) {
		foreach ( static::$secure_routes as $secure ) {
			$len	= mb_strlen( $secure, '8bit' );
			if ( 0 === strncasecmp( 
				$secure, $route, $len 
			) ) {
				return true;
			}
		}
		
		return false;
	}
}
