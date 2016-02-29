<?
namespace Blog\Messaging;

class Response extends Message 
	implements \Psr\Http\Message\ResponseInterface {
	
	protected $status;
	
	protected $reason;
	
	const MAX_REASON	= 100;
	
	protected static $responses = array(
		100 => 'Continue',				// https://tools.ietf.org/html/rfc7231#section-6.2.1
		101 => 'Switching Protocols',			// https://tools.ietf.org/html/rfc7231#section-6.2.2
		102 => 'Processing',				// https://tools.ietf.org/html/rfc2518#section-10.1
		200 => 'OK',					// https://tools.ietf.org/html/rfc7231#section-6.3.1
		201 => 'Created',				// https://tools.ietf.org/html/rfc7231#section-6.3.2
		202 => 'Accepted',				// https://tools.ietf.org/html/rfc7231#section-6.3.3
		203 => 'Non-Authoritative Information',		// https://tools.ietf.org/html/rfc7231#section-6.3.4
		204 => 'No Content',				// https://tools.ietf.org/html/rfc7231#section-6.3.5
		205 => 'Reset Content',				// https://tools.ietf.org/html/rfc7231#section-6.3.6
		206 => 'Partial Content',			// https://tools.ietf.org/html/rfc7233#section-4.1
		207 => 'Multi-status',				// https://tools.ietf.org/html/rfc4918#section-11.1
		208 => 'Already Reported',			// https://tools.ietf.org/html/rfc5842#section-7.1
		300 => 'Multiple Choices',			// https://tools.ietf.org/html/rfc7231#section-6.4.1
		301 => 'Moved Permanently',			// https://tools.ietf.org/html/rfc7231#section-6.4.2
		302 => 'Found',					// https://tools.ietf.org/html/rfc7231#section-6.4.3
		303 => 'See Other',				// https://tools.ietf.org/html/rfc7231#section-6.4.4
		304 => 'Not Modified',				// https://tools.ietf.org/html/rfc7232#section-4.1
		305 => 'Use Proxy',				// https://tools.ietf.org/html/rfc7231#section-6.4.5			
		306 => 'Switch Proxy',				// https://tools.ietf.org/html/draft-cohen-http-305-306-responses-00
		307 => 'Temporary Redirect',			// https://tools.ietf.org/html/rfc7231#section-6.4.7
		400 => 'Bad Request',				// https://tools.ietf.org/html/rfc7231#section-6.4.7
		401 => 'Unauthorized',				// https://tools.ietf.org/html/rfc7235#section-3.1
		402 => 'Payment Required',			// https://tools.ietf.org/html/rfc7231#section-6.5.2
		403 => 'Forbidden',				// https://tools.ietf.org/html/rfc7231#section-6.5.3
		404 => 'Not Found',				// https://tools.ietf.org/html/rfc7231#section-6.5.4
		405 => 'Method Not Allowed',			// https://tools.ietf.org/html/rfc7231#section-6.5.5
		406 => 'Not Acceptable',			// https://tools.ietf.org/html/rfc7231#section-6.5.6
		407 => 'Proxy Authentication Required',		// https://tools.ietf.org/html/rfc7235#section-3.2
		408 => 'Request Time-out',			// https://tools.ietf.org/html/rfc7231#section-6.5.7
		409 => 'Conflict',				// https://tools.ietf.org/html/rfc7231#section-6.5.8
		410 => 'Gone',					// https://tools.ietf.org/html/rfc7231#section-6.5.9
		411 => 'Length Required',			// https://tools.ietf.org/html/rfc7231#section-6.5.10
		412 => 'Precondition Failed',			// https://tools.ietf.org/html/rfc7232#section-4.2
		413 => 'Request Entity Too Large',		// https://tools.ietf.org/html/rfc7231#section-6.5.11
		414 => 'Request-URI Too Large',			// https://tools.ietf.org/html/rfc7231#section-6.5.12
		415 => 'Unsupported Media Type',		// https://tools.ietf.org/html/rfc7231#section-6.5.13
		416 => 'Requested range not satisfiable',	// https://tools.ietf.org/html/rfc7233#section-4.4
		417 => 'Expectation Failed',			// https://tools.ietf.org/html/rfc7231#section-6.5.14
		418 => 'I\'m a teapot',				// https://tools.ietf.org/html/rfc2324#section-2.3.2
		422 => 'Unprocessable Entity',			// https://tools.ietf.org/html/rfc4918#section-11.2
		423 => 'Locked',				// https://tools.ietf.org/html/rfc4918#section-11.3
		424 => 'Failed Dependency',			// https://tools.ietf.org/html/rfc5689
		425 => 'Unordered Collection',			// https://tools.ietf.org/html/draft-ietf-webdav-collection-protocol-04#section-7.2
		426 => 'Upgrade Required',			// https://tools.ietf.org/html/rfc7231#section-6.5.15
		428 => 'Precondition Required',			// https://tools.ietf.org/html/rfc6585#section-3
		429 => 'Too Many Requests',			// https://tools.ietf.org/html/rfc6585#section-4
		431 => 'Request Header Fields Too Large',	// https://tools.ietf.org/html/rfc6585#section-5
		451 => 'Unavailable For Legal Reasons',		// https://tools.ietf.org/html/draft-tbray-http-legally-restricted-status-05
		500 => 'Internal Server Error',			// https://tools.ietf.org/html/rfc7231#section-6.6.1
		501 => 'Not Implemented',			// https://tools.ietf.org/html/rfc7231#section-6.6.2
		502 => 'Bad Gateway',				// https://tools.ietf.org/html/rfc7231#section-6.6.3
		503 => 'Service Unavailable',			// https://tools.ietf.org/html/rfc7231#section-6.6.4
		504 => 'Gateway Time-out',			// https://tools.ietf.org/html/rfc7231#section-6.6.5
		505 => 'HTTP Version not supported',		// https://tools.ietf.org/html/rfc7231#section-6.6.6
		506 => 'Variant Also Negotiates',		// https://tools.ietf.org/html/rfc2295#section-8.1
		507 => 'Insufficient Storage',			// https://tools.ietf.org/html/rfc4918#section-11.5
		508 => 'Loop Detected',				// https://tools.ietf.org/html/rfc5842#section-7.2
		510 => 'Not Extended',				// https://tools.ietf.org/html/rfc2774#section-7
		511 => 'Network Authentication Required'	// https://tools.ietf.org/html/rfc6585#section-6
	);
	
	public function __construct(
		$status		= 200,
		array $headers	= array(),
		$body		= null,
		$protocol	= '1.1'
	) {
		parent::__construct( $body, $protocol, $headers );
		$this->status	= $status;
		$this->setReason( $status );
	}
	
	public function getStatusCode() {
		return $this->status;
	}
	
	public function getReasonPhrase() {
		return $this->reason;
	}
	
	public function withStatus( $code, $reasonPhrase = '' ) {
		$new = static::immu( $this, 'status', $code );
		$new->setReason( $code, $reasonPhrase );
		return $new;
	}
	
	protected function setReason( $code, $reasonPhrase = '' ) {
		if ( !empty( $reasonPhrase ) ) {
			$reasonPhrase = trim( $reasonPhrase );
			if (
				mb_strlen( $reasonPhrase, '8bit' ) > 
				self::MAX_REASON
			) {
				$reasonPhrase = '';
			}
		}
		
		if ( isset( self::$responses[$this->status] ) ) {
			$reason = empty( $reasonPhrase ) ? 
					self::$responses[$this->status] : 
					( string ) $reasonPhrase;
		}
		
		$this->reason = $reason;
	}
}