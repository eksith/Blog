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
	
	protected static $dbType = array();
	
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
		
		# If this is sqlite, enable Write-Ahead Logging
		if ( 'sqlite' == $type ) {
			static::$db[$name]->exec(
				'PRAGMA journal_mode = WAL;'
			);
		}
		static::$dbType[$name] = $type;
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
	
	protected static function filterFields( $fields ) {
		$f = explode( ',', $fields );
		$f = array_map( 
			function( $v ){
				return 
				preg_replace(
					'/[^a-zA-Z0-9\s\._]/', 
					'', trim( $v ) 
				);
			}, $f );
		
		return implode( ',', $f );
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
	
 	/**
	 * Composite field for multi-column aggregate data from a single table
	 * E.G. label,term1|label,term2 format
	 * 
	 * @param string $table Parent database table
	 * @param string $field Composite column name
	 * @param array $fields List of columns to aggregate from parent table
	 */
	protected static function aggregateField(
		$table, 
		$field, 
		$fields		= array(),
		$db		= 'content_store'
	) {
		$type		= static::$dbType[$db];
		
		/**
		 * If this is SQLite (I find your lack of faith, disturbing)
		 */
		if ( "sqlite" === $type ) {
			$params	= $table . '.' . 
				implode( "||','||{$table}.", $fields );
			return 
			"GROUP_CONCAT( {$params}, '|' ) AS {$field}";
		}
		
		$params = $table . '.'. 
			implode( ",',',{$table}.", $fields );
		
		/**
		 * If this is Postgres...
		 */
		if ( "postgres" === $type ) {
			return 
			"ARRAY_TO_STRING( 
				ARRAY_AGG( CONCAT( {$params} ) ), '|'
			) AS {$field}";
		}
		
		/**
		 * If not, try the MySQL method
		 */
		return 
		"GROUP_CONCAT( CONCAT( {$params} ) SEPARATOR '|' ) AS {$field}";
	}
	
	/**
	 * Converts label,term1|label,term2 format into
	 * label (term1, term2) format
	 * 
	 * @param string $labels Unformatted string from database
	 * @returns array
	 */
	protected static function parseAggregate( $labels ) {
		/**
		 * taxonomy("tags", "categories", "forum" etc...),
		 * label("computers", "programming", "tech" etc...)
		 */
		$params	= array();
		$taxos	= explode( '|', $labels );
		
		foreach( $taxos as $t ) {
			if ( empty( $t ) ) { continue; }
			
			$tx = explode( ',', $t );
			if ( empty( $tx ) ) { continue; }
			
			/**
			 * Do we have an array for this taxonomy label already?
			 * If not, create it
			 */
			if ( !isset( $params[$tx[0]] ) ) {
				$params[$tx[0]] = array();
			}
			
			if ( isset( $tx[1] ) ) {
				$params[$tx[0]][] = $tx[1];
			}
		}
		
		return $params;
	}
	
	/**
	 * SQL Query builder 
	 * 
	 * @return string SQL
	 */
	protected static function buildQuery( 
		$tables		= array(), 
		$from		= null, 
		$where		= null, 
		$extras		= ''
	) {
		$sql = 'SELECT ';
		foreach( $tables as $t => $fields ) {
			foreach( $fields as $f ) {
				$sql .= 
				is_array( $f ) ? 
					"$t.$f[0] AS $f[1], " : 
					"$t.$f AS $f, ";
			}
		}
		$sql	= rtrim( $sql, ', ' ) . $extras;
		
		if ( null !== $from ) {
			$sql .= ' FROM ' . $from;
		}
		
		if ( !empty( $where ) && is_array( $where ) ) {
			$sql .= ' WHERE ';
		
			foreach( $where as $t => $f ) {
				#foreach( $fields as $f ) {
					$sql .= "$t.$f[0] = $f[1] ";
					if ( isset( $f[2] ) ) {
						$sql .= $f[2] . ' ';
					}
				#}
			}
			$sql	= rtrim( $sql );
		} elseif ( !empty( $where ) ) {
			$sql .= rtrim( ' WHERE ' . $where );
		}
		return $sql;
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
			$result	= $stm->fetchAll();
			
		} elseif( 'object' == gettype( $class ) ) {
			$result = $stm->fetchAll( 
					\PDO::FETCH_CLASS, 
					get_class( $class )
				);
		} else {
			switch( $class ) {
				case 'class' :
					$result = 
					$stm->fetchAll( 
						\PDO::FETCH_CLASS, 
						get_called_class() 
					);
					break;
					
				case 'column':
					$result	= 
					$stmt->fetchALL( 
						\PDO::FETCH_COLUMN, 0 
					);
					break;
					
				case 'group':
					$result = 
					$stmt->fetchALL( 
						\PDO::FETCH_COLUMN | 
						\PDO::FETCH_GROUP
					);
					break;
					
				case 'row':
					$data = 
					$stmt->fetchAll( 
						\PDO::FETCH_ASSOC 
					);
					if ( count( $data ) ) {
						$result =  $data[0];
					}
					$result = array();
					
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
	
	/**
	 * Insert a new record into the specified table using the given 
	 * parameters
	 * 
	 * @param string $table Single table name
	 * @param array $params Matching column names and values
	 * 			Note: No need to add colons ':'
	 * @param string $db Optional database name override
	 * @return Last inserted ID
	 */
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
	
	/**
	 * Edit a record by given ID
	 * 
	 * @param string $table Single table name
	 * @param mixed $id Identifier ( table must have 'id' field )
	 * @param array $params Matching column names and values
	 * 			Note: No need to add colons ':'
	 * @param string $db Optional database name override
	 * @return Rows affected
	 */
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
	 * Convert text or int form datetime stamp into a UTC string
	 * 
	 * @param string|int $time Unix timestamp
	 * @return string 'Year-month-dateTHour:minute:second' format
	 */
	public static function utc( $time ) {
		if ( is_int( $time ) ) {
			return gmdate( 'y-m-dTH:i:s', $time );
		}
		return gmdate( 'y-m-dTH:i:s', strtotime( $time ) );
	}
	
	/**
	 * Convert text timestamp into a friendly display format given 
	 * in the app configuration
	 * 
	 * @param string $time Datetime stamp
	 * @return string Configuration specified format
	 */
	public static function niceDate( $time ) {
		$fmt = static::getSetting( 'date_format' );
		return gmdate( $fmt, strtotime( $time ) );
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
