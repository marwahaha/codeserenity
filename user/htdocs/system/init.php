<?php

// include system constants
require 'includes/constants.php';

// database driver settings (handling this better using XML definition)
define('DB_CONFIG', 'mysql://codeserenity:*dbx69sql*@localhost/codeserenity');
define('PREFIX','devcs_');

// Call Code Serenity framework
require '/home/default/gillescochez.info/codeserenity/init.php';
$cs = new codeserenity();

// Load and start the CMS class
require CMS_INCLUDES.'class.cms.php';
$cms = new cms();
	
// Initialise the language class
$cms->load_language();

// check for CMS core tables, if not present redirect to install form
if (!$cms->installed()) require CMS_SETUP.'index.php';
else {

	// Initialise the site class
	$cms->load_site();

	// Check for a path, if none redirect as the CMS require one to work 
	if (empty($cs->path[0])) $cs->path[0] = 'home';
	
	// Initialise the page
	$cms->load_page();

	// check for module presence
	if ($cms->page->get('module') != '' || $cms->is_admin) {
	
		// Handle the module
		$cms->load_module();
		
		// Initialize the module
		require $cms->module->init_file;
	}
	
	// Initialize the menus
	if ($cms->display_menus) $cms->load_menus();

	// Handle the theme, we do this at last so the theme can be changed at any point prior the rendering of the page
	$cms->load_theme();
	
	// Render the content
	$cms->render();
}

?>