<?php

namespace Blog\Handlers;
use Blog\Events;

class Errors extends Handler {
	
	public function handleEvent( Events\Event $event ) {
		# TODO
	}
	
	public function error404( Events\Event $event ) {
		$this->finish( true, 'Couldn\'t find the file you\'re looking for' );
	}
	
	public function error403( Events\Event $event ) {
		$this->finish( true, 'Permission denied' );
	}
	
	public function error401( Events\Event $event ) {
		$this->finish( true, 'Authentication failed' );
	}
}
