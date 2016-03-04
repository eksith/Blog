<?php

namespace Blog\Routes;
use Blog\Handlers;
use Blog\Views;

class UserRoute extends Route {
	
	public function route( array $map = array() ) {
		parent::route( $map );
		switch( $this->event->getName() ) {
			case 'loggingIn':
				$this->loginView( $this->sender );
				break;
				
			case 'login':
				$this->login( $this->sender );
				break;
				
			case 'registering':
				$this->registering( $this->sender );
				break;
				
			case 'register':
				$this->register( $this->sender );
				break;
				
			case 'profileView':
				$this->profileView( $this->sender );
				break;
				
			case 'profileChanged':
				$this->profile( $this->sender );
				break;
		}
		
		$this->sender->dispatch( 'route' );
	}
	
	private function loginView( $sender ) {
		$this->add( new Handlers\User\Login( $sender ) );
		$this->add( new Views\User\Login( $sender ) );
	}
	
	private function login( $sender ) {
		$this->add( new Handlers\User\Login( $sender ) );
	}
	
	private function registering( $sender ) {
		$this->add( new Handlers\User\Register( $sender ) );
		$this->add( new Views\User\Register( $sender ) );
	}
	
	private function register( $sender ) {
		$this->add( new Handlers\User\Register( $sender ) );
	}
	
	private function profileView( $sender ) {
		$this->add( new Handlers\User\Profile( $sender ) );
		$this->add( new Views\User\Profile( $sender ) );
	}
	
	private function profileChanged( $sender ) {
		$this->add( new Handlers\User\Profile( $sender ) );
	}
}
