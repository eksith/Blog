<?php

namespace Blog\Plugins\Security;
use Blog\Events;
use Blog\Views;
use Blog\Core;
use Blog\Handlers;
use Blog\Routes;
use Blog\Messaging;

class Plugin extends Events\Plugin {
	
	private $firewall;
	
	public function onConfigInit( 
		Core\Config $config, 
		Core\Crypto $crypto 
	) {}
	
	public function onDispatcherInit( 
		Events\Dispatcher $sender, 
		Messaging\ServerRequest $request,
		Core\Config $config,
		Core\Crypto $crypto
	) {
		$this->firewall	= new Sensor( $request, $config, $crypto );
		$this->firewall->run();
	}
	
	public function onRouteAdded( 
		Core\Router $router, $path, $route 
	) {}
	
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
