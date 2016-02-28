<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;
use Blog\Events;

class Index extends Handlers\Handler {
	
	public function index( Events\Event $event ) {
		echo 'home controller ';
	}
	
	public function archive( Events\Event $event ) {
		echo 'archive controller ';
	}
}
