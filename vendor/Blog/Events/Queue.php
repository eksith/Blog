<?php

namespace Blog\Events;

final class Queue {
	
	private $actions = array();
	
	public function __construct() {
		register_shutdown_function( array( $this, 'shutdown' ) );
	}
	
	protected function schedule() {
		$call = func_get_args();
		if ( empty( $call ) ) {
			return;
		}
		
		if ( !is_callable( $call[0] ) ) {
			return;
		}
		$this->actions[] = $call;
	}
	
	public function shutdown() {
		foreach ( $this->actions as $args ) {
			$call = array_shift( $args );
			call_user_func_array( $call, $args );
		}
	}
	
}

