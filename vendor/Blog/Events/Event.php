<?php

namespace Blog\Events;

class Event extends Pluggable implements \SplSubject {
	
	private $rules;
	
	/**
	 * @var array Event data
	 */
	private $data		= array();
	
	/**
	 * @var string Triggering event name
	 */
	private	$name;
	
	/**
	 * @var object Collection of event handlers
	 */
	private $handlers;
	
	/**
	 * @var object Event dispatcher
	 */
	private $dispatcher;
	
	public function __construct( $name, Dispatcher $dispatcher ) {
		$this->name		= $name;
		$this->dispatcher	= $dispatcher;
		$this->handlers		= new \SplObjectStorage();
	}
	
	/**
	 * Return this event's dispatcher
	 */
	public function getDispatcher() {
		return $this->dispatcher;
	}
	
	/**
	 * Get current dispatcher's request
	 */
	public function getRequest() {
		return $this->dispatcher->getRequest();
	}
	
	/**
	 * Get current dispatcher's configuration settings
	 */
	public function getSetting( $setting ) {
		return $this->dispatcher
			->getConfig()
			->getSetting( $setting );
	}
	
	/**
	 * Check if a handler has already been attached
	 */
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
	
	/**
	 * Attach a listener handler to this event
	 */
	public function attach( \SplObserver $handler ) {
		if ( !$this->has( $handler ) ) {
			$this->handlers->attach( $handler );
		}
	}
	
	/**
	 * Remove a handler from this event if it is attached
	 */
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
