<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;
use Blog\Events;

class Create extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'parent'	=> \FILTER_VALIDATE_INT,
		'title'		=> \FILTER_SANITIZE_ENCODED,
		'publish'	=> \FILTER_SANITIZE_ENCODED,
		'summary'	=> \FILTER_UNSAFE_RAW,
		'body'		=> \FILTER_UNSAFE_RAW,
		'status'	=> 
			array(
				'filter'	=> \FILTER_VALIDATE_INT,
				'flags'		=> \FILTER_REQUIRE_ARRAY,
				'options'	=> 
				array(
					'default'	=> -1,
					'min_range'	=> -1,
					'max_range'	=> 10
				)
			)
	);
	
	public function creatingPost( Events\Event $event ) {
		$event->set(
			'newpost_csrf',
			$this->getCsrf( 'newpost', $event ) 
		);
	}
	
	public function createPost( Events\Event $event ) {
		# TODO
		$data = filter_input_array( \INPUT_POST, $this->filter );
		
		var_dump( $data );
	}
}
