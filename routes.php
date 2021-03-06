<?php

# Example routes
# Change this to suit your own blog structure


# Placeholder markers (doubles as variable names passed to routes)
$markers	= 
array(
	'*'	=> '(?<all>.+?)',
	':id'	=> '(?<id>[1-9][0-9]*)',
	':tag'	=> '(?<tag>[\pL\pN\s_\,-]{3,30})',
	':cat'	=> '(?<cat>[\pL\pN]{3,20})',
	':user'	=> '(?<user>[\pL\pN\s-]{2,30})',
	':page'	=> '(?<page>[1-9][0-9]*)',
	':year'	=> '(?<year>[2][0-9]{3})',
	':month'=> '(?<month>[0-3][0-9]{1})',
	':day'	=> '(?<day>[0-9][0-9]{1})',
	':slug'	=> '(?<slug>[\pL\-\d]*)',
	':taxo'	=> '(?<taxo>blogs|boards|threads|forumposts|wiki|tags)',
	':file'	=> '(?<file>[\pL_\-\d\.\s]{1,120})'
);

# Create router with current request
$router		= new Blog\Core\Router( $request, $sender );


# Index routes
$router->add( 'get', '', array( '\\Blog\\Routes\\ContentRoute', 'index' ) );
$router->add( 'get', 'page:page', array( '\\Blog\\Routes\\ContentRoute', 'index' ) );


# Archives
$router->add( 'get', 'archive/:year', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

$router->add( 'get', 'archive/:year/:month', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/:month/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

$router->add( 'get', 'archive/:year/:month/:day', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );
$router->add( 'get', 'archive/:year/:month/:day/page:page', array( '\\Blog\\Routes\\ContentRoute', 'archive' ) );

# Read by post id
$router->add( 'get', 'read/:id', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );
$router->add( 'get', 'read/:id/page:page', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );

# Read by date and slug (if duplicates are found, show oldest first)
$router->add( 'get', 'read/:year/:month/:day/:slug', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );
$router->add( 'get', 'read/:year/:month/:day/:slug/page:page', array( '\\Blog\\Routes\\ContentRoute', 'read' ) );


# View created posts
$router->add( 'get', 'manage/posts', array( '\\Blog\\Routes\\ContentRoute', 'viewPosts' ) );
$router->add( 'get', 'manage/posts/page:page', array( '\\Blog\\Routes\\ContentRoute', 'viewPosts' ) );
Blog\Routes\Route::addSecureRoute( 'manage/posts' ); # Authorized users may enter

# Create post
$router->add( 'get', 'manage/new', array( '\\Blog\\Routes\\ContentRoute', 'creatingPost' ) );
# Create reply
$router->add( 'get', 'manage/new/:id', array( '\\Blog\\Routes\\ContentRoute', 'creatingPost' ) );
$router->add( 'post', 'manage/new', array( '\\Blog\\Routes\\ContentRoute', 'createPost' ) );
Blog\Routes\Route::addSecureRoute( 'manage/new' ); 

# Edit post
$router->add( 'get', 'manage/edit/:id', array( '\\Blog\\Routes\\ContentRoute', 'editingPost' ) );
$router->add( 'post', 'manage/edit/:id', array( '\\Blog\\Routes\\ContentRoute', 'editPost' ) );
Blog\Routes\Route::addSecureRoute( 'manage/edit' );

# Delete post
$router->add( 'get', 'manage/delete/:id', array( '\\Blog\\Routes\\ContentRoute', 'deletingPost' ) );
$router->add( 'post', 'manage/delete/:id', array( '\\Blog\\Routes\\ContentRoute', 'deletePost' ) );
Blog\Routes\Route::addSecureRoute( 'manage/delete' );


# User login
$router->add( 'get', 'manage/login', array( '\\Blog\\Routes\\UserRoute', 'loggingIn' ) );
$router->add( 'post', 'manage/login', array( '\\Blog\\Routes\\UserRoute', 'login' ) );
Blog\Routes\Route::setLoginRoute( 'manage/login' );

# User register
$router->add( 'get', 'manage/register', array( '\\Blog\\Routes\\UserRoute', 'registering' ) );
$router->add( 'post', 'manage/register', array( '\\Blog\\Routes\\UserRoute', 'register' ) );
Blog\Routes\Route::setRegisterRoute( 'manage/register' );

# User logout
$router->add( 'get', 'manage/logout', array( '\\Blog\\Routes\\UserRoute', 'logout' ) );
Blog\Routes\Route::setLogoutRoute( 'manage/logout' );

# User Profile
$router->add( 'get', 'manage/profile', array( '\\Blog\\Routes\\UserRoute', 'profileView' ) );
$router->add( 'post', 'manage/profile', array( '\\Blog\\Routes\\UserRoute', 'profileChanged' ) );
Blog\Routes\Route::addSecureRoute( 'manage/profile' );

# User password changed
$router->add( 'post', 'manage/changepass', array( '\\Blog\\Routes\\UserRoute', 'passChanged' ) );
Blog\Routes\Route::addSecureRoute( 'manage/changepass' );

# User deletion
$router->add( 'get', 'manage/deleteuser', array( '\\Blog\\Routes\\UserRoute', 'deleteView' ) );
$router->add( 'post', 'manage/deleteuser', array( '\\Blog\\Routes\\UserRoute', 'delete' ) );
Blog\Routes\Route::addSecureRoute( 'manage/deleteuser' );

# CAPTCHA routes
$router->add( 'get', 'captcha/:slug', array( '\\Blog\\Routes\\CaptchaRoute', 'generate' ) );
$router->add( 'post', '*', array( '\\Blog\\Routes\\CaptchaRoute', 'validate' ) );

# Send the route
$router->route( $markers );

