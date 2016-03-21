<?php

namespace Blog\Plugins\Example;
use Blog\Events;
use Blog\Views;
use Blog\Core;
use Blog\Handlers;
use Blog\Routes;
use Blog\Messaging;

/**
 * An example plugin that doesn't really do anything
 */
class Plugin extends Events\Plugin {
	
	public function function onConfigInit( 
		Core\Config $config, 
		Core\Crypto $crypto 
	) {
		# Do something with the configuration class settings
	}
	
	public function onDispatcherInit( 
		Events\Dispatcher $sender, 
		Messaging\ServerRequest $request,
		Core\Config $config,
		Core\Crypto $crypto
	) {
		# Do something on initialization of event dispatcher
	}
	
	public function onRouteAdded( 
		Core\Router $router, $path, $route 
	) {
		# Do something with the added route
	}
	
	public function onRouting( 
		Core\Router $router, 
		array $routes,
		array $markers,
		$verb
	) {}
	
	public function onRouteVerbMissing( 
		Core\Router $router, 
		array $routes,
		array $markers,
		$verb
	) {}
	
	public function onRouteFound( 
		Core\Router $router, 
		array $params,
		$route,
		$handler
	) {}
	
	public function onRouteNotFound( 
		Core\Router $router, 
		array $params,
		$route 
	) {}
	
	public function onRouteInit( 
		Core\Router $router, 
		Routes\Route $handler
	) {}
	
	public function onRouteSent( 
		Core\Router $router, 
		Routes\Route $handler
	) {}
	
	public function onRedirect(
		Handlers\Handler $handler, 
		$base, 
		$code, 
		$status 
	) {}
	
	public function onHandlerLoaded(
		Routes\Route $route,
		Handlers\Handler $handler,
		Events\Event $event
	) {}
	
	public function onViewLoaded(
		Routes\Route $route,
		Views\View $handler,
		Events\Event $event
	) {}
	
	public function onViewRendered(
		Views\View $handler,
		Events\Event $event,
		array $conds,
		array $vars
	) {}
}
