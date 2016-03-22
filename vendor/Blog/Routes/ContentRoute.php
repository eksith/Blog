<?php

namespace Blog\Routes;
use Blog\Language;
use Blog\Handlers;
use Blog\Views;

class ContentRoute extends Route {
	
	public function route( array $map = array() ) {
		parent::route( $map );
		switch( $this->event->getName() ) {
			case 'creatingPost':
				$this->creating( $this->sender );
				break;
				
			case 'createPost':
				$this->create( $this->sender );
				break;
				
			case 'editingPost':
				$this->editing( $this->sender );
				break;
				
			case 'editPost':
				$this->edit( $this->sender );
				break;
				
			case 'deletingPost':
				$this->deleting( $this->sender );
				break;
				
			case 'deletePost':
				$this->delete( $this->sender );
				break;
				
			case 'read':
				$this->read( $this->sender );
				break;
				
			case 'viewPosts':
				$this->posts( $this->sender );
				break;
				
			default:
				$this->archive( $this->sender );
		}
		
		$this->sender->dispatch( 'route' );
	}
	
	private function index( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Index( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Index( $sender )
		) );
	}
	
	private function archive( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Index( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Index( $sender ),
			new Views\Content\Front( $sender )
		) );
	}
	
	private function read( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Read( $sender );
			new Handlers\Menu( $sender );
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Read( $sender ),
			new Views\Content\Front( $sender ),
		) );
	}
	
	private function posts( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Index( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Index( $sender ),
			new Views\Content\Manage( $sender )
		) );
	}
	
	private function creating( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Create( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Create( $sender ),
			new Views\Content\Manage( $sender )
		) );
	}
	
	private function create( $sender ) {
		$this->add( new Handlers\Content\Create( $sender ) );
	}
	
	private function editing( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Edit( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Edit( $sender ),
			new Views\Content\Manage( $sender )
		) );
	}
	
	private function edit( $sender ) {
		$this->add( new Handlers\Content\Edit( $sender ) );
	}
	
	private function deleting( $sender ) {
		$this->addHandlers( array(
			new Handlers\Content\Delete( $sender ),
			new Handlers\Menu( $sender )
		) );
		
		$this->add( new Language\Locale( $sender ) );
		
		$this->addViews( array(
			new Views\Content\Delete( $sender ),
			new Views\Content\Manage( $sender )
		) );
	}
	
	private function delete( $sender ) {
		$this->add( new Handlers\Content\Delete( $sender ) );
	}
}
