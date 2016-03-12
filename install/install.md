# Blog is a blog. It lets you blog.

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
selecting data or including sub-template files.

Content may be entered via the WYSIWYG which is already formatted into  
HTML or via the markup editor which takes content in HTML or in  
[Markdown](http://daringfireball.net/projects/markdown/) format.

Mardown formatting is accomplished thanks to 
[Parsedown](https://github.com/erusev/parsedown).

### Configuration

All application settings are stored as a single JSON string in config.php  
under the CONFIG variable. All backslashes should be escaped with a   
forward slash '\' to avoid parsing errors.

The main options to get started are the locations to the various database  
files and the "firewall_local" setting which should be set to 'false'  
(without quotes) unless you're testing the installation on a local server.  

The database files should be located in a writable directory that is  
ideally outside the web root to prevent direct access.

The upload paths are set with "media_path", which should also be writable.  
If it is under the web root, the path should be prefixed with |PATH| to  
notify the script that it is in the root folder.

The "archive_path" setting is similar to "media_path" in that it should  
also be writable, however no user uploaded files are stored here. This  
folder is for generated static content that no longer requires database  
access.

The "compiled_tpl_path" is for storing template files that are loaded  
during execution. It is only meant to marginally speed up load times  
and may be left blank.


### Structure 

Typical execution of the front page : 

    index.php
    | - config.php ( Stores all configuration options and the class loader )
    | - routes.php ( Application URI routes sent to the router )
    |_______
    	| - Sensor.php
    	|	| - IP.php
    	|	| - BrowserProfile.php
    	|	| - Model.php
    	|	| 
    	|	| - Immutable.php
    	|	| - Uri.php ( Immutable )
    	|	| - Message.php ( Immutable )
    	|	| - Request.php ( Message )
    	|	| - ServerRequest.php ( Request )
    	|
    	| - Router.php
    		|
    		| - Route.php
    		| - ContentRoute.php ( Route )
    			| - Dispatcher.php
    			| - Event.php
    			| - Listener.php
    			|
    			| - Handler.php ( Listener )
    			| - Menu.php ( Handler )
    			| - ContentHandler.php ( Handler )
    			| 
    			| - Index.php ( ContentHandler )
    			| - Post.php ( Model )
    			| 
    			| - View.php ( Handler.php )
    			| - Index.php ( View )
    			| - Front.php ( View )

The views load the appropriate 'template.html' file from the designated  
theme folder. The "manage/" routes default to the "admin" theme.
