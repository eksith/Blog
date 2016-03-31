<?php
	
# http://us2.php.net/manual/en/features.file-upload.php#114004
# http://us2.php.net/manual/en/features.file-upload.put-method.php
# http://us2.php.net/manual/en/features.file-upload.multiple.php
# https://secure.php.net/manual/en/function.stream-copy-to-stream.php

namespace Blog\Messaging\File;

class UploadedFile extends Immutable 
	implements \Psr\Http\Message\UploadedFileInterface {
	
	const CHUNK_SIZE	= 8192;
	
	private $name;
	private $type;
	private $tmp_name;
	private $size;
	private $error;
	
	private $via_post	= true;
	
	private $stream;
	
	public function __construct(
		$name,
		$type,
		$tmp_name,
		$size,
		$error
	) {
		$this->name	= $this->filterUpName( $name );
		$this->type	= $type;
		$this->tmp_name	= $tmp_name;
		$this->size	= $size;
		$this->error	= $error;
		
		if ( 'put' == 
			strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
			$this->via_post = false;
		}
	}
	
	public function getStream() {
		# TODO
	}
	
	public function dupMoveTo( $targetPath ) {
		$name = $this->dupRename( 
				$targetPath . $this->name, $mod
			);
		if ( $this->via_post ) {
			if ( $this->error == \UPLOAD_ERR_OK ) {
				\move_uploaded_file(
					$this->tmp_name,
					$name
				);
			}
		} else {
			# TODO
		}
	}
	
	public function moveTo( $targetPath ) {
		$name	= $targetPath . $this->name;
		if ( $this->via_post ) {
			if ( $this->error == \UPLOAD_ERR_OK ) {
				\move_uploaded_file(
					$this->tmp_name,
					$name
				);
			}
		} else {
			# TODO
		}
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function getClientFilename() {
		return $this->size;
	}
	
	public function getClientMediaType() {
		return $this->type;
	}
	
	private function readStream() {
		# TODO
	}

	/**
	 * Rename file to prevent overwriting existing ones by 
	 * appending _i where 'i' is incremented by 1 until no 
	 * more files with the same name are found
	 */
	function dupRename( $up, &$mod = '' ) {
		$info	= pathinfo( $up );
		$ext	= $info['extension'];
		$name	= $info['basename'];
		$dir	= $info['dirname'];
		$file	= $up;
		$i	= 0;
		
		while ( file_exists( $file ) ) {
			$mod	= $name . '_' . $i++ . '.' . $ext;
			$file	= $dir . \DIRECTORY_SEPARATOR . $mod;
		}
		
		return $file;
	}

	/**
	 * Filter upload file name into a safe format
	 */
	private function filterUpName( $name ) {
		if ( empty( $name ) ) {
			return '_';
		}
		
		$name	= preg_replace('/[^\pL_\-\d\.\s]', ' ' );
		return preg_replace( '/\s+/', '-', trim( $name ) );
	}
}
