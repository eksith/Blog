<?php

namespace Blog\Core\Cache;

class CacheItemPool implements \Psr\CacheItemPoolInterface {
	
	private static $pool	= array();
	
	private static $model;
	
	private $pending;
	
	public function __construct() {
		$pending	= new \SplObjectStorage();
	}
	
	public function getItem( $key ) {
		if ( isset( self::$pool[$key] ) ) {
			return self::$pool[$key];
		}
		
		return $this->getItems( array( $key ) );
	}
	
	public function getItems( array $keys = array() ) {
		if ( empty( $keys ) ) {
			return array();
		}
		$found	= array_intersect_key( 
				self::$pool, 
				$keys
			);
		
		$find	= array_diff( $keys, array_keys( $found ) );
		
		$res	= CacheItem::find( $find );
		if ( !empty( $res ) ) {
			self::$pool = array_merge( self::$pool, $res );
		}
		
		return array_intersect_key( self::$pool, $keys );
	}
	
	public function hasItem( $key ) {
		return isset( self::$pool[$key] );
	}
	
	public function deleteItem( $key ) {
		if ( isset( self::$pool[$key] ) ) {
			unset( self::$pool[$key] );
		}
		CacheItem::deleteKeys( array( $key ) );
	}
	
	public function deleteItems( array $keys ) {
		if ( empty( $keys ) ) {
			return;
		}
		
		foreach ( $keys as $key ) {
			if ( isset( self::$pool[$key] ) ) {
				unset( self::$pool[$key] );
			}
		}
		
		CacheItem::deleteKeys( $keys );
	}
	
	public function save( \Psr\CacheItemInterface $item ) {
		$item->save();
	}
	
	public function saveDeferred( \Psr\CacheItemInterface $item ) {
		if ( $this->pending->contains( $item ) ) {
			return;
		}
		
		$this->pending->attach( $item );
	}
	
	public function commit() {
		CacheItem::saveAll( $this->pending );
	}
}
