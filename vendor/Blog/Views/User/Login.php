<?php
namespace Blog\Views\User;
use Blog\Events;
use Blog\Views;

class Login extends Views\View {
	
	public function loggingIn( Events\Event $event ) {
		$vars	= array(
			'login_csrf'	=> $event->get( 'login_csrf' )
		);
		
		$event->set( 'login_form', $vars );
	}
}
