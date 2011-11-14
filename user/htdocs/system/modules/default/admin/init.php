<?php

// set default page title based on the module name
$page_title = ucwords(str_replace("_"," ",$cms->module->name));

// sort out which file to include
if (!isset($cms->module->vars[0])) $inc = 'index';
else if ($cms->module->vars[0] == 'action' && isset($cms->module->vars[1])) {
	
	// set include file
	$inc = $cms->module->vars[1];
	
	// update page title string
	$page_title = '<a href="'.$cms->module->path.'">'.$page_title.'</a>';
	$page_title .= ' >> '.$cms->language->str('action_'.$cms->module->vars[1]);
	
	// set back button
	$cms->page->set('back_button', array(
		'label' => $cms->language->str('back_to'),
		'url' => $cms->module->path
	));
}

// call the requested file
require $cms->module->getfile($inc);

// set default page title based on the module name
$cms->page->set("title", $page_title);

?>