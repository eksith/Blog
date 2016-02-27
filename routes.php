<?php

// Example routes
// Change this to suit your own blog structure

// Prepare request and route
$request	= 
new Blog\Core\Request( 
		null,
		new Blog\Core\Uri(),
		file_get_contents( 'php://stdin' ),
		array(),
		null
	);

// Placeholder markers (doubles as variable names passed to routes)
$markers	= 
array(
	'*'	=> '(?<all>.+?)',
	':id'	=> '(?<id>[1-9][0-9]*)',
	':tag'	=> '(?<tag>[\pL\pN\s_\,-]{3,30})',
	':cat'	=> '(?<cat>[\pL\pN]{3,20})',
	':user'	=> '(?<user>[\pL\pN\s-]{2,30})',
	':page'	=> '(?<page>[1-9][0-9]*)',
	':year'	=> '(?<year>[2][0-9]{4})',
	':month'=> '(?<month>[0-3][0-9]{2})',
	':day'	=> '(?<day>[0-9][0-9]{2})',
	':slug'	=> '(?<slug>[\w]*)',
	':taxo'	=> '(?<taxo>blogs|boards|threads|forumposts|wiki|tags)'
);

// Create router with current request
$router		= new Blog\Core\Router( $request );

// Index routes
$router->add( 'get', '', array( '\\Blog\\Routes\\HomeRoute', 'index' ) );
$router->add( 'get', 'page:page', array( '\\Blog\\Routes\\HomeRoute', 'index' ) );


// Archives
$router->add( 'get', 'archive/:year', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

$router->add( 'get', 'archive/:year/:month', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/:month/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

$router->add( 'get', 'archive/:year/:month/:day', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/:month/:day/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

// Read by post id
$router->add( 'get', 'read/:id', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );
$router->add( 'get', 'read/:id/page:page', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );

// Read by date and slug (if duplicates are found, show oldest first)
$router->add( 'get', 'read/:year/:month/:day/:slug', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );
$router->add( 'get', 'read/:year/:month/:day/:slug/page:page', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );


// Create post
$router->add( 'get', 'new', array( '\\Blog\\Routes\\ContentRoute', 'creatingPost' ) );
$router->add( 'post', 'new', array( '\\Blog\\Routes\\ContentRoute', 'createPost' ) );

// Edit post
$router->add( 'get', 'edit/:id', array( '\\Blog\\Routes\\ContentRoute', 'editingPost' ) );
$router->add( 'post', 'edit/:id', array( '\\Blog\\Routes\\ContentRoute', 'editPost' ) );

// Delete post
$router->add( 'get', 'delete/:id', array( '\\Blog\\Routes\\ContentRoute', 'deletingPost' ) );
$router->add( 'post', 'delete/:id', array( '\\Blog\\Routes\\ContentRoute', 'deletePost' ) );


// User login
$router->add( 'get', 'login', array( '\\Blog\\Routes\\UserRoute', 'loggingIn' ) );
$router->add( 'post', 'login', array( '\\Blog\\Routes\\UserRoute', 'login' ) );

// User register
$router->add( 'get', 'register', array( '\\Blog\\Routes\\UserRoute', 'registering' ) );
$router->add( 'post', 'register', array( '\\Blog\\Routes\\UserRoute', 'register' ) );

// User logout
$router->add( 'get', 'logout', array( '\\Blog\\Routes\\UserRoute', 'logout' ) );

// User Profile
$router->add( 'get', 'profile', array( '\\Blog\\Routes\\UserRoute', 'profileView' ) );
$router->add( 'post', 'profile', array( '\\Blog\\Routes\\UserRoute', 'profileChanged' ) );


$router->route( $markers );

