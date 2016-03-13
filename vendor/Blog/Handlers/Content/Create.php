<?php

namespace Blog\Handlers\Content;
use Blog\Events;
use Blog\Models;

class Create extends ContentHandler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'parent'	=> \FILTER_VALIDATE_INT,
		'title'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'pubdate'	=> \FILTER_SANITIZE_STRING,
		'slug'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
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
					'max_range'	=> 99
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
		$post			= new Models\Post();
		$this->basePost( $data, $post );
		
		$post->parent_id	= empty( $data['parent'] ) ?
			0 : abs( ( int ) $data['parent'] );
		
		$post->user_id		= $event->get( 'user_id' );
		
		if ( $post->id ) {
			if ( $post->user_id ) {
				$this->redirect( 
					'/manage/edit/' . $post->id, 201 
				);
			} else {
				$this->redirect(
					'/read/' . $post->id, 201
				);
			}
		} else {
			# This is terrible
			# TODO Some error handling
			$this->redirect( '/' );
		}
	}
}
