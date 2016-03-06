<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;

class ChangePass extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'oldpassword'	=> \FILTER_UNSAFE_RAW,
		'newpassword'	=> \FILTER_UNSAFE_RAW
	);
	
	public function profileView( Events\Event $event ) {
		$event->set(
			'password_csrf',
			$this->getCsrf( 'password', $event ) 
		);
	}
	
	public function passChanged( Events\Event $event ) {
		# TODO
	}
}
