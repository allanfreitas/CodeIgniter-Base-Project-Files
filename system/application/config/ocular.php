<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ocular
 *
 * A layout system inspired by the Rails system.
 *
 * @package		Ocular Layout Library
 * @author		Lonnie Ezell
 * @copyright	Copyright (c) 2007, Lonnie Ezell
 * @license		http://creativecommons.org/licenses/LGPL/2.1/
 * @link			http://ocular.googlecode.com
 * @version		0.20
 * @filesource
 */

// ------------------------------------------------------------------------

/*
|--------------------------------------------------------------------
| OCULAR LAYOUT LIBRARY SETTINGS
|--------------------------------------------------------------------
| This file will contain the settings necessary for the Ocular Layout
| library to function properly.
|
| Unless you want to store your views in a different location, or
| use a different default naming convention, you shouldn't need
| to edit this file.
|
*/

/*
|--------------------------------------------------------------------
| APPMODE
|--------------------------------------------------------------------
| Used for various settings, but mainly for stylesheets and 
| javascripts. Development mode will render separate calls, while
| production mode uses the assets controller to combine all of the
| different calls to one file.
| 
*/
$config['OCU_development_mode'] = true;

/*
|--------------------------------------------------------------------
| SITE NAME
|--------------------------------------------------------------------
| The name of the site. This is used for the page title, and is
| available for other's to use within the view.
|
| The site_name_divider is displayed between the page title and
| the site title. A default page title might look like:
|    Create a User | My Site
*/
$config['OCU_site_name'] = "Site Name";
$config['OCU_site_name_divider'] = " | ";
$config['OCU_site_name_placement'] = "append";

/*
|--------------------------------------------------------------------
| VIEW DIRECTORY
|--------------------------------------------------------------------
| The location of the application's views. Leave blank for:
|   /system/application/views/
|
| All other views must be located in the views directory, like so:
|   .../views/controller_name/function_name
|
*/
$config['OCU_view_dir'] = "";

/*
|--------------------------------------------------------------------
| TEMPLATE DIRECTORY
|--------------------------------------------------------------------
| The location of the application's templates. Leave blank for:
|   /system/application/views/templates/
| 
| When Ocular goes to render a template, it first checks to see
| if a template exists for the controller being called. If it doesn't,
| it renders the system default template (Defined below). So, for
| an application with a URL of http://mysite.com/friends/1 the 
| controller being called is 'friends'. Ocular would look for a 
| view in the following location (assuming default settings):
|
| /views/templates/friends.php
|
*/
$config['OCU_template_dir'] = "templates";

/*
|--------------------------------------------------------------------
| DEFAULT TEMPLATE
|--------------------------------------------------------------------
| This is the name of the default template used if no others are
| specified.
|
| NOTE: do not include an ending ".php" extension.
|
*/
$config['OCU_default_template'] = "application";

/*
|--------------------------------------------------------------------
| DEFAULT PATHS
|--------------------------------------------------------------------
| The location of the application's supporting files.
|
| Must have the trailing /.
*/
$config['OCU_stylesheet_path'] = "public/stylesheets/";
$config['OCU_javascript_path'] = "public/javascripts/";
$config['OCU_images_path'] = "public/images/";

/*
|--------------------------------------------------------------------
| DEFAULT COLLECTIONS
|--------------------------------------------------------------------
| The collections that are loaded when using the "default" value.
|
| These files are loaded in the order they are listed, so be sure
| to include any files that others may depend on fist.
*/
$config['OCU_stylesheet_default_collection'] = "reset, text, 960, application";
$config['OCU_javascript_default_collection'] = "jquery.1.4.2.min, modernizr-1.1.min, application";

$config['OCU_inline_javascript_opener'] = '$(document).ready(function(){';
$config['OCU_inline_javascript_closer'] = '});';

?>
