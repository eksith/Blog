<?php
namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Manage extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( $this->getSetting( 'theme_admin' ) );
	}
	
	public function handleEvent( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
	}
	
	public function viewPosts( Events\Event $event ) {
		$cond	= array();
		$lang	= $event->get( 'lang' );
		
		$vars	= 
		array(
			'page_title'	=> 'Post list',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> 
				$lang->term( 'copyright', date( 'Y' ) )
		);
		
		$vars	= array_merge( $vars, $event->get( 'search_form' ) );
		echo $this->sendView( 'manage_posts.html', $cond, $vars );
	}
	
	public function creatingPost( Events\Event $event ) {
		$cond	= 
		array(
			'editor'	=> 'create'
		);
		$lang	= $event->get( 'lang' );
		
		$vars	= 
		array(
			'page_title'	=> 'New post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> 
				$lang->term( 'copyright', date( 'Y' ) )
		);
		
		$vars	= array_merge( $vars, $event->get( 'create_form' ) );
		echo $this->sendView( 'manage_posteditor.html', $cond, $vars );
	}
	
	public function editingPost( Events\Event $event ) {
		$cond	= 
		array(
			'editor'	=> 'edit'
		);
		$lang	= $event->get( 'lang' );
		
		$vars	=
		array(
			'page_title'	=> 'Editing post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> 
				$lang->term( 'copyright', date( 'Y' ) )
		);
		
		$vars = array_merge( $vars, $event->get( 'edit_form' ) );
		echo $this->sendView( 'manage_posteditor.html', $cond, $vars );
	}
	
	public function deletingPost( Events\Event $event ) {
		$cond	= 
		array(
			'editor'	=> 'delete'
		);
		$lang	= $event->get( 'lang' );
		
		$vars	= 
		array(
			'page_title'	=> 'Deleting post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay(),
			'copyright'	=> $lang->term( 'copyright', 2016 )
		);
		
		$vars = array_merge( $vars, $event->get( 'delete_form' ) );
		echo $this->sendView( 'manage_posteditor.html', $cond, $vars );
	}
}
