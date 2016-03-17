<?php

namespace Blog\Language;
use Blog\Views;
use Blog\Events;

class Locale extends Views\View {
	
	protected $defs	= array();
	
	protected $charset;
	
	protected $direction;
	
	protected $map = array(
		'en-us'	=> 'EnglishUS'
	);
	
	# https://stackoverflow.com/questions/19249159/best-practice-multi-language-website?rq=1
	public function handleEvent( Events\Event $event ) {
		$name	= $event->get( 'locale' );
		if ( empty( $name ) || empty( $this->map[$name] ) ) {
			$name = $this->processLocale( $event );
			$event->set( 'locale', $name );
		}
		$this->setLang( $name, $event );
	}
	
	private function processLocale( Events\Event $event ) {
		$langs	= $event->getRequest()
				->getBrowserProfile()
				->languages( $this->map );
		
		if ( empty( $langs ) ) {
			return 'en-us';
		}
		
		return array_keys( $langs )[0];
	}
	
	private function setLang( $name, Events\Event $event ) {
		$vars		= $this->map[$name];
		$lang		= $this->getSetting( 'language_files' );
		$this->defs	= $this->loadJson( $lang . $vars . '.json' );
		
		$event->set( 'lang', $this );
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
