# Blog

Blog is a weblog application written in the PHP language intended for  
multi-user content management. This is still a rough sketch and very  
much work in progress.

Blog uses the following extensions which are usually bundled with PHP  
version 5.6 or greater, but may be turned off in some cases:  

 - SQLite 3	https://secure.php.net/manual/en/sqlite.installation.php
 - Tidy		https://secure.php.net/manual/en/tidy.installation.php
 - Intl		https://secure.php.net/manual/en/intl.installation.php
 - MBstring	https://secure.php.net/manual/en/mbstring.installation.php

Themes are written in plain HTML with some custom tags for rendering  
select data or including sub-template files.

Content may be entered via the WYSIWYG which is already formatted into  
HTML or via the markup editor which takes content in HTML or in  
[Markdown](http://daringfireball.net/projects/markdown/) format.

Markdown formatting is accomplished thanks to 
[Parsedown](https://github.com/erusev/parsedown).

All content is filtered to disallow `<object>`, `<script>` or other  
potentially harmful tags. If it is necessary to include these for  
informational purposes, put them inside `<code>` tags.  

### Configuration

All application settings are stored as a single JSON string in config.php  
under the CONFIG variable. All backslashes should be escaped with a   
forward slash '\' to avoid parsing errors.

The main options to get started are the locations to the various database  
files and the **"firewall_local"** setting which should be set to 'false'  
(without quotes) unless you're testing the installation on a local server. 

The database files should be located in a writable directory that is  
ideally outside the web root to prevent direct access.

**Important** : Never allow execution privileges on any of the writable  
directories: data, media_path, archive_path, compiled_tpl_path, and  
cache_path. On UNIX-like systems, chmod 644 should work fine.  

The upload paths are set with **"media_path"**, which should also be writable.  
If it is under the web root, the path should be prefixed with **|PATH|** to  
notify the script that it is in the root folder.

The **"archive_path"** setting is similar to **"media_path"** in that it should  
also be writable, however no user uploaded files are stored here. This  
folder is for generated static content that no longer requires database  
access.

The **"compiled_tpl_path"** is for storing template files that are loaded  
during execution. It is only meant to marginally speed up load times  
and may be left blank.  

Change **"theme_default"** if you're using a different theme. The themes  
should be located in the /themes folder in the root directory unless  
you choose to move it somewhere else. In that case, remember to also  
change **"theme_path"** to the new themes folder location and  
**"theme_display"** hint so your image files, CSS or javascript files will  
load without problems.  



### Structure 

Typical execution of the front page : 

```
index.php
  | - config.php ( Stores all configuration options and the class loader )
  | - routes.php ( Application URI routes sent to the router )
  |_____
	| - Blog\Core\Config.php
	| - Blog\Core\Crypto.php
	| - Blog\Core\Security\Sensor.php
	|	| - Blog\Core\Security\IP.php
	|	| - Blog\Core\Security\BrowserProfile.php
	|	| - Blog\Models\Model.php
	|	| 
	|	| - Blog\Messaging\Immutable.php
	|	| - Blog\Messaging\Uri.php ( Immutable )
	|	| - Blog\Messaging\Message.php ( Immutable )
	|	| - Blog\Messaging\Request.php ( Message )
	|	| - Blog\Messaging\ServerRequest.php ( Request )
	| 
	| - Blog\Core\Router.php
		|
		| - Blog\Routes\Route.php
		| - Blog\Routes\ContentRoute.php ( Route )
			| - Blog\Events\Dispatcher.php
			| - Blog\Events\Event.php ( SplSubject )
			| - Blog\Events\Listener.php ( SplObserver )
			|
			| - Blog\Handlers\Handler.php ( Listener )
			| - Blog\Handlers\Menu.php ( Handler )
			| - Blog\Language\Locale.php 
			| - Blog\Language\English.php ( Locale )
			| - Blog\Handlers\Content\ContentHandler.php ( Handler )
			| 
			| - Blog\Handlers\Content\Index.php ( ContentHandler )
			| - Blog\Models\Post.php ( Model )
			| 
			| - Blog\Views\View.php ( Handler.php )
			| - Blog\Views\Content\Index.php ( View )
			| - Blog\Views\Content\Front.php ( View )  

```

The views load the appropriate 'template.html' file from the designated  
theme folder. The "manage/" routes default to the "admin" theme.
