<?php

namespace Blog\Handlers;
use Blog\Handlers;
use Blog\Events;

/**
 * Menu and navigation handler
 */
class Menu extends Handler {
	
	public function handleEvent( Events\Event $event ) {
		switch( $event->getName() ) {
			case 'creatingPost':
				$event->set( 
					'main_menu', 
					$this->manageMenu( 'New' )
				);
				break;
			
			case 'viewPosts':
			case 'editingPost':
			case 'deletingPost':
				$event->set( 
					'main_menu', 
					$this->manageMenu( 'Posts' )
				);
				break;
				
			case 'profileView':
				$event->set( 
					'main_menu', 
					$this->manageMenu( 'Profile' )
				);
				break;
				
			case 'userView':
			case 'deleteView':
				$event->set( 
					'main_menu', 
					$this->manageMenu( 'Users' )
				);
				break;
				
			case 'loggingIn':
			case 'registering':
				$event->set( 
					'main_menu', 
					$this->manageMenu( 'Account' )
				);
				break;
			
			default:
				$event->set( 
					'main_menu', 
					$this->publicMenu( 'Home' )
				);
				
				$event->set(
					'side_menu',
					$this->sidebar( $event )
				);
		}
	}
	
	private function sidebar( Events\Event $event ) {
		$side = $event->get( 'sidebar' );
		if ( empty( $side ) ) {
			return array();
		}
		
		return $this->build( $side, '' );
	}
	
	private function publicMenu( $name ) {
		$paths	= $this->getSetting( 'main_navigation' );
		
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
			$sel		= ( $name == $k ) ? true : false;
			$menu[$k]	= 
			array(
				'label'		=> $k,
				'path'		=> $v,
				'selected'	=> $sel
			);
		}
		
		return $menu;
	}
}
