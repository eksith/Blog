<?php

namespace Blog\Events;
use Blog\Core;
use Blog\Handlers;
use Blog\Routes;
use Blog\Messaging;

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
	abstract public function onConfigInit( 
		Core\Config $config, 
		Core\Crypto $crypto 
	);
	
	/**
	 * Event dispatcher initializing
	 */
	abstract public function onDispatcherInit( 
		Dispatcher $sender, 
		Messaging\ServerRequest $request, 
		Core\Config $config, 
		Core\Crypto $crypto
	);
	
	/**
	 * Navigation route, path, HTTP method verb
	 */
	abstract public function onRouteAdded( 
		Core\Router $router, $path, $route 
	);
	
	/**
	 * Route parsing has started
	 */
	abstract public function onRouting( 
		Core\Router $router, 
		array $routes,
		array $markers,
		$verb
	);
	
	/**
	 * Sent route/verb combination missing
	 */
	abstract public function onRouteVerbMissing( 
		Core\Router $router, 
		array $routes,
		array $markers,
		$verb
	);
	
	/**
	 * Route with matching path/verb found
	 */
	abstract public function onRouteFound( 
		Core\Router $router, 
		array $params,
		$route,
		$handler
	);
	
	/**
	 * No routes specified for this path
	 */
	abstract public function onRouteNotFound( 
		Core\Router $router, 
		array $params,
		$route 
	);
	
	/**
	 * A route handler has been initialized
	 */
	abstract public function onRouteInit( 
		Core\Router $router, 
		Routes\Route $handler
	);
	
	/**
	 * Route handler created and sent
	 */
	abstract public function onRouteSent( 
		Core\Router $router, 
		Routes\Route $handler
	);
	
	/**
	 * Visitor is being sent to another location
	 */
	abstract public function onRedirect(
		Handlers\Handler $handler, 
		$base, 
		$code, 
		$status 
	);
	
	/**
	 * Inside the route, a new handler has been loaded
	 */
	abstract public function onHandlerLoaded(
		Routes\Route $route,
		Handlers\Handler $handler,
		Events\Event $event
	);
	
	/**
	 * Inside the route, a new view has been loaded
	 */
	abstract public function onViewLoaded(
		Routes\Route $route,
		Views\View $handler,
		Events\Event $event
	);
	
	/**
	 * In the view, the template has been rendered with conditions
	 * and display variables
	 */
	abstract public function onViewRendered(
		Views\View $handler,
		Events\Event $event,
		array $conds,
		array $vars
	);
}
