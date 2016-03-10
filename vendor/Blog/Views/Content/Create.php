<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Create extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( 'admin' );
	}
	
	public function creatingPost( Events\Event $event ) {
		$parent	= $event->get('parent');
		$parent	= empty( $parent ) ? 0 : $parent;
		
		$title	= $event->get( 'parent_title' );
		$title	= empty( $title ) ? '' : $title;
		
		$vars	= array(
			'csrf'		=> $event->get( 'newpost_csrf' ),
			'parent'	=> $parent,
			'parent_title'	=> $title
		);
		$event->set( 'create_form', $vars );
	}
}
