<?php
namespace Blog\Models;

class Session extends Model {
	
	private static $life;
	
	private static $db;
	
	public $skey;
	
	private $key;
	
	public $data;
	
	public $decrypted;
	
	public function setKey( $key ) {
		$this->key = $key;
	}
	
	public function __get( $name ) {
		if ( 'content' == $name ) {
			return $this->decryptedData();
		}
			
		return null;
	}
	
	protected static function getDb( $name = 'session_store' ) {
		if ( isset( self::$db ) ) {
			return self::$db;
		}
		
		self::$db = parent::getDb( $name );
		
		return self::$db;
	}
	
	public static function find( $id ) {
		if ( !isset( self::$life ) ) {
			self::$life	= 
			get_cfg_var( "session.gc_maxlifetime" );
		}
		
		$stm 	 = 
		"SELECT id, skey, data FROM sessions 
		WHERE id = :id AND ( strftime('%s', updated_at) + :t ) > 
			strftime('%s', 'now');";
		
		$params	= array( 
				':id'	=> $id, 
				':t'	=> self::$life 
			);
		return parent::query( $sql, $params, 'class' );
	}
	
	public function delete() {
		if ( !isset( $this->id ) ) {
			return;
		}
		parent::run( 
			'DELETE FROM sessions WHERE id = :id;', 
			array( ':id' => $this->id ), 
			'session_store' 
		);
	}
	
	public function save() {
		$this->skey	= 
		$this->getCrypto()->bytes( 
				$this->getSetting( 'session_key' )
			);
		
		$params = array(
			'skey'		=> $this->skey, 
			'content'	=> $this->encrypted(), 
			'id'		=> $this->id 
		);
		return 
		$this->replace( 
			'sessions', $params, 'session_store'
		);
	}
	
	public static gc( $exp ) {
		$db	= self::getDb( 'session_store' );
		$db->beginTransaction();
		
		$stm	= $db->prepare(
				"DELETE FROM sessions WHERE 
				( strftime('%s', updated_at) + :t ) < 
				strftime('%s', 'now');" 
			);
		
		$stm->execute( array( ':t' => $exp ) );
		$db->commit();
	}
	
	private function encrypted() {
		return 
		$this->getCrypto()->encrypt(
			$this->data, $this->getStoreKey()
		);
	}
	
	private function decryptedData() {
		if ( isset( $this->decrypted ) ) {
			return $this->decrypted;
		}
		
		if ( !isset( $this->data ) ) {
			return null;
		}
		
		$this->decrypted = 
		$this->getCrypto()-decrypt(
			$this->data, $this->getStoreKey()
		);
		
		return $this->decrypted;
	}
	
	private function getStoreKey() {
		return hash( 
			$this->getSetting( 'session_hash' ), 
			$this->key . $this->skey
		);
	}
}
