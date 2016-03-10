<?php
namespace Blog\Views\Content;
use Blog\Events;
use Blog\Views;

class Manage extends Views\View {
	
	public function __construct( Events\Dispatcher $dispatcher ) {
		parent::__construct( $dispatcher );
		$this->setTheme( 'admin' );
	}
	
	public function handleEvent( Events\Event $event ) {
		$this->menuBuilder( $event, $conds );
	}
	
	public function viewPosts( Events\Event $event ) {
		$cond = array();
		$vars	= array(
			'page_title'	=> 'Post list',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay()
		);
		
		$vars	= array_merge( $vars, $event->get( 'search_form' ) );
		echo $this->sendView( 'manage_posts.html', $cond, $vars );
	}
	
	public function creatingPost( Events\Event $event ) {
		$cond = array(
			'editor'	=> 'create'
		);
		$vars	= array(
			'page_title'	=> 'New post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay()
		);
		
		$vars	= array_merge( $vars, $event->get( 'create_form' ) );
		echo $this->sendView( 'manage_newpost.html', $cond, $vars );
	}
	
	public function editingPost( Events\Event $event ) {
		$cond = array(
			'editor'	=> 'edit'
		);
		$vars = array(
			'page_title'	=> 'Editing post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay()
		);
		
		$vars = array_merge( $vars, $event->get( 'edit_form' ) );
		echo $this->sendView( 'manage_editpost.html', $cond, $vars );
	}
	
	public function deletingPost( Events\Event $event ) {
		$cond = array(
			'editor'	=> 'delete'
		);
		$vars = array(
			'page_title'	=> 'Deleting post',
			'page_heading'	=> 'Blog',
			'theme'		=> $this->getThemeDisplay()
		);
		
		$vars = array_merge( $vars, $event->get( 'delete_form' ) );
		echo $this->sendView( 'manage_content.html', $cond, $vars );
	}
}
