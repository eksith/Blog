<?php

namespace Blog\Handlers;

class Captcha extends Handler {
	
	const C_HEIGHT	= 50;
	const C_WIDTH	= 19;
	const C_PAD	= 10;
	const C_BUFFER	= -13;
	
	const C_FONT	= 'monofont.ttf';
	const C_SIZE	= 30;			# Font size
	const C_ANG_MIN	= -10;			# Min letter tilt angle
	const C_ANG_MAX	= 10;			# Max letter tilt angle
	const C_RGB_MIN	= 0;			# Letter color RGB min value
	const C_RGB_MAX	= 150;			# Letter color RGB max value
	
	const C_PX_MIN	= 18;			# Letter X position min
	const C_PX_MAX	= 19;			# Letter X position max
	const C_PY_MIN	= 30;			# Letter Y position min
	const C_PY_MAX	= 40;			# Letter Y position max
	
	
	const L_RGB_DIV	= 250;			# Line spread divisor
	const L_RGB_MIN	= 150;			# Line color RGB min value
	const L_RGB_MAX	= 200;			# Line color RGB max value
	
	public function generate( Events\Event $event ) {
		if ( headers_sent() ) {
			return;
		}
		
		$captcha = $event->get( 'captcha' );
		if ( empty( $captcha ) ) {
			return;
		}
		
		\putenv( 'GDFONTPATH=' . \realpath( '.' ) );
		
		image( $captcha );
	}
	
	public function validate( Events\Event $event ) {
		$captcha = $event->get( 'captcha' );
		
		if ( !empty( $captcha ) ) {
			$sent = 
			filter_input( 
				\INPUT_POST,
				'captcha', 
				\FILTER_SANITIZE_SPECIAL_CHARS 
			);
			
			if ( \hash_equals( $sent, $captcha ) ) {
				$event->set( 'captcha_val', true );
			}
		}
		
		$event->set( 'captcha_val', false );
	}
	
	private function image( $txt ) {
		$sizey	= self::C_HEIGHT;
		$cl	= strlen( $txt );
		
		# Expand to character size
		$sizex	= ( $cl * self::C_WIDTH ) + self::C_PAD;
		$w	= floor( $sizex / $cl ) + self::C_BUFFER;
		
		$img	= \imagecreatetruecolor( $sizex, $sizey );
		$bg	= \imagecolorallocate( $img, 255, 255, 255 );
		\imagefilledrectangle( $img, 0, 0, $sizex, $sizey, $bg );
		
		$d = ( $sizex * $sizey ) / self::L_RGB_DIV;
		
		# Random lines
		for ( $i = 0; $i < $d; $i++ ) {
			$r = rand( self::L_RGB_MIN, self::L_RGB_MAX );
			$g = rand( self::L_RGB_MIN, self::L_RGB_MAX );
			$b = rand( self::L_RGB_MIN, self::L_RGB_MAX );
			
			$t = \imagecolorallocate( $img, $r, $g, $b );
			\imageline( 
				$img, 
				mt_rand( 0, $sizex ), 
				mt_rand( 0, $sizey ), 
				mt_rand( 0, $sizex ), 
				mt_rand( 0, $sizey ), 
				$t 
			);
		}
		
		# Insert text with random colors and placement
		for ( $i = $cl; $i >= 0; $i-- ) {
			$l = substr( $txt, $i, 1 );
			
			$r = rand( self::C_RGB_MIN, self::C_RGB_MAX );
			$g = rand( self::C_RGB_MIN, self::C_RGB_MAX );
			$b = rand( self::C_RGB_MIN, self::C_RGB_MAX );
			$a = rand( self::C_ANG_MIN, self::C_ANG_MAX );
			
			$x = $w + ( $i * rand( 
					self::C_PX_MIN, self::C_PX_MAN 
				) );
				
			$y = rand( C_PY_MIN, C_PY_MAX );
			
			$t = \imagecolorallocate( $img, $r, $g, $b );
			\imagettftext( 
				$img, self::C_SIZE, $a, $x, 
				$y, $t, self::C_FONT
			);
		}
		
		header( 'Content-type: image/png' );
		
		imagepng( $img );
		imagedestroy( $img );
	}
}
