<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;

class ContentHandler extends Handlers\Handler {
	
	/**
	 * Load base properties for both creating and editing a post
	 */
	protected function basePost( $data, &$post ) {
		$post->title		= empty( $data['title'] ) ?
			'Untitled' : $data['title'];
		
		$post->raw		= empty( $data['body'] ) ? 
			'' : $data['body'];
		
		$post->summary		= empty( $data['summary'] ) ? 
			'' : $filter->clean( $data['summary'], false );
		
		$post->body		= $filter->clean( $post->raw );
		$post->plain		= strip_tags( $post->body );
		
		$pub			=  empty( $data['pubdate'] ) ?
			time() : strtotime( $data['pubdate'] . ' UTC' );
		
		$post->published_at	= Models\Model::myTime( $pub );
		
		$post->slug		= empty( $data['slug'] ) ?
			$this->slugify( $post->title ) :
			$this->slugify( $data['slug'] );
		
	}
	
	/**
	 * Convert a string into a page slug
	 */
	public static function slugify( $text ) {
		$text = preg_replace( '~[^\\pL\d]+~u', ' ', $text );
		$text = preg_replace( '/\s+/', '-', trim( $text ) );
		
		if ( empty( $text ) ) {
			return hash( 'md5', $title );
		}
		return $text;
	}
	
	/**
	 * Limit a string without cutting off words
	 */
	protected static function smartTrim( $val, $max = 100 ) {
		$val	= trim( $val );
		$len	= mb_strlen( $val );
		
		if ( $len <= $max ) {
			return $val;
		}
		
		$out	= '';
		$words	= preg_split( '/([\.\s]+)/', $val, -1, 
				\PREG_SPLIT_OFFSET_CAPTURE | 
				\PREG_SPLIT_DELIM_CAPTURE );
			
		for ( $i = 0; $i < count( $words ); $i++ ) {
			$w	= $words[$i];
			// Add if this word's length is less than length
			if ( $w[1] <= $max ) {
				$out .= $w[0];
			}
		}
		
		$out	= preg_replace( "/\r?\n/", '', $out );
		
		// If there's too much overlap
		if ( mb_strlen( $out ) > $max + 10 ) {
			$out = self::trimStr( $out, $max );
		}
		return $out;
	}
	
	/**
	 * Twitter style #hashtags link formatting
	 */
	protected function hashtags( 
		$body, 
		$hfx, 
		$ufx, 
		&$users	= array(), 
		&$tags	= array() 
	) {
		$body = 
		preg_replace_callback( 
			'/(?:[^-\\/"\':!=a-z0-9_@@]|^|\\:)[##]([\p{L}\p{N}]{1,30})/', 
			function( $matches ) use ( &$tags, $hfx ) {
				$tags[] = $matches[1];
				return 
				"<a href='{$hfx}{$matches[1]}'>{$matches[0]}</a>";
			}, 
			$body, 5 
		);
		
		$body = 
		preg_replace_callback( 
			'#(?:[^-\\/"\':!=a-z0-9_@@]|^|\\:)[@@]([\p{L}\p{N}]{2,30})#', 
			function( $matches ) use ( &$users, $ufx ) {
				$users[] = $matches[1];
				return 
				"<a href='{$ufx}{$matches[1]}'>{$matches[0]}</a>";
			},
			$body
		);
		return $body;
	}
}
