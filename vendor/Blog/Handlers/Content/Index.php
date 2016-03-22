<?php

namespace Blog\Handlers\Content;
use Blog\Models;
use Blog\Handlers;
use Blog\Events;

class Index extends ContentHandler {
	
	public function index( Events\Event $event ) {
		$page		= $event->get( 'page' );
		$page		= empty( $page ) ? 1 : $page;
		
		$posts		= 
		Models\Post::find( array( 
			'search'	=> 'all',
			'values'	=> 'all',
			'page'		=> $page,
			'fields'	=> 'summary'
		) );
		
		$event->set( 'posts', $posts );
	}
	
	public function archive( Events\Event $event ) {
		# echo 'archive controller ';
	}
	
	public function viewPosts( Events\Event $event ) {
		$event->set(
			'searchpost_csrf',
			$this->getCsrf( 'searchpost', $event ) 
		);
	}
	
	public function searchPost( Events\Event $event ) {
		
	}
}
