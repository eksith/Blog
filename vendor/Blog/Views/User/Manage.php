<?php
namespace Blog\Views\User;
use Blog\Events;
use Blog\Views;

class Manage extends Views\View {
	
	public function loggingIn( Events\Event $event ) {
		# TODO
		echo 'manage view login';
	}
	
	public function registering( Events\Event $event ) {
		# TODO
		echo 'manage view registering';
	}
	
	public function profileView( Events\Event $event ) {
		# TODO
		echo 'manage view profile';
	}
	
	public function deleteView( Events\Event $event ) {
		# TODO
		echo 'manage view delete';
	}
}
