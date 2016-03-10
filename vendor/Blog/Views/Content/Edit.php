<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Edit extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( 'admin' );
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
		$event->set( 'edit_form', $vars );
	}
}
