<?php

define( 'PATH',			\realpath( \dirname( __FILE__ ) ) . '/' );
define( 'PKGS',			PATH . 'vendor/' );



# Use the |PATH| placeholder if the relevant folder/resource 
# is relative to this config file. It's recommended that the databases 
# and upload directories be placed outside the web root
define( 'CONFIG', <<<JSON
{
	"version"		: "0.01",
	
	"blog_name"		: "Blog",
	"blog_tagline"		: "This is my blog. There are many like it, but this one is mine",
	
	"content_store"		: "sqlite:data\/content.sqlite",
	"session_store"		: "sqlite:data\/sessions.sqlite",
	"cache_store"		: "sqlite:data\/cache.sqlite",
	"firewall_store"	: "sqlite:data\/firewall.sqlite",
	
	"media_path"		: "|PATH|data\/media\/",
	"archive_path"		: "|PATH|data\/archive\/",
	"compiled_tpl_path"	: "|PATH|data\/templates\/",
	
	"timezone"		: "America\/New_York",
	
	"theme_default"		: "default",
	"theme_admin"		: "admin",
	"theme_path"		: "|PATH|themes\/",
	"theme_display"		: "\/themes\/",
	"theme_include_limit"	: 40,
	"theme_compile_hash"	: "sha256",
	
	"enable_register"	: true,
	"enable_login"		: true,
	"show_fullbody"		: true,
	
	"nav_main"		: {
					"Home"		: "\/",
					"Archive"	: "\/archive",
					"Account"	: "\/manage/login"
				},
	
	"nav_manage"		: {
					"Home"		: "\/",
					"New"		: "\/manage/new",
					"Posts"		: "\/manage/posts",
					"Profile"	: "\/manage/profile",
					"Logout"	: "\/manage/logout"
				},
	
	"nav_login"		: {
					"Home"		: "\/",
					"New"		: "\/manage/new",
					"Posts"		: "\/manage/posts",
					"Profile"	: "\/manage/profile",
					"Account"	: "\/manage/login"
				},
	
	"firewall_ua"		: "|PATH|data\/ua.ini",
	"firewall_uri"		: "|PATH|data\/uri.ini",
	"firewall_ip"		: "|PATH|data\/ip.ini",
	"firewall_bots"		: "|PATH|data\/bots.ini",
	"firewall_hosts"	: true,
	"firewall_local"	: true,
	
	"list_per_page"		: 20,
	"posts_per_page"	: 15,
	"data_timeout"		: 10,
	
	"date_format"		: "M, d h:i",
	"date_nice"		: "l, M d, Y",
	
	"language_files"	: "|PATH|vendor\/Blog\/Language\/",
	
	"cache_hash"		: "tiger192,4",
	"cache_time"		: 3600,
	
	"cookie_name"		: "site",
	"cookie_secure"		: false,
	"cookie_hash"		: "tiger160,4",
	"cookie_rounds"		: 1000,
	"cookie_salt"		: 16,
	"cookie_time"		: 2592000,
	"cookie_path"		: "\/",
	
	"session_hash"		: "tiger160,4",
	"sesssion_key"		: 12,
	"session_time"		: 1200,
	
	"visit_key"		: 6,
	
	"user_hash"		: "ripemd128",
	"user_hash_size"	: 32,
	
	"field_hash"		: "tiger128,3",
	"signature_hash"	: "tiger160,4",
	
	"csrf_hash"		: "tiger128,3",
	"csrf_size"		: 16,
	"csrf_salt"		: 4,
	"csrf_rounds"		: 100,
	
	"post_status_buried"	: -1,
	"post_status_open"	: 0,
	"post_status_fopen"	: 1,
	"post_status_noanon"	: 2,
	"post_status_fnoanon"	: 3,
	"post_status_closed"	: 4,
	"post_status_fclosed"	: 5,
	
	"user_status_buried"	: -1,
	"user_status_regular"	: 0,
	"user_status_sub"	: 97,
	"user_status_mod"	: 98,
	"user_status_admin"	: 99,
	
	"csp_header"		: {
		"base-uri"			: [],
		"default-src"			: [],
		"connect-uri"			: [],
		"frame-ancestors"		: [],
		"img-src"			: { "self" : true, "data" : true },
		"media-src"			: [],
		"script-src"			: { "self" : true },
		"form-action"			: { "allow": [], "self" : true },
		"upgrade-insecure-requests"	: true
	}
}
JSON
);

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

