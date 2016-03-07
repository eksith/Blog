<?php
namespace Blog\Core;
use Blog\Events;
use Blog\Handlers;

class Auth extends Handlers\Handler  {
	
	private $auth;
	private $secure_routes;
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		/*
		$session = new BlogSession();
		\session_set_save_handler( $session, true );
		register_shutdown_function( 
			'session_write_close' 
		);
		*/
	}
	
	/**
	 * Give each event a set of rules as to which name can carry out which actions
	 */
	
	/**
	 * Handles all events
	 */
	public function handleEvent( Events\Event $event ) {
		$this->sessionCheck();
		$this->processEventRules(
			$event->getName(), 
			$event->getRules()
		);
		
		$event->set( 'session_id', session_id() );
	}
	
	private function processEventRules( $name, $rules ) {
		
	}
	
	/**
	 * ᕙ(⇀‸↼‶)ᕗ
	 * 
	 * @link https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 */
	private function authenticate() {
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
	
	/**
	 * First visit session initialization
	 */
	private function session( $reset = false ) {
		if ( 
			\session_status() === \PHP_SESSION_ACTIVE && 
			!$reset 
		) {
			return;
		}
		
		if ( \session_status() != \PHP_SESSION_ACTIVE ) {
			session_start();
		}
		if ( $reset ) {
			\session_regenerate_id( true );
			foreach ( array_keys( $_SESSION ) as $k ) {
				unset( $_SESSION[$k] );
			}
		}
	}
	
	/**
	 * Check session staleness
	 */
	private function sessionCheck( $reset = false ) {
		if ( !isset( $_SESSION['canary'] ) || $reset ) {
			$this->session( true );
			$this->sessionCanary();
			return;
		}
		
		if ( 
			strcmp( 
				$_SESSION['canary']['sig'], 
				$this->getSignature() 
			) !== 0 
		) {
			$this->session( true );
			$this->sessionCanary();
			return;
		}
		
		if ( 
			$_SESSION['canary']['exp'] < time() - 
			$this->getSetting( 'session_time' )
		) {
			$this->session();
			\session_regenerate_id( true );
			$this->sessionCanary();
		}
	}
	
	/**
	 * Session owner and staleness marker
	 * 
	 * @link https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
	 */
	private function sessionCanary() {
		$key	= $this->getSetting( 'visit_key' );
		$bytes	= $this->getCrypto()->bytes( $key );
		
		$_SESSION['canary'] = [
			'exp'	=> time(),
			'visit'	=> bin2hex( $bytes ),
			'sig'	=> $this->getSignature()
		];
	}
}
