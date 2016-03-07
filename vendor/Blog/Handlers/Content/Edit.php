<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;
use Blog\Events;
use Blog\Models;

class Edit extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_ENCODED,
		'id'		=> \FILTER_VALIDATE_INT,
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
					'default'	=> POST_STATUS_OPEN,
					'min_range'	=> POST_STATUS_BURIED,
					'max_range'	=> POST_STATUS_FCLOSED
				)
			)
	);
	
	public function editingPost( Events\Event $event ) {
		# TODO
		$post			= new Model\Post();
		$post->id		= 32;
		$post->title		= 'This is a test title';
		$post->raw		= 
			'<p>Some HTML in <strong>here</strong>.</p>';
		$post->summary		= 'A short description';
		$post->published_at	= Models\Model::myTime( time() );
		
		$event->set( 'post', $post );
		$event->set(
			'editpost_csrf',
			$this->getCsrf( 'editpost', $event ) 
		);
	}
	
	public function editPost( Events\Event $event ) {
		# TODO
		$data = filter_input_array( \INPUT_POST, $this->filter );
		
		var_dump( $data );
	}
}
