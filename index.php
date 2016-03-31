<?php

# Execution start (from request time)
define( 'START',		isset( $_SERVER['REQUEST_TIME'] ) ? 
					$_SERVER['REQUEST_TIME'] : 
					microtime( true ) );

# The following files should ideally be placed outside the web root
require( 'shims.php' );
require( 'config.php' );
require( 'routes.php' );

# Uncomment to test execution time
# echo 'Ran in ' . round( microtime( true ) - START, 5 ) . ' seconds';
