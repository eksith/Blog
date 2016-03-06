<?php

namespace Blog\Handlers\User;
use Blog\Handlers;
use Blog\Events;

class Delete extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'delete'	=> 
		array(
			'filter'	=> \FILTER_VALIDATE_INT,
			'flags'		=> \FILTER_REQUIRE_ARRAY,
			'options'	=> 
			array(
				'default'	=> 0,
				'min_range'	=> 0,
				'max_range'	=> 1
			)
		),
		'conf_delete'	=> 
		array(
			'filter'	=> \FILTER_VALIDATE_INT,
			'flags'		=> \FILTER_REQUIRE_ARRAY,
			'options'	=> 
			array(
				'default'	=> 0,
				'min_range'	=> 0,
				'max_range'	=> 1
			)
		)
	);
	
	public function profileView( Events\Event $event ) {
		$event->set(
			'delete_csrf',
			$this->getCsrf( 'delete', $event )
		);
	}
	
	public function delete( Events\Event $event ) {
		# TODO
	}
}
