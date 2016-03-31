<?php

namespace Blog\Messaging;
use Blog\Core;

# http://www.php-fig.org/psr/psr-7/
class ServerRequest extends Request 
	implements \Psr\Http\Message\ServerRequestInterface {
		
	private $browser;
	
	private $rawsig;
	
	private $sig;
	
	private $parsed_body;
	
	private $files;
	
	private $cookie;
	
	private $params;
	
	public function __construct(
		$method		= null,
		$uri		= null,
		$body		= null,
		array $headers	= array(),
		$protocol	= null
	) {
		$this->signature();
		
		if ( empty( $body ) ) {
			$body		= $this->getInput();
		}
		if ( empty( $headers ) ) {
			$headers	= $this->browser->headers();
		}
		if ( empty( $method ) ) {
			$method		= $_SERVER['REQUEST_METHOD'];
		}
		
		if ( empty( $protocol ) ) {
			$protocol	= $_SERVER['SERVER_PROTOCOL'];
		}
		
		parent::__construct(
			$method,
			$this->getAsUri( $uri ),
			$body,
			$headers,
			$protocol
		);
		$this->params = $_GET;
		$this->cookie = $_COOKIE;
	}
	
	public function getServerParams() {
		# TODO
	}
	
	public function getCookieParams() {
		return $this->cookie;
	}
	
	public function withCookieParams( array $cookie ) {
		return 
		static::immu( $this, 'cookies', $cookie );
	}
	
	public function getQueryParams() {
		return $this->params;
	}
	
	public function withQueryParams( array $query ) {
		return 
		static::immu( $this, 'params', $query );
	}
	
	public function getUploadedFiles() {
		if ( isset( $this->files ) ) {
			return $this->files;
		}
		
		if ( empty( $_FILES ) ) {
			$this->files	= $tihs->filesFromPut();
		} else {
			$this->files	= $this->filesFromArray();
		}
		
		return $this->files;
	}
	
	public function withUploadedFiles( array $uploadedFiles ) {
		# TODO
	}
	
	public function getParsedBody() {
		if ( isset( $this->parsed_body) ) {
			return $this->parsed_body;
		}
		
		return null;
	}
	
	public function withParsedBody( $data ) {
		return 
		static::immu( $this, 'parsed_body', $data );
	}
	
	public function getAttributes() {
		# TODO
	}
	
	public function getAttribute( $name, $default = null ) {
		# TODO
	}
	
	public function withAttribute( $name, $value ) {
		# TODO
	}
	
	public function withoutAttribute( $name ) {
		# TODO
	}
	
	# https://www.w3.org/TR/html401/interact/forms.html#h-17.13.4
	# https://secure.php.net/manual/en/wrappers.php
	# Detect content type and parse as application/x-www-form-urlencoded or multipart/form-data
	public function getBodyWithFilter( $filter ) {
		# TODO 
	}
	
	public function getPostWithFilter( $filter ) {
		return \filter_input_array( \INPUT_POST, $filter );
	}
	
	public function getParamsWithFilter( $filter ) {
		return \filter_input_array( \INPUT_GET, $filter );
	}
	
	public function getCookieWithFilter( $filter ) {
		return \filter_input_array( \INPUT_COOKIE, $filter );
	}
	
	public function getSignature( $raw = false ) {
		if ( $raw ) {
			return $this->rawsig;
		} 
		return $this->sig;
	}
	
	public function getBrowserProfile() {
		return $this->browser;
	}
	
	private function filesFromPut() {
		$data	= $this->getBody();
		
		if ( empty( $data ) ) {
			return array();
		}
		
		parse_str( $data, $files );
		return $files;
	}
	
	# https://php.net/manual/en/features.file-upload.php#114004
	# https://php.net/manual/en/features.file-upload.post-method.php#118858
	private function filesFromArray() {
		$files = $this->parseUploads();
		
		if ( empty( $files ) ) {
			return array();
		}
		
		$parsed = array();
		
		foreach( $files as $k => $v  ) {
			$parsed[$k] = array();
			foreach( $v as $file ) {
				$parsed[$k][] = 
					new UploadedFile(
						$file['name'],
						$file['type'],
						$file['tmp_name'],
						$file['size'],
						$file['error']
					);
			}
		}
		
		return $parsed;
	}
	
	/** 
	 * Return uploaded $_FILES array into a more sane format
	 * 
	 * https://secure.php.net/manual/en/features.file-upload.multiple.php
	 */
	private function parseUploads() {
		$files = array();
		
		foreach( $_FILES as $name => $file ) {
			if ( is_array($file['name']) ) {
				foreach ( $file['name'] as $n => $f ) {
					$files[$name][$n] = array();
					
					foreach( $file as $k => $v ) {
						$files[$name][$n][$k] = 
							$file[$k][$n];
					}
				}
			} else {
				$files[$name][] = $file;
			}
		}
		return $files;
	}
	
	# https://php.net/manual/en/function.stream-copy-to-stream.php#98119
	# https://secure.php.net/tempnam
	# https://php.net/manual/en/features.file-upload.put-method.php#99863
	private function getInput() {
		# TODO Read from input as a stream when content length isn't specified
		$body	= file_get_contents( 'php://stdin' );
		if ( empty( $body ) ) {
			if ( $this->hasHeader( 'content-length' ) ) {
				$body	= file_get_contents( 'php://input' );
			}
		}
		
		return $body;
	}
	
	
	private function signature() {
		if ( !isset( $this->browser ) ) {
			$this->browser = 
				new BrowserProfile();
		}
		
		$this->sig	= $this->browser->getSignature();
		$this->rawsig	= $this->browser->getSignature( true );
	}
}
