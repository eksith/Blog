<?php

namespace Blog\Views;
use Blog\Handlers;
use Blog\Events;

class View extends Handlers\Handler {
	
	private $theme		= 'default';
	
	/**
	 * @var int Count of template includes per render
	 */
	private $included	= 0;
	
	/**
	 * Template option states
	 */
	protected $states	= array();
	
	/**
	 * Fallback default theme compile hash
	 */
	const COMPILE_HASH	= 'sha256';
	
	/**
	 * XML placeholder and matching rendered content
	 * ( Workaround for broken content also breaking the parser )
	 */
	protected $data = array();
	
	protected function setTheme( $theme ) {
		$path	= $this->getSetting( 'theme_path' );
		if ( file_exists( $path . $theme ) ) {
			$this->theme = $theme;
		}
	}
	
	protected function getTheme() {
		$path	= $this->getSetting( 'theme_path' );
		return $path . $this->theme . '/';
	}
	
	protected function getThemeDisplay() {
		$name	= $this->getSetting( 'theme_display' );
		return $name . $this->theme . '/';
	}
	
	/**
	 * Unescapes escaped open/close curly brackets - {}
	 */
	private function fixVars( $from ) {
		return preg_replace( 
			"/%7B([a-z_]{1,15})%7D/", 
			'{$1}', 
			$from 
		);
	}
	
	/**
	 * Substitute specific template placeholders with values
	 */
	protected function tplRender( $content, $template ) {
		$v = array();
		foreach( array_keys( $content ) as $k ) {
			$v[] = '{' . $k . '}';
		}
		return str_replace( $v, 
			array_values( $content ), $template );
	}
	
	/**
	 * Substitute loaded template placeholders with values
	 */
	protected function render( 
		$template, 
		$vars = array() 
	) {
		if ( empty( $vars ) || empty( $vars ) ) {
			return $template;
		}
		$keys = array_map( function( $k ) {
				return '{' . $k . '}';
			}, array_keys( $vars ) );
		
		return str_replace( 
				$keys, 
				array_values( $vars ), 
				$this->fixVars( $template ) 
			);
	}
	
	/**
	 * Workaround to prevent potentially broken user content 
	 * from also breaking the parser by using placeholders
	 */
	protected function renderXml( 
		$template, 
		$data 
	) {
		$k		= 
			'{XMLDATA_' . count( $this->data ) . '}';
		$this->data[$k] = $this->render( $template, $data );
		return $k;
	}
	
	/**
	 * Get any tags by name in the specified node
	 */
	protected function getTags( $tag, &$node ) {
		return $node->getElementsByTagName( $tag );
	}
	
	/**
	 * Check if template option state exits
	 */
	public function hasState( $name ) {
		return isset( $this->state[$name] );
	}
	
	/**
	 * Add template option state
	 */
	public function addState( $name, $value ) {
		$this->state[$name] = $value;
	}
	
	/**
	 * Delete template option state
	 */
	protected function removeState( $name ) {
		if ( isset( $this->state[$name] ) ) {
			unset( $this->state[$name] );
		}
	}
	
	/**
	 * Get specific option from template state
	 */
	protected function getState( $name ) {
		return isset( $this->state[$name] ) ? 
			$this->state[$name] : null;
	}
	
	/**
	 * All template option states
	 */
	protected function getAllStates() {
		return $this->state;
	}
	
	/**
	 * Helper to get any current state data to match loops 
	 * or conditions etc... in the XML template
	 */
	protected function getData( $obj ) {
		if ( false === strpos( $obj, ':' ) ) {
			return $this->getState( $obj );
		}
		$a = explode( ':', $obj );
		if ( 
			$this->hasState( $a[0] ) && 
			count( $a ) > 1 
		) {
			$o = $this->getState( $a[0] )->{$a[1]};
			return $o;
		}
	}
	
	// HTML rendering
	
	/**
	 * Replace the specified node's HTML with the given data
	 */
	protected function swapHTML( 
		$html, 
		&$node, 
		&$dom 
	) {
		$elem	= $dom->createDocumentFragment();
		$elem->appendXML( $html );
		$node->parentNode->replaceChild( $elem, $node );
	}
	
	/**
	 * Gets the inner HTML of an element including any child nodes
	 */
	protected function innerHTML( &$node ) {
		if ( !$node->hasChildNodes() ) { return ''; }
		$html		= '';
		$children	= $node->childNodes;
		foreach( $children as $c ) {
			$html .= $node->ownerDocument->saveHTML( $c );
		}
		return $html;
	}
	
	/**
	 * Prepares loop. 
	 * Removes/displays <empty> tag depending on whether 
	 * content is available
	 */
	protected function formatLoop( &$node, &$dom ) {
		$items		= $this->getData( 
					$node->getAttribute( 'rel' ) 
				);
		
		if ( count( $items ) ) { 
			// We have items. Remove the <empty> tags
			$this->scrubTag( 'empty', $node );
		} else {
			return $this->fromNodes( 'empty', $node );
		}
		
		$html		= '';
		$tpl		= $this->innerHTML( $node );
		
		// TODO: Move this outside the loop
		foreach( $items as $i ) {
			$html .= $this->renderXml( $tpl, $i );
		}
		return $html;
	}
	
	/**
	 * Delete all tags by the given name in the specified node
	 */
	protected function scrubTag( $tag, &$node ) {
		$em	= $this->getTags( $tag, $node );
		for ( $i = 0; $i < $em->length; $i++ ) {
			$e = $em->item( $i );
			$e->parentNode->removeChild( $e );
		}
	}
	
	/**
	 * Get the cumulative HTML from the specified tag names
	 */
	protected function fromNodes( $tag, &$node ) {
		$em		= $this->getTags( $tag, $node );
		$html		= '';
		for ( $i = 0; $i < $em->length; $i++ ) {
			$e = $em->item( $i );
			$html	.= $this->innerHTML( $e );
		}
		return $html;
	}
	
	/**
	 * Match specific state conditions
	 */
	protected function matchCondition( 
		$value, 
		&$conds
	) {
		$n = explode( ':', $value );
		if ( empty( $n ) ) {
			return false;
		}
		if ( isset( $conds[$n[0]] ) ) {
			if ( count( $n ) > 1 ) {
				if ( $conds[$n[0]] === $n[1] ) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Match the conditions of a 'rel' attribute and move out the 
	 * contents of the node and replace it
	 */
	protected function matchSwap( $conds, $node, &$dom ) {
		if ( !$node->hasAttribute( 'rel' ) ) {
			return false;
		}
		
		$r = $node->getAttribute( 'rel' );
		
		if ( $this->matchCondition( $r, $conds ) ) {
			$this->swapHTML( 
				$this->innerHTML( $node ), $node, $dom 
			);
			return true;
		}
		return false;
	}
	
	/**
	 * Parse any conditionals in the template.
	 * If the condition is true, replace the <if> tag with 
	 * the content inside it. Remove the rest if false.
	 */
	protected function parseConditions( $conds, &$dom ) {
		$ifs		= $dom->getElementsByTagName( 'if' );
		foreach ( $ifs as $node ) {
			if ( $this->matchSwap( $conds, $node, $dom ) ) {
				continue;
			}
			$node->parentNode->removeChild( $node );
		}
		
		$this->loadIncludes( $conds, $dom );
	}
	
	/**
	 * Cache key for compiled template conditions
	 */
	protected function conditionsKey( $conds ) {
		$hash	= $this->getSeting( 'theme_compile_hash' );
		if ( empty( $hash ) ) {
			$hash = self::COMPILE_HASH;
		}
		
		return hash(
			$hash,
			json_encode( $conds )
		);
	}
	
	/**
	 * Render stored template data
	 */
	protected function renderData( $page ) {
		foreach( $this->data as $k => $v ) {
			str_replace( $k, $v, $page );
		}
		return $page;
	}
	
	/**
	 * Parse repeated items
	 */
	protected function parseLoops( 
		&$conds, 
		&$dom 
	) {
		$loops		= $dom->getElementsByTagName( 'each' );
		foreach( $loops as $node ) {
			$html	= $this->formatLoop( $node, $dom );
			$this->swapHTML( $html, $node, $dom );
		}
		
		$this->loadIncludes( $conds, $dom );
	}
	
	/**
	 * Load include files.
	 * This does have some overhead so limit the number of includes.
	 */
	protected function loadIncludes( 
		&$conds, 
		&$dom 
	) {
		if ( $this->includeCheck() ) {
			return;
		}
		
		$files	= $this->getTags( 'include', $dom );
		foreach( $files as $f ) {
			if ( $this->includeCheck() ) {
				return;
			}
			if ( empty( $f->nodeValue ) ) {
				continue;
			}
			$data  = $this->loadFile( $f->nodeValue );
			$this->included++;
			
			// Swap the include tag with loaded data
			$this->swapHTML( $data, $f, $dom );
			
			// Parse any new conditions after load
			$this->parseConditions( $conds, $dom );
			
			// Parse any new loops
			$this->parseLoops( $conds, $dom );
		}
	}
	
	/**
	 * Template include limit. This check was introduced to prevent
	 * designers from getting carried away with too many includes
	 */
	protected function includeCheck() {
		$limit = $this->getSetting( 'theme_include_limit' );
		if ( $this->included >= $limit ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Template parser. 
	 * Creates a new DOMDocument and loads the template into itself.
	 * Calls for any conditionals to be interpreted, includes to be 
	 * loaded, and loops to be processed.
	 */
	protected function parse( 
		$tpl, 
		$conds,
		$cache
	) {
		$data				= 
			$this->loadFile( $tpl );
		
		$err				= 
			\libxml_use_internal_errors( true );
		
		$dom				= new \DOMDocument();
		$dom->preserveWhiteSpace	= false;
		$dom->formatOutput		= false;
		$dom->strictErrorChecking	= false;
		$dom->validateOnParse		= false;
		$dom->resolveExternals		= true;
		
		$dom->loadHTML( 
			$data, 
			\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
			\LIBXML_NOERROR | \LIBXML_NOWARNING | 
			\LIBXML_NOXMLDECL
		);
		
		// Parse pre-include conditions
		$this->parseConditions( $conds, $dom );
		
		// Loop through items
		$this->parseLoops( $conds, $dom );
		
		\libxml_clear_errors();
		\libxml_use_internal_errors( $err );
		
		$page = $dom->saveHTML();
		
		// Caching is enabled for this template
		if ( $cache ) {
			$this->cacheTemplate( $conds, $page );
		}
		
		return $this->renderData( $page );
	}
	
	/**
	 * Formatted view helper. 
	 * Calls to parse the given template with any conditions 
	 * and sends back rendered HTML
	 */
	protected function sendView( 
		$template, 
		$conds	= array(), 
		$vars	= array(),
		$cache	= false
	) {
		$html = $this->parse( $template, $conds, $cache );
		echo $this->render( $html, $vars );
	}
	
	/**
	 * Load a specific template file
	 */
	protected function loadFile( $name ) {
		$name	= $this->getTheme() . $name;
		if ( file_exists( $name ) ) {
			return file_get_contents(  $name );
		}
		return '';
	}
	
	/**
	 * Save generated page template to compile directory
	 */
	protected function cacheTemplate( $conds, $page ) {
		$key	= $this->conditionsKey( $conds );
		$path	= $this->getConfig( 'compiled_tpl_path' );
		
		file_put_contents( $path . $key, $page );
	}
	
	/**
	 * Map object property names to placeholder markers
	 * 
	 * @param array $items Object collection
	 * @param array $place Placeholder names matching each property 
	 * 		in the collection
	 * 
	 * @return array Mapped placeholders -> property values
	 */
	protected function mapObjToPlace(
		array $items, 
		array $place 
	) {
		$map	= array();
		$vals	= array_keys( $place );
		
		foreach ( $items as $item ) {
			$var = array();
			
			foreach( $vals as $v ) {
				if ( isset( $item->{$vals[$v]} ) ) {
					$var[$v] = $item->{$vals[$v]};
				}
			}
			if ( !empty( $var ) ) {
				$map[] = $var;
			}
		}
		
		return $map;
	}
}
