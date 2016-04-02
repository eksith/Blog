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
	
		# Remove control chars except linebreaks/tabs etc...
		$html		= 
		preg_replace(
			'/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', 
			'', 
			$html
		);
		if ( $parse ) {
			$html = $this->getParsedown()->text( $html );
		}
		$html		= \mb_convert_encoding( 
					$html, 'HTML-ENTITIES', "UTF-8" 
				);
		
		$html		= tidyup( $html );
		$dom		= new \DOMDocument();
		$dom->loadHTML( 
			$html, 
			\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
			\LIBXML_NOERROR | \LIBXML_NOWARNING | 
			\LIBXML_NOXMLDECL | \LIBXML_COMPACT | 
			\LIBXML_NOCDATA
		);
		
		$domBody	= 
		$dom->getElementsByTagName( 'body' )->item( 0 );
		
		# Iterate through every HTML element 
		foreach( $domBody->childNodes as $node ) {
			$this->scrub( $node, $flush );
		}
		
		# Remove any tags not found in the whitelist
		if ( !empty( $flush ) ) {
			foreach( $flush as $node ) {
				if ( $node->nodeName == '#text' ) {
					continue;
				}
				# Replace tag has harmless text
				$safe	= $dom->createTextNode( 
						$dom->saveHTML( $node )
					);
				$node->parentNode
					->replaceChild( $safe, $node );
			}
		}
		
		$clean		= '';
		foreach ( $domBody->childNodes as $node ) {
			$clean .= $dom->saveHTML( $node );
		}
		
		\libxml_clear_errors();
		\libxml_use_internal_errors( $err );
		
		# Format any embedded media
		if ( $parse ) {
			$clean = $this->embeds( $clean );
		}
		return trim( $clean );
	}
	
	
	/* HTML Filtering */
	
	
	/**
	 * Scrub each node against white list
	 */
	protected function scrub( \DOMNode $node, &$flush = array() ) {
		if ( isset( $this->white[$node->nodeName] ) ) {
			# Clean attributes first
			$this->cleanAttributes( $node );
			
			if ( $node->childNodes ) {
				# Continue to other tags
				foreach ( $node->childNodes as $child ) {
					$this->scrub( $child, $flush, $white );
				}
			}
		} elseif ( $node->nodeType == \XML_ELEMENT_NODE ) {
			# This tag isn't on the whitelist
			$flush[] = $node;
		}
	}
	
	/**
	 * Clean DOM node attribute against whitelist
	 * 
	 * @param $node object DOM Node
	 */
	protected function cleanAttributes(
		\DOMNode $node
	) {
		foreach ( 
			\iterator_to_array( $node->attributes ) as $at
		) {
			$n = $at->nodeName;
			$v = $at->nodeValue;
			
			# Default action is to remove attribute
			# It will only get added if it's safe
			$node->removeAttributeNode( $at );
			
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
				
				$node->setAttribute( $n, $v );
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
		if ( !function_exists( 'tidy_repair_string' ) ) {
			return $text;
		}
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
	
	
	/**
	 * Embedded Big Brother silo media
	 */
	function embeds( $html ) {
		$filter		= 
		array(
			'/\[youtube http(s)?\:\/\/(www)?\.?youtube\.com\/watch\?v=([0-9a-z_]*)\]/is'
			=> 
			'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$3" frameborder="0" allowfullscreen></iframe></div>',
			
			'/\[youtube http(s)?\:\/\/(www)?\.?youtu\.be\/([0-9a-z_]*)\]/is'
			=> 
			'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$3" frameborder="0" allowfullscreen></iframe></div>',
			
			'/\[youtube ([0-9a-z_]*)\]/is'
			=> 
			'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>',
			
			'/\[vimeo ([0-9]*)\]/is'
			=> 
			'<div class="media"><iframe src="https://player.vimeo.com/video/$1?portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>',
			
			'/\[vimeo http(s)?\:\/\/(www)?\.?vimeo\.com\/([0-9]*)\]/is'
			=> 
			'<div class="media"><iframe src="https://player.vimeo.com/video/$3?portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>'
		);
		
		return 
		preg_replace( 
			array_keys( $filter ), 
			array_values( $filter ), 
			$html 
		);
	}
}
