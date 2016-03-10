<?php
namespace Blog\Views\User;
use Blog\Events;
use Blog\Views;

class Register extends Views\View {
	
	public function loggingIn( Events\Event $event ) {
		$this->registering( $event );
	}
	
	public function registering( Events\Event $event ) {
		$vars	= array(
			'register_csrf'	=> $event->get( 'register_csrf' )
		);
		
		$event->set( 'register_form', $vars );
	}
}
