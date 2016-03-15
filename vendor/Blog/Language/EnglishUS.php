<?php<?php

namespace Blog\Language;

class EnglishUS {
	
	protected $defs = array(
		'new_post'	=> 'New post',
		'new_title'	=> 'Title',
		'new_body'	=> 'Body',
		'slug_place'	=> 'path-to-post',
		'pub_place'	=> 'publish date (now)',
		'summary'	=> 'Abstract',
		
		'up_drop'	=> 'Drop files',
		'files'		=> 'Select files',
		
		'edit_post'	=> 'Edit post',
		'delete_post'	=> 'Delete post',
		'tab_source'	=> 'source',
		'tab_preview'	=> 'preview',
		'tab_options'	=> 'options',
		'tab_abstract'	=> 'abstract',
		'tab_media'	=> 'media',
		
		'anon_author'	=> 'Anonymous',
		'author'	=> 'Author',
		'user'		=> 'User',
		
		'cmt_allow'	=> 'Allow comments',
		'cmt_user'	=> 'Named comments',
		'cmt_none'	=> 'No comments',
		
		'register'	=> 'Register',
		'remember'	=> 'Remember me',
		'login'		=> 'Login',
		
		'next_page'	=> 'Next',
		'prev_page'	=> 'Previous'
	);
	
	public function __construct( Events\Event $event ) {
		$event->set( 'lang', $this );
	}
}
