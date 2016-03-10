<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;

class Profile extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'username'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'password'	=> \FILTER_UNSAFE_RAW,
		'email'		=> \FILTER_VALIDATE_EMAIL,
		'bio'		=> \FILTER_UNSAFE_RAW,
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
		)
	);
	
	public function profileView( Events\Event $event ) {
		$event->set(
			'profile_csrf',
			$this->getCsrf( 'profile', $event ) 
		);
	}
	
	public function profileChanged( Events\Event $event ) {
		# TODO
		echo 'Profile changed';
	}
}
