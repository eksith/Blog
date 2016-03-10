<?php
namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Front extends Views\View {
	
	public function handleEvent( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
	}
	
	public function archive( Events\Event $event ) {
		# TODO
		echo 'archive view front';
	}
	
	public function read( Events\Event $event ) {
		# TODO
		echo 'read view front';
	}
}
