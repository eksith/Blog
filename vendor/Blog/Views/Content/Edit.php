<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Edit extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_admin' ) );
	}
	
	public function editingPost( Events\Event $event ) {
		$post	= $event->get( 'post' );
		$vars	= array(
			'csrf'		=> $event->get( 'editpost_csrf' ),
			'post_title'	=> $post->title,
			'post_body'	=> $post->raw,
			'post_summary'	=> $post->summary,
			'post_pub'	=> $post->published_at
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
		$event->set( 'edit_form', $vars );
	}
}
