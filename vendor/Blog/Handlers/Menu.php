<?php

namespace Blog\Handlers;
use Blog\Handlers;
use Blog\Events;

/**
 * Menu and navigation handler
 */
class Menu extends Handler {
	
	public function handlEvent( Events\Event $event ) {
		switch( $event->getName() ) {
			case '':
				$event->add( 
					'main_menu', 
					$this->manageMenu()
				);
				break;
			default:
				$event->add( 
					'main_menu', 
					$this->publicMenu()
				);
		}
	}
	
	private function publicMenu( Events\Event $event ) {
		return array(
			'Home'		=> array( '/', true ),
			'Account'	=> array( '/account', false )
		);
	}
	
	private function manageMenu( Events\Event $event ) {
		return array(
			'Home'		=> array( '/', false ),
			'New'		=> array( '/new', true ),
			'Posts'		=> array( '/posts', false ),
			'Users'		=> array( '/users', false ),
			'Profile'	=> array( '/profile', false ),
			'Account'	=> array( '/account', false )
		);
	}
}
