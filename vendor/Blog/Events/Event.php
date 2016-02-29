<?php

namespace Blog\Events;

class Event implements \SplSubject {
	
	private $rules;
	private	$name;
	private $handlers;
	private $dispatcher;
	
	public function __construct( $name, Dispatcher $dispatcher ) {
		$this->name		= $name;
		$this->dispatcher	= $dispatcher;
		$this->handlers		= new \SplObjectStorage();
	}
	
	public function getDispatcher() {
		return $this->dispatcher;
	}
	
	public function getRequest() {
		return $this->dispatcher->getRequest();
	}
	
	public function has( \SplObserver $handler ) {
		return $this->handlers->contains( $handler );
	}
	
	
	public function set( $key, $value ) {
		$this->data[$key] = $value;
	}
	
	public function get( $key ) {
		return isset( $this->data[$key] ) ?
			$this->data[$key] : null;
	}
	
	public function setRules( $rule ) {
		$this->rules[$this->name] = $rule;
	}
	
	public function getRules() {
		return isset( $this->rules ) ? $this->rules : array();
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function attach( \SplObserver $handler ) {
		if ( !$this->has( $handler ) ) {
			$this->handlers->attach( $handler );
		}
	}
	
	public function detach( \SplObserver $handler ) {
		if ( $this->has( $handler ) ) {
			$this->handlers->detach( $handler );
		}
	}
	
	public function notify() {
		foreach( $this->handlers as $handler ) {
			$handler->update( $this );
		}
	}
}