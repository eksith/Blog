<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Create extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_admin' ) );
	}
	
	public function creatingPost( Events\Event $event ) {
		$parent	= $event->get( 'id' );
		$parent	= empty( $parent ) ? 0 : $parent;
		
		$title	= $event->get( 'parent_title' );
		$title	= empty( $title ) ? '' : $title;
		
		$vars	= array(
			'csrf'		=> $event->get( 'newpost_csrf' ),
			'parent'	=> $parent,
			'parent_title'	=> $title
		);
		
		$lang	= array(
			'place_title',
			'place_body',
			'place_summary',
			'place_slug',
			'place_pub',
			'place_post',
			'place_edit',
			'up_drop',
			'select_files',
			'tab_source',
			'tab_preview',
			'tab_options',
			'tab_abstract',
			'tab_media'
		);
		
		$vars	= array_merge( $vars, $this->fromLang( $event, $lang ) );
		$event->set( 'create_form', $vars );
	}
}
