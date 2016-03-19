<?php

namespace Blog\Events;
use Blog\Core;
use Blog\Messaging;

class Dispatcher extends Pluggable {
	
	private static $queue;
	private $crypto;
	private $events		= array();
	private $config;
	private $request;
	
	public function __construct(
		Messaging\ServerRequest $request,
		Core\Config $config, 
		Core\Crypto $crypto
	) {
		$this->hook( 
			'DispatcherInit', $this, $request, $config, $crypto 
		);
		$this->request	= $request;
		$this->config	= $config;
		$this->crypto	= $crypto;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function getCrypto() {
		return $this->crypto;
	}
	
	public function has( $name, Event $event ) {
		if ( !isset( $this->events[$name] ) ) {
			return false;
		}
		
		if ( empty( $event ) ) {
			return true;
		}
		
		return $this->events[$name]->contains( $event );
	}
	
	public function set( $name, $value ) {
		if ( !$this->has( $name ) ) {
			return;
		}
		foreach ( $this->events[$name] as $event ) {
			$event->set( $name, $value );
		}
	}
	
	public function add() {
		$args	= func_get_args();
		$name	= array_shift( $args );
		
		if ( !$this->has( $name ) ) {
			$this->events[$name] = new \SplObjectStorage();
		}
		
		foreach( $args as $event ) {
			$this->attach( $name, $event );
		}
	}
	
	public function attach( $name, Event $event ) {
		if ( !isset( $this->events[$name] ) ) {
			$this->events[$name]	= 
				new \SplObjectStorage();
		}
		if ( !$this->has( $name, $event ) ) {
			$this->events[$name]->attach( $event );
		}
	}
	
	public function detach( $name, Event $event ) {
		if ( isset( $this->events[$name] ) ) {
			if ( $this->has( $name, $event ) ) {
				$this->events[$name]->detach( $event );
			}
		}
	}
	
	public function dispatch( $name ) {
		if ( !isset( $this->events[$name] ) ) {
			return;
		}
		foreach( $this->events[$name] as $event ) {
			$event->notify();
		}
	}
	
	public function defer() {
		if ( !isset( static::$queue ) ) {
			static::$queue = new Queue();
		}
		
		static::$queue->schedule( func_get_args() );
	}
}
