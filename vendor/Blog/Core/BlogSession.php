<?php
namespace Blog\Core;
use Blog\Models;

final class BlogSession
	implements \SessionHandlerInterface {
	
	private static $crypto;
	private $request;
	
	public function __construct( $request ) {
		$this->request = $request;
	}
	
	public function open( $path, $name ) {
		return true;
	}
	
	public function close() {
		self::$db = null;
		return true;
	}
	
	public function read( $id ) {
		$session = Models\Session::find( $id );
		
		if ( empty( $session )  ) {
			return '';
		}
		
		$session->setKey( $this->request->getSignature() );
		return $session->data;
	}
	
	public function write( $id, $data ) {
		$session	= new Models\Session();
		$session->id	= $id;
		$session->data	= $data;
		$session->setKey( $this->request->getSignature() );
		
		$session->save();
		return true;
	}
	
	public function destroy( $id ) {
		$session	= new Models\Session();
		$session->id	= $id;
		$session->delete();
	}
	
	public function gc( $exp ) {
		Models\Session::gc( $exp );
		return true;
	}
}
