<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;
use Blog\Events;

class Index extends ContentHandler {
	
	public function index( Events\Event $event ) {
		# echo 'home controller ';
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
