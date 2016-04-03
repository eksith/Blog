<?php

define( 'PATH',			\realpath( \dirname( __FILE__ ) ) . '/' );
define( 'PKGS',			PATH . 'vendor/' );



# Use the |PATH| placeholder if the relevant folder/resource 
# is relative to this config file. It's recommended that the databases 
# and upload directories be placed outside the web root
define( 'CONFIG',		PATH . 'data/blog.conf' );

\set_include_path( \get_include_path() . PATH_SEPARATOR . PKGS );
\spl_autoload_extensions( '.php' );
\spl_autoload_register( function( $class ) {
	\spl_autoload( str_replace( "\\", "/", $class ) );
});


# Prepare crypto
$crypto		= new Blog\Core\Crypto();

# Prepare configuration
$config		= new Blog\Core\Config( $crypto );

# Prepare base model
Blog\Models\Model::setConfig( $config );
Blog\Models\Model::setCrypto( $crypto );

# Prepare request
$request	= new Blog\Messaging\ServerRequest();

# Event dispatcher
$sender		= new Blog\Events\Dispatcher(
			$request, $config, $crypto
		);

# Register firewall plugin before any others
Blog\Events\Pluggable::register( new Blog\Plugins\Security\Plugin() );

# Register any extra plugins 
# Blog\Events\Pluggable::register( new Blog\Plugins\Example\Plugin() );



/*

. ／l、
（ﾟ､ ｡ ７
l、ﾞ ~ヽ
じしf_, )ノ

*/

