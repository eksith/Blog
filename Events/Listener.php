<?php

namespace Blog\Events;

class Listener implements \SplObserver {
	
	public function update( \SplSubject $event ) {
		$name		= $event->getName();
		
		if ( method_exists( $this, $name ) ) {
			call_user_func_array( 
				array( $this, $name ), array( $event ) 
			);
		}
	}
}
