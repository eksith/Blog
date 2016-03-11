<?php

namespace Blog\Models;

class Model {
	
	/**
	 * Unique identifier
	 * 
	 * @var it
	 */
	public $id;
	
	/**
	 * Creation datetime
	 * 
	 * @var string
	 */
	public $created_at;
	
	/**
	 * Last edited datetime
	 * 
	 * @var string
	 */
	public $updated_at;
	
	/**
	 * Entity status 
	 * 
	 * @example
	 * 0 = Approved
	 * 1 = Awaiting moderation
	 * 2 = Locked
	 * 3 = Promoted
	 * 4 = Promoted locked
	 * -1 = Buried
	 * 
	 * @var int
	 */
	public $status;
	
	/**
	 * @var object Configuration class
	 */
	protected static $config;
	
	/**
	 * @var object Cryptography class
	 */
	protected static $crypto;
	
	/**
	 * @var array Connection store of PDO objects
	 */
	protected static $db	= array();
	
	public function __destruct() {
		static::$db	= null;
	}
	
	protected static function getDb( $name = 'content_store' ) {
		if ( isset( static::$db[$name] ) ) {
			return static::$db[$name];
		}
		
		$name	= static::$config->getSetting( $name );
		$options		= array(
			\PDO::ATTR_TIMEOUT		=> 
			static::$config->getSetting( 'data_timeout' ),
			
			\PDO::ATTR_DEFAULT_FETCH_MODE	=> 
				\PDO::FETCH_ASSOC,
			
			\PDO::ATTR_PERSISTENT		=> false,
			\PDO::ATTR_EMULATE_PREPARES	=> false,
			\PDO::ATTR_ERRMODE		=> 
				\PDO::ERRMODE_EXCEPTION
		);
		$type	= self::dbType( $dsn );
		$dsn	= self::dsn( $name, $username, $password );
			
		static::$db[$name]	= 
			new \PDO( $dsn, $username, $password, $options );
		
		// If this is sqlite, enable Write-Ahead Logging
		if ( 'sqlite' == $type ) {
			static::$db[$name]->exec(
				'PRAGMA journal_mode = WAL;'
			);
		}
		
		return static::$db[$name];
	}
	
	public static function setConfig( $config ) {
		static::$config = $config;
	}
	
	public static function setCrypto( $crypto ) {
		static::$crypto = $crypto;
	}
	
	protected static function getCrypto() {
		if ( !isset( static::$crypto ) ) {
			static::$rypto = new \Blog\Core\Crypto();
		}
		
		return static::$crypto;
	}
	
	protected static function getSetting( $setting ) {
		if ( !isset( static::$config ) ) {
			static::$config = new \Blog\Core\Config();
		}
		return static::$config->getSetting( $setting );
	}
	
	protected static function parseParams( $params ) {
		$k = array_keys( $params );
		$v = ':' . implode( ',:', $k );
		
		return array_combine( 
			explode( ',',  $v ), 
			array_values( $params ) 
		);
	}
	
	protected static function replaceSql( $table, $params ) {
		$k = array_keys( $params );
		$f = implode( ',', $k );
		$v = ':' . implode( ',:', $k );
		
		return "REPLACE INTO $table ( $f ) VALUES ( $v );";
	}
	
	protected static function insertSql( $table, $params ) {
		$k = array_keys( $params );
		$f = implode( ',', $k );
		$v = ':' . implode( ',:', $k );
		
		return "INSERT INTO $table ( $f ) VALUES ( $v );";
	}
	
	protected static function deleteSql( $table, $params ) {
		$k = array_keys( $params );
		$v = array_map( function( $f ) {
			return "$f = :$f";
		}, $k );
		
		$p = implode( ',', $v );
		return "DELETE FROM $table WHERE $p;";
	}
	
	protected static function updateSql( $table, $params, $cond ) {
		$k = array_keys( $params );
		$v = array_map( function( $f ) {
			return "$f = :$f";
		}, $k );
		
		$p = implode( ',', $v );
		return "UPDATE $table SET $p WHERE $cond;";
	}
	
	protected static function baseFilter( 
		$filter, 
		&$id, 
		&$limit, 
		&$page, 
		&$sort 
	) {
		$id	= isset( $filter['id'] ) ? $filter['id'] : 0;
		
		$limit	= isset( $filter['limit'] ) ? 
				$filter['limit'] : 
				static::$config->getSetting( 
					'list_per_page' 
				);
		
		$page	= isset( $filter['page'] ) ? 
				$filter['page'] : 1;
		
		$sort	= isset( $filter['sort'] ) ? 
				$filter['sort'] : null;
	}
	
	protected static function inParam(
		&$s	= '',
		&$p	= array(), 
		$data	= array(), 
		$f	= 'param' 
	) {
		$c = count( $data );
		for ( $i = 0; $i < $c; $i++ ) {
			$a	= ':' . $f . '_' . $i;
			$p[$a]	= $data[$i];
			$s	.= $a . ',';
		}
		
		$s = rtrim( ',', $s );
	}
	
	protected static function query( 
		$sql, 
		$params, 
		$class		= null,
		$db		= '' 
	) {
		$stm	= self::getDb( $db )->prepare( $sql );
		$stm->execute( $params );
		
		if ( empty( $class ) ) {
			$result	=  $stm->fetchAll();
			
		} elseif( 'object' == gettype( $class ) ) {
			$result = $stm->fetchAll( 
					\PDO::FETCH_CLASS, 
					get_class( $class )
				);
		} else {
			switch( $class ) {
				case 'class' :
					$result = $stm->fetchAll( 
						\PDO::FETCH_CLASS, 
						get_called_class() 
					);
					break;
					
				default:
					$result	=  $stm->fetchAll();
			}
		}
		
		return $result;
	}
	
	protected function replace( 
		$table, 
		$params = array(), 
		$db	= '' 
	) {
		$sql	= static::replaceSql( $table, $params );
		$params = static::parseParams( $params );
		
		return $this->run( $sql, $params, $db );
	}
	
	protected static function run( $sql, $params, $db = '' ) {
		$stm	= self::getDb( $db )->prepare( $sql );
		return $stm->execute( $params );
	}
	
	protected function put(
		$table,
		$params = array(),
		$db	= ''
	) {
		$stm	= 
		self::getDb( $db )->prepare(
			static::insertSql( $table, $params )
		);
		
		$stm->execute( static::parseParams( $params ) );
		
		return $db->lastInsertId(); 
	}
	
	protected function edit(
		$table,
		$id,
		$params		= array(),
		$db		= '' 
	) {
		$stm	= 
		self::getDb( $db )->prepare(
			static::updateSql( $table, $params, 'id = :id' )
		);
		
		return $stm->execute( static::parseParams( $params ) );
	}
	
	protected function setIf( &$object, $params = array() ) {
		foreach ( $params as $k => $v ) {
			$object->{$k} = $v;
		}
	}
	
	/**
	 * For each property set, return its name and value as an array
	 * 
	 * @param object $object Class to check
	 * @param array $params Properites to check
	 * 
	 * @return array Property names and matching values
	 */
	protected function ifIsset( $object, $params = array() ) {
		$data	= array();
		foreach ( $params as $value ) {
			if ( isset( $object->{$value} ) ) {
				$data[$value] = $object->{$value};
			}
		}
		
		return $data;
	}
	
	/**
	 * Convert a unix timestamp a datetime-friendly timestamp
	 * 
	 * @param int $time Unix timestamp
	 * @return string 'Year-month-date Hour:minute:second' format
	 */
	public static function myTime( $time ) {
		return gmdate( 'Y-m-d H:i:s', $time );
	}
	
	/**
	 * Extract the username and password from the DSN and rebuild
	 */
	private static function dsn(
		$dsn,
		&$username = null,
		&$password = null
	) {
		/**
		 * No host name with ':' would mean this is a DSN name 
		 * in php.ini
		 */
		if ( false === strrpos( $dsn, ':' ) ) {
			/**
			 * We need get_cfg_var() here because ini_get 
			 * doesn't work
			 * https://bugs.php.net/bug.php?id=54276
			 */
			$dsn = get_cfg_var( "php.dsn.$dsn" );
		}
		
		/**
		 * Some people use spaces to separate parameters in
		 * DSN strings and this is NOT standard
		 */
		$d = explode( ';', $dsn );
		$m = count( $d );
		$s = '';
		
		for ( $i = 0; $i < $m; $i++ ) {
			$n = explode( '=', $d[$i] );
			
			// Empty parameter? Continue
			if ( count( $n ) <= 1 ) {
				$s .= implode( '', $n ) . ';';
				continue;
			}
			
			switch( trim( $n[0] ) ) {
				case 'uid':
				case 'user':
				case 'username':
					$username = trim( $n[1] );
					break;
				
				case 'pwd':
				case 'pass':
				case 'password':
					$password = trim( $n[1] );
					break;
			
				default: 
				// Some other parameter? Leave as-is
					$s .= implode( '=', $n ) . ';';
			}
		}
		
		return $s;
	}
	
	/**
	 * Gets the database type from the dsn
	 * Useful for database specific SQL.
	 * Expand as necessary.
	 */
	private static function dbType( $dsn ) {
		if ( 0 === strpos( $dsn, 'mysql' ) ) {
			return 'mysql';
		} elseif ( 0 === strpos( $dsn, 'postgres' ) ) {
			return 'postgres';
		} elseif ( 0 === strpos( $dsn, 'sqlite' ) ) {
			return 'sqlite';
		}
		return 'other';
	}
}
