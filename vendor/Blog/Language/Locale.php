<?php

namespace Blog\Language;
use Blog\Views;
use Blog\Events;

class Locale extends Views\View {
	
	protected $defs	= array();
	
	protected $charset;
	
	protected $direction;
	
	protected $map = array(
		'en-us' => 'EnglishUS'
	);
	
	public function handleEvent( Events\Event $event ) {
		$name	= $event->get( 'locale' );
		if ( empty( $name ) || empty( $this->map[$name] ) ) {
			$lang = $this->setLang( 'en-us', $event );
		} else {
			$lang = $this->setLang( $name, $event );
		}
	}
	
	private function setLang( $name, Events\Event $event ) {
		$class	= $this->map[$name];
		$locale = new $class( $event );
	}
	
	public function term() {
		$args	= func_get_args();
		$label	= array_shift( $args );
		
		if ( empty( $this->defs[$label] ) ) {
			return '';
		}
		
		if ( empty( $args ) ) {
			return $this->defs[$label];
		}
		
		return vsprintf( $this->defs[$label], $args );
	}
}
