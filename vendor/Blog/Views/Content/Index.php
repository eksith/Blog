<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Index extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( 'default' );
	}
	
	public function index( Events\Event $event ) {
		$conds		= array();
		
		$title		= 'test heading';
		$vars		= 
		array(
			'heading'	=> $title,
			'page_title'	=> $title,
			'host_uri'	=> $_SERVER['SERVER_NAME'],
			'theme'		=> $this->getThemeDisplay()
		);
		
		
		$threads	= 
		array(
			array(
				'post_author'	=> 'author',
				'post_title'	=> 'test title',
				'date_u'	=> 'date_u',
				'date'		=> 'date',
				'post_id'	=> '12',
				'post_tags'	=> 'tags'
			)
		);
		$this->addState( 'threads', $threads );
		echo $this->sendView( 'forum.html', $conds, $vars );
	}
	
	public function archive( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
		
		echo 'archive view ';
	}
	
	public function viewPosts( Events\Event $event ) {
		$vars	= array(
			'csrf'		=> $event->get( 'searchpost_csrf' )
		);
		
		$event->set( 'search_form', $vars );
	}
}
