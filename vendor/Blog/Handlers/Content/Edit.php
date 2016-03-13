<?php

namespace Blog\Handlers\Content;
use Blog\Events;
use Blog\Models;

class Edit extends ContentHandler {
	
	private $filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'id'		=> \FILTER_VALIDATE_INT,
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
	
	public function editingPost( Events\Event $event ) {
		if ( empty( $uid = $event->get( 'user_id' ) ) ) {
			$this->redirect( '/', 403 );
		}
		
		if ( empty( $event->get( 'user_status' ) ) ) {
			$this->redirect( '/', 403 );
		}
		
		$post			= 
		Models\Post::find(
			'search'	=> 'id',
			'value'		=> $event->get( 'post_id' );
			'fields'	=> 'raw,summary'
		);
		
		if ( $this->editorStatus( $post->user_id, $uid, $event ) ) {
			$this->redirect( '/', 403 );
		}
		
		$event->set( 'post', $post );
		$event->set(
			'editpost_csrf',
			$this->getCsrf( 'editpost', $event ) 
		);
	}
	
	public function editPost( Events\Event $event ) {
		# TODO
		$data = filter_input_array( \INPUT_POST, $this->filter );
		$csrf = $this->verifyCsrf( 
				$data['csrf'], 'editpost', $event 
			);
		
		if ( $csrf ) {
			$this->save( $data );
		} else {
			$this->redirect( '/', 401 );
		}
	}
	
	private function findPost( $id ) {
		return 
		Models\Post::find( 
			array( 'search' => 'id', 'values' => $id ) 
		);
	}
	
	private function save( $data ) {
		$data['id']		= empty( $data['id'] ) ?
			0 : abs( ( int ) $data['id'] );
		
		if ( empty( $data['id'] ) ) {
			$this->redirect( '/', 401 );
		}
		
		$post			= 
			$this->findPost( $data['id'] );
		
		if ( empty( $post ) ) {
			# No post found matching that
			$this->redirect( '/', 304 );
		}
		
		$post->id		= $data['id'];
		$this->basePost( $data, $post );
		
		$post->save();
		$this->redirect( '/manage/edit/' . $post->id, 205 );
	}
}
