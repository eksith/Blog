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
		$this->add( new Views\Content\Index( $sender ) );
	}
	
	private function read( $sender ) {
		$this->add( new Handlers\Content\Read( $sender ) );
		$this->add( new Views\Content\Read( $sender ) );
	}
	
	private function creating( $sender ) {
		$event->add( new Handlers\Content\Create( $sender ) );
		$event->add( new Views\Content\Create( $sender ) );
	}
	
	private function create( $sender ) {
		$event->add( new Handlers\Content\Create( $sender ) );
	}
	
	private function editing( $sender ) {
		$event->add( new Handlers\Content\Edit( $sender ) );
		$event->add( new Views\Content\Edit( $sender ) );
	}
	
	private function edit( $sender ) {
		$event->add( new Handlers\Content\Edit( $sender ) );
	}
	
	private function deleting( $sender ) {
		$event->add( new Handlers\Content\Delete( $sender ) );
		$event->add( new Views\Content\Delete( $sender ) );
	}
	
	private function delete( $sender ) {
		$event->add( new Handlers\Content\Delete( $sender ) );
	}
}

