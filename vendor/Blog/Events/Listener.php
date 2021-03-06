<?php

namespace Blog\Events;

class Listener extends Pluggable implements \SplObserver {
	
	protected $dispatcher;
	
	public function __construct( Dispatcher $dispatcher ) {
		$this->dispatcher	= $dispatcher;
	}
	
	public function getDispatcher() {
		return $this->dispatcher;
	}
	
	protected function getRequest() {
		return $this->dispatcher->getRequest();
	}
	
	protected function getSignature( $raw = false ) {
		return $this->dispatcher
				->getRequest()
				->getSignature( $raw );
	}
	
	protected function getConfig() {
		return $this->dispatcher
				->getConfig();
	}
	
	protected function getSetting( $setting ) {
		return $this->dispatcher
				->getConfig()
				->getSetting( $setting );
	}
	
	protected function getSettings( array $settings = array() ) {
		return $this->dispatcher
				->getConfig()
				->getSettings( $settings );
	}
	
	protected function getCrypto() {
		return $this->dispatcher->getCrypto();
	}
	
	public function update( \SplSubject $event ) {
		$name = $event->getName();
		
		if ( method_exists( $this, $name ) ) {
			$this->{$name}( $event );
		}
		
		/**
		 * Universal event handler no matter what took place
		 */
		if ( method_exists( $this, 'handleEvent' ) ) {
			$this->handleEvent( $event );
		}
	}
}
