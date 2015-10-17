<?php
namespace Blog;

class BlogSession implements \SessionHandlerInterface {
	
	private static $base;
	private static $life;
	private static $exists	= false;
	private static $session;
	
	public function open( $path, $name ) {
		self::$life	= get_cfg_var( "session.gc_maxlifetime" );
		self::$base	= new Data();
		return true;
	}
	
	public function close() {
		self::$base = null;
		return true;
	}
	
	private function getDb() {
		return self::$base->getDb( SESSION_STORE );
	}
	
	public function read( $id ) {
		$db	= $this->getDb();
		$stm 	= $db->prepare(
			"SELECT sesssion_id, skey, content FROM sessions 
			WHERE session_id = :id AND 
			( strftime('%s', updated_at) + :t ) > strftime('%s', 'now');"
		);
		
		$stm->execute( array( 
			':id'	=> $id, 
			':t'	=> self::$life 
		) );
		$data = fetch();
		if ( empty( $data )  ) {
			self::$exits = false;
			return '';
		}
		self::$exists	= true;
		
		$session = $data['content'];
		if ( empty( $session ) ) {
			return '';
		}
		return $session;
	}
	
	public function write( $id, $data ) {
		$db	= $this->getDb();
		$db->beginTransaction();
		
		$session = $this->read( $id );
		
		if ( $data == $session ) {
			$db->rollback();
			return true;
		}
		
		$params = array(
			'skey'		=> $this->rnd(5), 
			'content'	=> $data, 
			'session_id'	=> $id 
		);
		
		if ( self::$exists ) {
			$sql = self::$base->insertSql( $params );
		} else {
			$sql = self::$base->updateSql( $params );
		}
		$stm = $db->prepare( $sql );
		$stm->execute( $params );
		
		$db->commit();
		
		return true;
	}
	
	public function destroy( $id ) {
		$db	= $this->getDb();
		
		$db->beginTransaction();
		
		$stm = $db->prepare( 
			"DELETE FROM sessions 
				WHERE session_id = :session_id;" 
		);
		$stm->execute( array( ( ':session_id' => $id ) );
		$db->commit();
	}
	
	public function gc( $exp ) {
		$db	= $this->getDb();
		
		$db->beginTransaction();
		
		$stm	= $db->prepare(
			"DELETE FROM sessions WHERE 
			( strftime('%s', updated_at) + :t ) < strftime('%s', 'now');" 
		);
		$stm->execute( array( ':t' => $exp ) );
		$db->commit();
		return true;
	}
	
	
	private function rnd( $size ) {
		return mcrypt_create_iv( $size, MCRYPT_DEV_URANDOM );
	}
}
