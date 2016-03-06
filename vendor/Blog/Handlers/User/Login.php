<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;

class Login extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'username'	=> \FILTER_SANITIZE_ENCODED,
		'password'	=> \FILTER_UNSAFE_RAW,
		'status'	=> 
		array(
			'filter'	=> \FILTER_VALIDATE_INT,
			'flags'		=> \FILTER_REQUIRE_ARRAY,
			'options'	=> 
			array(
				'default'	=> 0,
				'min_range'	=> 0,
				'max_range'	=> 1
			)
		),
	);
	
	public function logginIn( Events\Event $event ) {
		$event->set( 
			'login_csrf', 
			$this->getCsrf( 'login', $event )
		);
	}
	
	public function login( Events\Event $event ) {
		# TODO
	}
}
