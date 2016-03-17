<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Delete extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_admin' ) );
	}
	
	public function deletingPost( Events\Event $event ) {
		$post	= $event->get( 'post' );
		$vars	= array(
			'csrf'		=> $event->get( 'deletepost_csrf' ),
			'post_title'	=> $post->title,
			'post_body'	=> $post->body
		);
		$lang	= array(
			'place_title',
			'place_body',
			'place_delete',
			'post_del_msg',
			'post_conf_del'
		);
		$vars	= array_merge( $vars, $this->fromLang( $event, $lang ) );
		$event->set( 'delete_form', $vars );
	}
}
