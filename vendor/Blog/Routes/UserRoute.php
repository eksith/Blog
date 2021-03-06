<?php

namespace Blog\Routes;
use Blog\Language;
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
				
			case 'logout':
				$this->redirect( 
					$this->sender, 
					static::$login_route
				);
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
				
			case 'passChanged':
				$this->passChanged( $this->sender );
				break;
				
			case 'deleteView':
				$this->deleteView( $this->sender );
				break;
				
			case 'delete':
				$this->delete( $this->sender );
				break;
		}
		
		$this->sender->dispatch( 'route' );
	}
	
	private function loginView( $sender ) {
		$this->addHandlers( array(
			new Handlers\User\Login( $sender ),
			new Handlers\User\Register( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		$this->addViews( array(
			new Views\User\Login( $sender ),
			new Views\User\Register( $sender ),
			new Views\User\Manage( $sender )
		) );
	}
	
	private function login( $sender ) {
		$this->add( new Handlers\User\Login( $sender ) );
	}
	
	private function registering( $sender ) {
		$this->addHandlers( array(
			new Handlers\User\Register( $sender ),
			new Handlers\Menu( $sender ),
		) );
		
		$this->add( new Language\Locale( $sender ) );
		$this->addViews( array(
			new Views\User\Register( $sender ),
			new Views\User\Manage( $sender )
		) );
	}
	
	private function register( $sender ) {
		$this->add( new Handlers\User\Register( $sender ) );
	}
	
	private function profileView( $sender ) 
		$this->addHandlers( array(
			new Handlers\User\Profile( $sender ),
			new Handlers\User\ChangePass( $sender ),
			new Handlers\User\Delete( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		$this->addViews( array(
			new Views\User\Profile( $sender ),
			new Views\User\Manage( $sender )
		) );
	}
	
	private function profileChanged( $sender ) {
		$this->add( new Handlers\User\Profile( $sender ) );
	}
	
	private function passChanged( $sender ) {
		$this->add( new Handlers\User\ChangePass( $sender ) );
	}
	
	private function deleteView( $sender ) {
		$this->addHandlers( array(
			new Handlers\User\Delete( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		$this->addViews( array(
			new Views\User\Delete( $sender ),
			new Views\User\Manage( $sender )
		) );
	}
	
	private function delete( $sender ) {
		$this->add( new Handlers\User\Delete( $sender ) );
	}
}
