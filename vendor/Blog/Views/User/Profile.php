<?php
namespace Blog\Views\User;
use Blog\Events;
use Blog\Views;

class Profile extends Views\View {
	
	public function profileView( Events\Event $event ) {
		$vars	= array(
			'profile_csrf'	=> $event->get( 'profile_csrf' ),
			'password_csrf' => $event->get( 'password_csrf' ),
			'delete_csrf'	=> $event->get( 'delete_csrf' )
		);
		
		$event->set( 'profile_form', $vars );
	}
}
