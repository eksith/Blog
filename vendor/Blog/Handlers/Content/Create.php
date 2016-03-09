<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;
use Blog\Events;
use Blog\Models;

class Create extends Handlers\Handler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'parent'	=> \FILTER_VALIDATE_INT,
		'title'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'publish'	=> \FILTER_SANITIZE_STRING,
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
		$data = filter_input_array( \INPUT_POST, $this->filter );
		$csrf = $this->verifyCsrf( 
				$data['csrf'], 'newpost', $event 
			);
		
		if ( $csrf ) {
			$this->save( $data, $event );
		} else {
			$this->redirect( '/', 401 );
		}
	}
	
	private function save( $data, Events\Event $event ) {
		$filter			= $this->getHtmlFilter();
		$post			= new Models\Post();
		
		$post->title		= empty( $data['title'] ) ?
			'Untitled' : $data['title'];
		
		$post->raw		= empty( $data['body'] ) ? 
			'' : $data['body'];
		
		$post->summary		= empty( $data['summary'] ) ? 
			'' : $filter->clean( $data['summary'], false );
		
		$post->body		= $filter->clean( $post->raw );
		$post->plain		= strip_tags( $post->body );
		$post->parent_id	= empty( $data['parent'] ) ?
			0 : abs( ( int ) $data['parent'] );
		
		$post->user_id		= $event->get( 'user_id' );
		$post->save();
		if ( $post->id ) {
			$this->redirect( '/manage/edit/' . $post->id, 201 );
		} else {
			# This is terrible
			# TODO Some error handling
			$this->redirect( '/' );
		}
	}
}
