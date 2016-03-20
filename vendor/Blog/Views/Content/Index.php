<?php

namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Index extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_default' ) );
	}
	
	public function index( Events\Event $event ) {		
		$title		= $this->getSetting( 'blog_name' );
		$tagline	= $this->getSetting( 'blog_tagline' );
		$uri		= $this->getRequest()
					->getUri()
					->getRoot();
		
		$vars		= 
		array(
			'heading'	=> $tagline,
			'page_title'	=> $title,
			'host_uri'	=> $uri,
			'theme'		=> $this->getThemeDisplay()
		);
		
		$posts		= $event->get( 'posts' );
		$conds		= 
		array(
			'post_count'	=> count( $posts )
		);
		
		$parsed		= 
		$this->mapObjToPlace( $this->post_map, $posts );
		
		$this->menuBuilder( $event, $conds );
		$this->addState( 'posts', $parsed );
		echo $this->sendView( 'index.html', $conds, $vars );
	}
	
	public function archive( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
		echo 'archive view ';
	}
	
	public function viewPosts( Events\Event $event ) {
		$vars	= array(
			'csrf'		=> $event->get( 'searchpost_csrf' )
		);
		
		$this->menuBuilder( $event, $conds );
		$event->set( 'search_form', $vars );
	}
}
