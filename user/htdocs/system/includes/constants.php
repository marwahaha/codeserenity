<?php

// Set some CMS constants for easy paths access
define('CMS_ROOT', getcwd().'/');
define('CMS_THEMES', CMS_ROOT.'themes/');
define('CMS_VAR', CMS_ROOT.'var/');
define('CMS_CODE', CMS_ROOT.'code/');

	// Code subpaths
	define('CMS_CODE_INCLUDES', CMS_CODE.'includes/');
	define('CMS_CODE_LANGUAGES', CMS_CODE.'languages/');
	define('CMS_CODE_MODULES', CMS_CODE.'modules/');
	
	//  system path
	define('CMS_SYSTEM', CMS_ROOT.'system/');

	// system subpaths
	define('CMS_ERRORS', CMS_SYSTEM.'errors/');
	define('CMS_INCLUDES', CMS_SYSTEM.'includes/');
	define('CMS_LANGUAGES', CMS_SYSTEM.'languages/');
	define('CMS_MODULES', CMS_SYSTEM.'modules/');
	define('CMS_SETUP', CMS_MODULES.'setup/');

// Some path for code serenity (check usage of this)
/*
	Need to find something better than that to set those.
	That will do for now :S
*/
define('CACHE', CMS_ROOT.'var/cache/');
define('STORE', CMS_ROOT.'var/store/');
define('ROOT', getcwd().'/');
define('WEBROOT', 'http://'.$_SERVER['SERVER_NAME'].'/');

?>