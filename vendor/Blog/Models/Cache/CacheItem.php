<?php

namespace Blog\Models\Cache;
use Blog\Models;

# http://www.php-fig.org/psr/psr-6/meta/
class CacheItem extends Models\Model 
	implements \Psr\CacheItemInterface {
	
	public $cache_id;
	public $data;
	public $expiration;
	public $ttl;
	
	private static $db;
	
	public function __construct() {
		$this->ttl		= 
			$this->getSetting( 'cache_time' );
		
		$this->expiration	= 
			time() + $this->getSetting( 'cache_time' );
	}
	
	protected static function getDb( $name = 'cache_store' ) {
		if ( isset( self::$db ) ) {
			return self::$db;
		}
		
		self::$db = parent::getDb( $name );
		return self::$db;
	}
	
	public static function find( array $keys = array() ) {
		$this->inParam( $in, $params, $keys );
		$sql	= 
		"SELECT cache_id, data FROM caches 
		WHERE cache_id IN (" . $in . ") 
		AND strftime('%s',expiration) > strftime('s%','now');";
		
		$stm	= self::getDb()->prepare( $sql );
		$stm->execute( $params );
		
		$res	= $stm->fetchAll(
				PDO::FETCH_GROUP | PDO::FETCH_ASSOC 
			);
		
		if ( empty( $res ) ) {
			return array();
		}
		return array_map( 'reset', $res );
	}
	
	# Magic methods mostly to help PDO
	public function __get( $name ) {
		if ( $name == 'key' ) {
			return isset( $this->cache_id ) ? 
				$this->cache_id : null;
		}
		return null;
	}
	
	public function __set( $name, $value ) {
		if ( $name == 'key' ) {
			$this->cache_id = $value;
		}
	}
	
	public function __isset( $name ) {
		if ( $name == 'key' ) {
			return isset( $this->cache_id );
		}
		return false;
	}
	
	public function getKey() {
		return $this->id;
	}
	
	public function get() {
		return isset( $this->data ) ? $this->data : null;
	}
	
	public function isHit() {
		return isset( $this->data ) ? true : false;
	}
	
	public function set( $value ) {
		$this->data		= $value;
	}
	
	public function expiresAt( $expiration ) {
		$this->expiration	= abs( ( int ) $expiration );
	}
	
	public function expiresAfter( $time = '' ) {
		if ( empty( $time ) ) {
			$time = $this->getSetting( 'cache_time' );
		}
		$this->ttl		= $time;
		$this->expiration	= time() + abs( $time );
	}
	
	public function delete( $key ) {
		self::deleteKeys( array( $key ) );
	}
	
	public static function deleteKeys( array $keys = array() ) {
		parent::inParam( $in, $params, $keys );
		
		$sql	= 
		'DELETE FROM caches WHERE id IN ( ' . $in . ' );';
		
		$stm	= self::getDb()->prepare( $sql );
		$stm->execute( $params );
	}
	
	public function save() {
		$params = array( 
			'cache_id'	=> $this->cache_id,
			'expiration'	=> $this->expiration,
			'ttl'		=> $this->ttl,
			'data'		=> $this->data
		);
		$sql	= $this->replaceSql( 'caches', $params );
		$stm	= self::getDb()->prepare( $sql );
		
		$stm->execute( $this->parseParams( $params ) );
	}
	
	public static function saveAll( $items ) {
		if ( empty( $items ) ) {
			return;
		}
		
		$sql	= 
		"REPLACE INTO caches (cache_id, expiration, ttl, data) 
			VALUES (:id, :expiration, :ttl, :data);";
		$stm	= self::getDb()->prepare( $sql );
		
		foreach( $items as $item ) {
			$param = 
			array( 
				':id'		=> $item->cache_id,
				':expiration'	=> $item->expiration,
				':ttl'		=> $item->ttl,
				':data'		=> $item->data
			);
			$stm->execute( $param );
		}
	}
	
	public static function gc() {
		$sql	= 
		"DELETE FROM caches WHERE 
			strftime(expiration) < strftime('%s','now');";
		
		$stm	= self::getDb()->prepare( $sql );
		$stm->execute();
	}
}
