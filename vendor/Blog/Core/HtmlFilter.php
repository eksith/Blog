<?php

namespace Blog\Core;

/**
 * General purpose HTML filter with a whitelist of tags
 */
class HtmlFilter {
	
	/**
	 * @var object Parsedown Markdown parser
	 */
	private $parsedown;
	
	/**
	 * @var Whitelist of HTML tags and allowed attributes
	 */
	private $white = 
		array(
		'p'		=> array( 'style', 'class', 'align' ),
		'div'		=> array( 'style', 'class', 'align' ),
		'span'		=> array( 'style', 'class' ),
		'br'		=> array( 'style', 'class' ),
		'hr'		=> array( 'style', 'class' ),
		
		'h1'		=> array( 'style', 'class' ),
		'h2'		=> array( 'style', 'class' ),
		'h3'		=> array( 'style', 'class' ),
		'h4'		=> array( 'style', 'class' ),
		'h5'		=> array( 'style', 'class' ),
		'h6'		=> array( 'style', 'class' ),
		
		'strong'	=> array( 'style', 'class' ),
		'em'		=> array( 'style', 'class' ),
		'u'	 		=> array( 'style', 'class' ),
		'strike'	=> array( 'style', 'class' ),
		'del'		=> array( 'style', 'class' ),
		'ol'		=> array( 'style', 'class' ),
		'ul'		=> array( 'style', 'class' ),
		'li'		=> array( 'style', 'class' ),
		'code'		=> array( 'style', 'class' ),
		'pre'		=> array( 'style', 'class' ),
		
		'sup'		=> array( 'style', 'class' ),
		'sub'		=> array( 'style', 'class' ),
		
		# Took out 'rel' and 'title', because we're using those below
		'a'		=> array( 'style', 'class', 'href' ),
		
		'img'		=> array( 'style', 'class', 'src', 'height', 
				'width', 'alt', 'longdesc', 'title', 
				'hspace', 'vspace' ),
		
		'table'		=> array( 'style', 'class', 'border-collapse', 
				'cellspacing', 'cellpadding' ),
		'thead'		=> array( 'style', 'class' ),
		'tbody'		=> array( 'style', 'class' ),
		'tfoot'		=> array( 'style', 'class' ),
		'tr'		=> array( 'style', 'class' ),
		'td'		=> array( 'style', 'class', 
					'colspan', 'rowspan' ),
		'th'		=> array( 'style', 'class', 'scope', 'colspan', 
					'rowspan' ),
		'q'		=> array( 'style', 'class', 'cite' ),
		'cite'		=> array( 'style', 'class' ),
		'abbr'		=> array( 'style', 'class' ),
		'blockquote'	=> array( 'style', 'class' ),
		
		# Stripped out
		'body'		=> array()
		);
	
	public function __construct() {}
	
	/**
	 * Clean user provided HTML. Optionally apply markdown syntax
	 * 
	 * @param string $html Raw HTML
	 * @param bool $parse Apply markdown syntax formatting (defaults to true)
	 */
	public function clean( $html, $parse = true ) {
		
		$err		= \libxml_use_internal_errors( true );
		if ( $parse ) {
			$html = $this->getParsedown()->text( $html );
		}
		$html		= \mb_convert_encoding( 
					$html, 'HTML-ENTITIES', "UTF-8" 
				);
		
		$html		=  $this->tidyup( $html );
		
		$old		= new \DOMDocument();
			$old->loadXML( $html );
		
		
		$oldBody	= 
			$old->getElementsByTagName( 'body' )->item( 0 );
		
		$out		= new \DOMDocument();
		$outBody	= 
			$out->appendChild( $out->createElement( 'body' ) );
		
		$this->scrub( $oldBody, $outBody );
		$clean		= '';
		foreach ( $outBody->childNodes as $node ) {
			$clean .= $out->saveHTML( $node );
		}
		
		\libxml_clear_errors();
		\libxml_use_internal_errors( $err );
		return trim( $clean );
	}
	
	
	/* HTML Filtering */
	
	/**
	 * Scrub each node against white list
	 */
	protected function scrub( \DOMNode $old, \DOMNode $out ) {
		foreach ( $old->childNodes as $node ) {
			if ( 
				( $node->nodeType == \XML_ELEMENT_NODE ) && 
				( isset( $this->white[$node->nodeName] ) )
			) {
				if ( $node->nodeName == 'code' ) {
					$clean = 
					$out->ownerDocument->createElement( 
						'code', 
						$this->entities( $node->textContent )
					);
				} else {
					$clean = 
					$out->ownerDocument->createElement( 
						$node->nodeName,
						$node->textContent
					);
				}
				
				$this->cleanAttributes( $node, $clean );
				$out->appendChild( $clean );
				
				# Continue to other tags
				$this->scrub( $node, $clean );
				
			} elseif ( $node->nodeType == \XML_ELEMENT_NODE ) {
				# This tag isn't on the whitelist
				# Extract interior. Add as plaintext
				$text	= 
				$out->ownerDocument->createTextNode(
					$this->entities( $node->textContent )
				);
				$out->appendChild( $text );
			}
		}
	}
	
	/**
	 * Clean DOM node attribute against whitelist
	 * 
	 * @param $node object DOM Node
	 */
	protected function cleanAttributes(
		\DOMNode $node,
		\DOMNode &$clean
	) {
		foreach ( 
			\iterator_to_array( $node->attributes ) as $at
		) {
			$n = $at->nodeName;
			$v = $at->nodeValue;
			
			if ( in_array( $n, $this->white[$node->nodeName] ) ) {
				switch( $n ) {
					case 'longdesc':
					case 'url':
					case 'src':
					case 'href':
						$v = 
						\Blog\Messaging\Uri::cleanUrl( $v );
						break;
						
					default:
						$v = $this->entities( $v );
				}
				
				$clean->setAttribute( $n, $v );
			}
		}
	}
	
	/**
	 * Convert content between code tags into HTML entities safe for display 
	 * 
	 * @param $val string Value to encode to entities
	 */
	public function escapeCode( $val ) {
		if ( is_array( $val ) ) {
			$out = $this->entities( $val[1], true );
			return '<code>' . $out . '</code>';
		}
		return '<code>' . $val . '</code>';
	}
	
	/**
	 * HTML safe character entities in UTF-8
	 * 
	 * @return string
	 */
	public function entities( $v, $quotes = true ) {
		if ( $quotes ) {
			return \htmlentities( 
				\iconv( 'UTF-8', 'UTF-8', $v ), 
				\ENT_QUOTES | \ENT_SUBSTITUTE, 
				'UTF-8'
			);
		}
		
		return \htmlentities( 
			\iconv( 'UTF-8', 'UTF-8', $v ), 
			\ENT_NOQUOTES | \ENT_SUBSTITUTE, 
			'UTF-8'
		);
	}
	
	/**
	 * Get parsedown class
	 */
	protected function getParsedown() {
		if ( !isset( $this->parsedown ) ) {
			$this->parsedown = new \Parsedown();
		}
		return $this->parsedown;
	}
	
	/**
	 * Clean raw HTML against Tidy
	 */
	protected function tidyup( $text ) {
		$opt = array(
			'bare'				=> 1,
			'hide-comments' 		=> 1,
			'drop-proprietary-attributes'   => 1,
			'fix-uri'			=> 1,
			'join-styles'			=> 1,
			'output-xhtml'			=> 1,
			'merge-spans'			=> 1,
			'show-body-only'		=> 0,
			'wrap'				=> 0
		);
		
		return trim( \tidy_repair_string( $text, $opt ) );
	}
}

