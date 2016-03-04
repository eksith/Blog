<?php

namespace Blog\Models;

abstract class PagedList extends Model 
	implements \ArrayAccess, \SeekableIterator, \Countable {
	
	protected $key;
	protected $items	= array();
	protected $sort_by	= '';
	protected $group_by	= '';
	protected $limit	= '';
	protected $page_size	= 10;
	
	protected $paginator;
	
	private $valid		= false;
	private $cache_enabled	= true;
	private $cache		= array();
	
	
	abstract public function find(
		$fields		= null,
		$filter		= array(),
		$limit		= null,
		$page		= 1
	);
	
	abstract protected function loadItem( $result );
	
	protected function populate( $options = null ) {
		$this->items	= 
		parent::query(	
			$options['sql'],
			$options['params'],
			$options['return'],
			$options['db']
		);
	}
	
	# http://www.devnetwork.net/viewtopic.php?f=19&t=86011
	# Array Access section
	public function offsetExists( $offset ) {
		return array_key_exists( $offset, $this->items );
	}
	
	public function offsetSet( $offset, $value ) {
		$this->offset 	= $value;
	}
	
	public function offsetUnset( $offset ) {
		$this->offset	= 0;
	}
	
	public function offsetGet( $offset ) {
		if ( $this->offsetExists( $offset ) ) {
			if ( $this->cache_enabled ) {
				
			} else {
				
			}
		}
		return $this->offset;
	}
	
	public function getPaginator() {
		if ( isset( $this->paginator ) ) {
			return $this->paginator;
		}
		
		$this->paginator = 
		new Paginator( $this, $this->page_size );
	}
	
	# Iterator Section
	public function rewind() {
		$this->key	= 0;
	}
	
	public function next() {
		$this->key++;
	}
	
	public function key() {
		return $this->key;
	}
	
	public function valid() {
		return array_key_exists( $this->key, $this->items );
	}
	
	public function current() {
		return $this->loadItem( $this->key );
	}
	
	public function count() {
		return count( $this->items );
	}
	
	public function seek( $index ) {
		if ( isset( $this->items[$index] ) ) {
			$this->key = $index;
		} else {
			throw new 
			\OutOfBoundsException( 'Invalid seek position' );
		}
	}
	
	public function getIterator() {
		return $this;
	}
	
	public function count() {
		return count( $this->items );
	}
	
	public function cachePackage() {
		return 
		json_encode( $this->items, 
			\JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_TAG |
			\JSON_HEX_AMP | \JSON_PRESERVE_ZERO_FRACTION
		);
	}
	
	public function cacheUnpackage( $data ) {
		$package	= json_decode( utf8_encode( $data ) );
		$items		= array();
		foreach ( $package as $item ) {
			$items[] = $item;
		}
		
		return $items;
	}
}
