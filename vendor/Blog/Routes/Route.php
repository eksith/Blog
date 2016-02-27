<?php

namespace Blog\Routes;
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
	
	/**
	 * Create a new route with a given request
	 */
	public function __construct( $name, Core\Request $request ) {
		$this->sender	= new Events\Dispatcher( $request );
		$this->event	= 
			new Events\Event( $name, $this->sender );
		
		$this->sender->attach( 'route', $this->event );
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
}
