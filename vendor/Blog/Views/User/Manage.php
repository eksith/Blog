<?php
namespace Blog\Views\User;
use Blog\Events;
use Blog\Views;

class Manage extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_admin' ) );
	}
	
	public function handleEvent( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
	}
	
	public function loggingIn( Events\Event $event ) {
		$cond	= array();
		$lang	= $event->get( 'lang' );
		$vars	= array(
			'page_title'	=> 'Account login',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> 
				$lang->term( 'copyright', date( 'Y' ) )
		);
		
		$vars	= array_merge( $vars, $event->get( 'login_form' ) );
		$vars	= array_merge( $vars, $event->get( 'register_form' ) );
		echo $this->sendView( 'manage_login.html', $cond, $vars );
	}
	
	public function registering( Events\Event $event ) {
		$this->loggingIn( $event );
	}
	
	public function profileView( Events\Event $event ) {
		$cond	= array();
		$lang	= $event->get( 'lang' );
		$vars	= array(
			'page_title'	=> 'Profile',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> 
				$lang->term( 'copyright', date( 'Y' ) )
		);
		$vars	= array_merge( $vars, $event->get( 'profile_form' ) );
		echo $this->sendView( 'manage_profile.html', $cond, $vars );
	}
	
	public function deleteView( Events\Event $event ) {
		# TODO
		echo 'manage view delete';
	}
}
