<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;
use Blog\Models;

class Register extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'username'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'password'	=> \FILTER_UNSAFE_RAW,
		'email'		=> \FILTER_SANITIZE_EMAIL
	);
	
	public function logginIn( Events\Event $event ) {
		$this->registering( $event );
	}
	
	public function registering( Events\Event $event ) {
		$event->set(
			'register_csrf',
			$this->getCsrf( 'register', $event )
		);
	}
	
	public function register( Events\Event $event ) {
		$data = filter_input_array( \INPUT_POST, $this->filter );
		$csrf = $this->verifyCsrf( 
				$data['csrf'], 'register', $event 
			);
		if ( $csrf ) {
			$this->save( $data );
		} else {
			$this->redirect( '/', 401 );
		}
	}
	
	private function findUser( $usernma, $email ) {
		return 
		Models\User::find( 
			array( 
				'search'	=> 'user or email', 
				'values'	=> array( $username, $email )
			) 
		);
	}
	
	private function save( $data ) 
		$post			= 
		$this->findUser( $data['username'], $data['email'] );
		
		if ( !empty( $post ) ) {
			$this->redirect( '/', 401 );
		}
		
	}
}
