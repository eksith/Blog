<?php

namespace Blog\Events;

class Dispatcher {
	
	private $events = array();
	
	public function __construct() { }
	
	public function has( $scope, $event == null ) {
		if ( !isset( $this->events[$scope] ) ) {
			return false;
		}
		if ( empty( $event ) ) {
			return true;
		}
		if ( $this->events[$scope]->contains( $event ) ) {
			return true;
		}
		return false;
	}
	
	public function set( $scope, $name, $value ) {
		if ( !$this->has( $scope ) ) {
			return;
		}
		foreach ( $this->events[$scope] as $event ) {
			$event->set( $name, $value );
		}
	}
	
	public function add() {
		$args	= func_get_args();
		$scope	= array_shift( $args );
		
		if ( !$this->has( $scope ) ) {
			$this->events[$scope] = new \SplObjectStorage();
		}
		
		foreach( $args as $event ) {
			if ( !$this->has( $scope, $event ) ) {
				$this->events[$scope]->attach( $event );
			}
		}
	}
	
	public function remove() {
		$args	= func_get_args();
		$scope	= array_shift( $args );
		
		foreach( $args as $event ) {
			if ( $this->has( $scope, $event ) ) {
				$this->events[$scope]->detach( $event );
			}
		}
	}
	
	public function attach() {
		$args	= func_get_args();
		$scope	= array_shift( $args );
		
		if ( !$this->has( $scope ) ) {
			return;
		}
		foreach ( $this->events[$scope] as $event ) {
			foreach( $args as $handler ) {
				$event->attach( $handler );
			}
		}
	}
	
	public function detach() {
		$args	= func_get_args();
		$scope	= array_shift( $args );
		if ( !$this->has( $scope ) ) {
			return;
		}
		foreach ( $this->events[$scope] as $event ) {
			foreach( $args as $handler ) {
				$event->detach( $handler );
			}
		}
	}
	
	public function dispatch() {
		$args	= func_get_args();
		
		foreach( $args as $scope ) {
			$this->run( $scope );
		}
	}
	
	private hasScope( $scope ) {
		return isset( $this->events[$scope] );
	}
	
	private function run( $scope ) {
		if ( !isset( $this->events[$scope] ) ) {
			return;
		}
		
		foreach ( $this->events[$scope] as $event ) {
			$event->notify();
		}
	}
}
