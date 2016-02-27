<?php
namespace Blog\Core;

/**
 * Helper class to provide 'immu' function
 */
class Immutable {
	
	/**
	 * Creates a clone of the given class if one of its properties
	 * already exists and its value is not identical to the new one.
	 * Else, it returns the given class.
	 * 
	 * @param object $class Class object to clone or send as-is
	 * @param string $param Property name
	 * @param string $value Value to check against
	 * @param callable|null $filter Optional filter function
	 * 
	 * @return object
	 */
	protected static function immu( 
		$class, 
		$param, 
		$value,
		$filter = null
	) {
		if ( is_callable( array( $class, $filter ) ) ) {
			$value	= call_user_func_array( 
					array( $class, $filter ), 
					$value 
				);
		}
		
		if ( 0 === strcmp( $value, $class->{$param} ) ) {
			return $class;
		}
		
		$new		= clone $class;
		$new->{$param}	= $value;
		return $new;
	}
}