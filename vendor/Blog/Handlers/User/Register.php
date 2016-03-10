<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;

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
		#TODO
		$data = filter_input_array( \INPUT_POST, $this->filter );
	}
}
