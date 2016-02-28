<?php

namespace Blog\Routes;
use Blog\Handlers;
use Blog\Views;

class HomeRoute extends Route {
	
	public function route( array $map = array() ) {
		parent::route( $map );
		
		$this->add( new Handlers\Content\Index( $this->sender ) );
		$this->add( new Views\Content\Index( $this->sender ) );
		
		$this->sender->dispatch( 'route' );
	}
}
