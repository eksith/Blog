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
			case 'creatingPost':
				$event->add( 
					'main_menu', 
					$this->manageMenu( 'New' )
				);
				break;
			
			case 'viewPosts':
			case 'editingPost':
			case 'deletingPost':
				$event->add( 
					'main_menu', 
					$this->manageMenu( 'Posts' )
				);
				break;
				
			case 'profileView':
				$event->add( 
					'main_menu', 
					$this->manageMenu( 'Profile' )
				);
				break;
				
			case 'userView':
			case 'deleteView':
				$event->add( 
					'main_menu', 
					$this->manageMenu( 'Users' )
				);
				break;
				
			case 'loggingIn':
			case 'registering':
				$event->add( 
					'main_menu', 
					$this->manageMenu( 'Account' )
				);
				break;
			
			default:
				$event->add( 
					'main_menu', 
					$this->publicMenu( 'Home' );
				);
		}
	}
	
	private function publicMenu( $name ) {
		return array(
			'Home'		=> '/',
			'Account'	=> '/account'
		);
		return $this->build( $paths, $name );
	}
	
	private function manageMenu( $name ) {
		$paths = 
		array(
			'Home'		=> '/',
			'New'		=> '/new',
			'Posts'		=> '/posts',
			'Users'		=> '/users',
			'Profile'	=> '/profile',
			'Account'	=> '/account'
		);
		return $this->build( $paths, $name );
	}
	
	private function build( $paths, $name ) {
		
		$menu = array();
		
		foreach ( $paths as $k => $v ) {
			$menu[$k] = 
			array( 
				$v, ( $name == $k ) ? true : false 
			);
		}
		
		return $menu;
	}
}
