<?php

namespace Blog\Models;

/**
 * Firewall block entry
 */
class Block extends Model {
	
	/**
	 * Block category label
	 * 
	 * @var string
	 */
	public $label;
	
	/**
	 * Block matching terms
	 * 
	 * @var string
	 */
	public $term;
	
	/**
	 * Expiration date (TODO GC)
	 * 
	 * @var string
	 */
	public $expires_at;
	
	public function __construct( $data = array() ) {
		parent::ifIsset( $this, $data );
	}
	
	public static function find( array $search ) {
		if ( !isset( $search['terms'] ) ) {
			return array();
		}
		
		$params = array();
		$sql	= 
		"SELECT label, expires_at FROM blocks WHERE ";
		
		if ( is_array( $search['terms'] ) ) {
			$this->inParam( $in, $params, $search['terms'] );
			$sql .= "term IN ( $in )";
		} else {
			$params[':term']	= $search['terms'];
			$sql			.= 'term = :term';
		}
		
		
		if ( isset( $search['label'] ) ) {
			$params[':label']	= $search['label'];
			$sql			.= ' AND label = :label';
		}
		
		$sql .= ';';
		
		return 
		parent::query( $sql, $params, 'class', 'firewall_store' );
		
	}
	
	public function save() {
		$params	= parent::ifIsset( 
			$this, 
			array( 
				'label', 'term', 'expires_at'
			)
		);
		
		if ( isset( $this->id ) ) {
			return 
			parent::edit( 
				'blocks', 
				$this->id, 
				$params, 
				'firewall_store'
			);
		}
		
		$this->id = parent::put( 
				'blocks', $params, 'firewall_store' 
			);
		
		return $this->id;
	}
}
