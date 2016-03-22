<?php

namespace Blog\Handlers\Content;
use Blog\Handlers;

class ContentHandler extends Handlers\Handler {
	
	protected function editorStatus( $user_id, $post_user, $event ) {
		$status	= $event->get( 'user_status' );
		if ( $post_user == $user_id && $status > -1 ) {
			return true;
		}
		$admin	= $this->getSetting( 'user_status_admin' );
		$mod	= $this->getSetting( 'user_status_mod' );
		
		return ( $status ==  $admin || $status == $mod );
	}
	
	/**
	 * Load base properties for both creating and editing a post
	 */
	protected function basePost( $data, &$post ) {
		$filter			= $this->getHtmlFilter();
		
		$post->title		= empty( $data['title'] ) ?
			'Untitled' : $data['title'];
		
		$post->raw		= empty( $data['body'] ) ? 
			'' : $data['body'];
		
		$post->body		= $filter->clean( $post->raw );
		$post->plain		= strip_tags( $post->body );
		
		$post->summary		= empty( $data['summary'] ) ? 
			$this->smartTrim( $post->plain ) : 
			$filter->clean( $data['summary'], false );
		
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
	public static function slugify( $title, $text ) {
		$text = preg_replace( '~[^\\pL\d]+~u', ' ', $title );
		$text = preg_replace( '/\s+/', '-', trim( $text ) );
		
		if ( empty( $text ) ) {
			return hash( 'md5', $title );
		}
		return strtolower( $text );
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
			# Add if this word's length is less than length
			if ( $w[1] <= $max ) {
				$out .= $w[0];
			}
		}
		
		$out	= preg_replace( "/\r?\n/", '', $out );
		
		# If there's too much overlap
		if ( mb_strlen( $out ) > $max + 10 ) {
			$out = mb_substr( $out, 0, $max );
		}
		return $out;
	}
	
	/**
	 * Twitter style @reply formatting
	 */
	protected static function atReplies(
		$body, 
		$ufx, 
		&$users	= array()
	) {
		$body	= 
		preg_replace_callback(
			'/@([\p{Pc}\p{N}\p{L}\p{Mn}]{2,30})/u',
			function( $matches ) use ( &$users, $ufx ) {
				$users[] = strtolower( $matches[1] );
				return 
				"<a href='{$ufx}{$matches[1]}'>@{$matches[0]}</a>";
			},
			$body, 5
		);
		
		$uesrs = array_unique( $users );
		
		return $body;
	}
	
	/**
	 * Memory formatting
	 */
	protected static function formatBytes( $bytes, $precision = 2 ) {
		$units	= array('B', 'KB', 'MB', 'GB', 'TB');
		
		$bytes	= max( $bytes, 0 );
		$pow	= floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow	= min( $pow, count( $units ) - 1 );
		
		return round( $bytes, $precision ) . ' ' . $units[$pow];
	}
	
	/**
	 * Create a URL based on the date and title
	 * @example /2015/02/26/how-the-west-was-won
	 */
	protected static function datePath( 
		$title, 
		$time	= null 
	) {
		$p = ( null == $time ) ? date( 'Y/m/d' ) : date( 'Y/m/d', $time );
		return $p . '/' . static::slugify( $title );
	}
}
