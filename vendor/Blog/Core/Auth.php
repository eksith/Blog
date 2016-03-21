<?php
namespace Blog\Core;
use Blog\Events;
use Blog\Handlers;

class Auth extends Handlers\Handler  {
	
	private $auth;
	private $secure_routes;
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
	}
	
	/**
	 * Give each event a set of rules as to which name can carry out which actions
	 */
	
	/**
	 * Handles all events
	 */
	public function handleEvent( Events\Event $event ) {
		$this->processEventRules(
			$event->getName(), 
			$event->getRules()
		);
		
		if ( !isset( $_SESSION ) ) {
			return;
		}
		$event->set( 'session_id', $_SESSION['canary']['visit'] );
		$event->set( 'user_id', 0 );
	}
	
	/**
	 * ᕙ(⇀‸↼‶)ᕗ
	 * 
	 * @link https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 */
	private function authenticate() {
		if ( !isset( $_SESSION ) ) {
			return;
		}
		if ( empty( $_SESSION['user'] ) ) {
			$this->auth = $this->getAuthCookie();
		} else {
			$this->auth = $_SESSION['user'];
		}
	}
	
	/**
	 * Authenticated cookie authorization
	 */
	private function getAuthCookie() {
		$auth = 
		$this->getCookie( 
			$this->getSetting( 'cookie_name' ), 
			$this->getSignature( true ) 
		);
		if ( empty( $auth ) || false === $auth ) {
			return null;
		}
	}
	
	private function processEventRules( $name, $rules ) {
		# TODO
	}
}
