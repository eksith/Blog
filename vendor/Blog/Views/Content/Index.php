<?php

namespace Blog\Views\Content;
use Blog\Models;
use Blog\Events;
use Blog\Views;

class Index extends Views\View {
	
	protected $post_map = 
	array(
		'post_author'	=> 'author',
		'post_title'	=> 'title',
		'date_u'	=> 'pub',
		'date'		=> 'published_at',
		'post_id'	=> 'id',
		'post_tags'	=> 'tags'
	);
	
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
			'heading'	=> $title . ' - ' $tagline,
			'page_title'	=> $title,
			'host_uri'	=> $uri,
			'theme'		=> $this->getThemeDisplay()
		);
		
		# Test
		$test		= new Models\Post();
		$test->id	= 12;
		$test->title	= 'test title';
		$test->author	= 'author';
		
		$posts		= array( $test );
		$parsed		= $this->mapObjToPlace( 
					$posts, $this->post_map
				);
		
		$this->addState( 'posts', $posts );
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
