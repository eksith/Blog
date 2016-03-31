<?php

namespace Blog\Routes;
use Blog\Core;
use Blog\Handlers;

/**
 * CAPTCHA Handling routes
 */
class CaptchaRoute extends Route {
	
	public function route( array $map = array() ) {
		parent::route( $map );
		switch( $this->event->getName() ) {
			case 'generate':
			case 'validate'
				$this->add( 
					new Handlers\Captcha( $this->sender ) 
				);
				break;
				
		}
		
		$this->sender->dispatch( 'route' );
	}
}
