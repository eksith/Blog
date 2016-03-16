<?php

namespace Blog\Views\Content;
use Blog\Models;
use Blog\Events;
use Blog\Views;

class Index extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_default' ) );
	}
	
	public function index( Events\Event $event ) {
		$conds		= array();
		
		$title		= $this->getSetting( 'blog_name' );
		$tagline	= $this->getSetting( 'blog_tagline' );
		$uri		= $this->getRequest()
					->getUri()
					->getRoot();
		
		$vars		= 
		array(
			'heading'	=> $title . ' - ' . $tagline,
			'page_title'	=> $title,
			'host_uri'	=> $uri,
			'theme'		=> $this->getThemeDisplay()
		);
		
		$posts		= $event->get( 'posts' );
		$parsed		= array();
		
		foreach ( $posts as $post ) {
			$parsed[$post->id] = array();
			foreach( $this->post_map as $k => $v ) {
				if ( is_array( $post->{$v} ) ) {
					continue;
				}
				$parsed[$post->id][$k] = $post->{$v};
			}
		}
		
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
		
		$event->set( 'search_form', $vars );
	}
}
