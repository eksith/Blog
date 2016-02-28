<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Index extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( 'default' );
	}
	
	public function index( Events\Event $event ) {
		echo 'index view ';
	}
	
	public function archive( Events\Event $event ) {
		echo 'archive view ';
	}
}
