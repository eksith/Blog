<?php

namespace Blog\Events;

class Event implements \SplSubject {
	
	private $properties	= array();
	private $block		= false;
	private $listeners;
	private $name;
	
	public function __construct( $name ) {
		$this->name		= $name;
		$this->listeners	= new \SplObjectStorage();
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function set( $property, $value = null ) {
		$this->properties[$property] = $value;
	}
	
	public function get( $property ) {
		return isset( $this->properties[$property] ) ? 
			$this->properties[$property] : null;
	}
	
	public function attach( \SplObserver $listener ) {
		if ( $this->block ) {
			return;
		}
		if ( !$this->listeners->contains( $listener ) ) {
			$this->listeners->attach( $listener );
		}
	}
	
	public function detach( \SplObserver $lisetner ) {
		if ( $this->listeners->contains( $listener ) ) {
			$this->listeners->detach( $listener );
		}
	}
	
	public function notify() {
		foreach ( $this->listeners as $listener ) {
			$listener->update( $this );
		}
	}
}
