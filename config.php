<?php

define( 'PATH',			\realpath( \dirname( __FILE__ ) ) . '/' );
define( 'PKGS',			PATH . 'vendor/' );

// Use the |PATH| placeholder if the relevant folder/resource 
// is relative to this config file
define( 'CONFIG', <<<JSON
{
	"version"		: "0.01",
	
	"content_store"		: "sqlite:data\/content.sqlite",
	"session_store"		: "sqlite:data\/sessions.sqlite",
	"cache_store"		: "sqlite:data\/cache.sqlite",
	
	"theme_path"		: "|PATH|themes\/",
	"theme_display"		: "\/themes\/",
	"theme_include_limit"	: 40,
	"theme_compile_hash"	: "sha256",
	
	"enable_register"	: true,
	"enable_login"		: true,
	
	"list_per_page"		: 20,
	"posts_per_page"	: 15,
	"data_timeout"		: 10,
	
	"media_path"		: "|PATH|data\/media\/",
	"archive_path"		: "|PATH|data\/archive\/",
	"compiled_tpl_path"	: "|PATH|data\/templates\/",
	
	"date_format"		: "M, d h:i",
	
	"cache_hash"		: "tiger192,4",
	"cache_time"		: 3600,
	
	"cookie_name"		: "site",
	"cookie_secure"		: false,
	"cookie_hash"		: "tiger160,4",
	"cookie_rounds"		: 200,
	"cookie_salt"		: 16,
	"cookie_time"		: 2592000,
	"cookie_path"		: "\/",
	
	"session_hash"		: "tiger160,4",
	"sesssion_key"		: 12,
	"session_time"		: 20,
	
	"visit_key"		: 6,
	
	"user_hash"		: "ripemd128",
	"user_hash_size"	: 32,
	
	"field_hash"		: "tiger128,3",
	"signature_hash"	: "tiger160,4",
	
	"csrf_hash"		: "tiger128,3",
	"csrf_size"		: 16,
	"csrf_salt"		: 4,
	"csrf_rounds"		: 1000,
	
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
