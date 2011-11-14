<?php

// assign the ADD form
$cms->page->set('content', $cs->dbform($cms->module->name, array(
	'lang' => $cms->module->get_language_array($cms->module->name),
	'success_url' => $cms->module->path
	)
));

// build crumbs bit
$cms->crumbs[] = array(
	'depth' => count($cms->crumbs)-1,
	'label' => isset($cms->module->lang['action_add']) ? $cms->module->lang['action_add'] : $cms->language->str('action_add'),
	'name' => 'add',
	'url' => $cms->module->path.'/add'
);
	
// WYSIWYG interface for the module (if needed)
$cms->module->wysiwyg();

?>