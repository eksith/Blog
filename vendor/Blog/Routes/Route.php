<?php

namespace Blog\Routes;
use Blog\Messaging;
use Blog\Core;
use Blog\Events;
use Blog\Handlers;

/**
 * Blog route called by the core router 
 */
class Route extends Events\Pluggable {
	
	/**
	 * @var object Event dispatcher
	 */
	protected $sender;
	
	/**
	 * @var object Blog named event
	 */
	protected $event;
	
	protected static $secure_routes	= array();
	protected static $register_route;
	protected static $login_route;
	protected static $logout_route;
	
	/**
	 * Create a new route with a given request
	 */
	public function __construct(
		$name,
		$route,
		Messaging\ServerRequest $request,
		Events\Dispatcher $sender
	) {
		$this->sender	= $sender;
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
	 * Add multiple route handlers and trigger HandlerLoaded hook
	 */
	public function addHandlers( array $handlers ) {
		foreach ( $handlers as $handler ) {
			$this->add( $handler );
			$this->hook(
				'HandlerLoaded',
				$this,
				$handler,
				$this->event
			);
		}
	}
	
	/**
	 * Add multiple route views and trigger ViewLoaded hook
	 */
	public function addViews( array $views ) {
		foreach ( $views as $view ) {
			$this->add( $view );
			$this->hook(
				'ViewLoaded',
				$this,
				$view,
				$this->event
			);
		}
	}
	
	public function redirect( Events\Dispatcher $sender, $url ) {
		if ( !headers_sent() ) {
			$host	= $sender->getRequest()
					->getUri()
					->getRoot();
			
			$url	= rtrim( $url, '/' );
			header( 'Location: '. $host . '/' . $url );
		}
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
		
		# Authorization handler always gets added first
		$auth = new Core\Auth( $this->sender );
		$this->add( $auth );
	}
	
	protected function isSecureRoute() {
		$route = $this->sender
				->getRequest()
				->getUri()
				->getPath();
		
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
