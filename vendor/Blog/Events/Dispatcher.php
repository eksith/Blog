<?php

namespace Blog\Core\Events;
use Blog\Models;
use Blog\Core;

class Dispatcher {
	
	private static $queue;
	private static $crypto;
	private $events		= array();
	private $config;
	private $request;
	
	public function __construct( Core\Request $request ) {
		$this->request	= $request;
		$this->config	= new Core\Config( $this->crypto() );
		
		Models\Model::setConfig( $this->config );
		Models\Model::setCrypto( $this->crypto() );
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getConfig() {
		return $this->config;
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
	
	public function set( $name, $name, $value ) {
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
	
	public function crypto() {
		if ( !isset( self::$crypto ) ) {
			self::$crypto = new Core\Crypto();
		}
		
		return self::$crypto;
	}
	
	public function defer() {
		if ( !isset( self::$queue ) ) {
			self::$queue = new Queue();
		}
		
		self::$queue->schedule( func_get_args() );
	}
}

