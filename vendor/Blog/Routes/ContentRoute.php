<?php

namespace Blog\Routes;
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
				
			default:
				$this->archive( $this->sender );
		}
		
		$this->sender->dispatch( 'route' );
	}
	
	private function archive( $sender ) {
		$this->add( new Handlers\Content\Index( $sender ) );
		$this->add( new Handlers\Menu( $sender ) );
		
		$this->add( new Views\Content\Index( $sender ) );
		$this->add( new Views\Content\Front( $sender ) );
	}
	
	private function read( $sender ) {
		$this->add( new Handlers\Content\Read( $sender ) );
		$this->add( new Handlers\Menu( $sender ) );
		
		$this->add( new Views\Content\Read( $sender ) );
		$this->add( new Views\Content\Front( $sender ) );
	}
	
	private function creating( $sender ) {
		$this->add( new Handlers\Content\Create( $sender ) );
		$this->add( new Handlers\Menu( $sender ) );
		
		$this->add( new Views\Content\Create( $sender ) );
		$this->add( new Views\Content\Manage( $sender ) );
	}
	
	private function create( $sender ) {
		$this->add( new Handlers\Content\Create( $sender ) );
	}
	
	private function editing( $sender ) {
		$this->add( new Handlers\Content\Edit( $sender ) );
		$this->add( new Handlers\Menu( $sender ) );
		
		$this->add( new Views\Content\Edit( $sender ) );
		$this->add( new Views\Content\Manage( $sender ) );
	}
	
	private function edit( $sender ) {
		$this->add( new Handlers\Content\Edit( $sender ) );
	}
	
	private function deleting( $sender ) {
		$this->add( new Handlers\Content\Delete( $sender ) );
		$this->add( new Handlers\Menu( $sender ) );
		
		$this->add( new Views\Content\Delete( $sender ) );
		$this->add( new Views\Content\Manage( $sender ) );
	}
	
	private function delete( $sender ) {
		$this->add( new Handlers\Content\Delete( $sender ) );
	}
}
