<?php

// check for require variable
if (isset($cms->module->vars[2])) {
	
	// grab id
	$id = $cms->module->vars[2];
	
	// set page content
	$page_content = $cs->dbform($cms->module->name, array(
			'primarykey' => $id,
			'success_url' => $cms->module->path,
			'lang' => $cms->module->get_language_array($cms->module->name)
		)
	);
	
	// WYSIWYG interface for the module (if needed)
	$cms->module->wysiwyg();
	
	$cms->addjs('mod_form', true);
	
	// grab the record
	$arr = $cs->dbarray($cms->module->name, array('where' => 'id='.$id));
	
	// if there is a list attribute build page title based on the first column in there
	if (isset($cms->module->config['admin'][0]['list'])) {
		$list = explode(',', $cms->module->config['admin'][0]['list']);
		foreach ($list as $v) {
			if (isset($arr[0][$v]) && 'position' != $v) {
				$page_title .= ' '.$arr[0][$v];
				$label = $arr[0][$v];
				break;
			}
		}
	}
	
	// build crumbs bit
	$cms->crumbs[] = array(
		'depth' => count($cms->crumbs)-1,
		'label' => isset($cms->module->lang['action_edit']) ? $cms->module->lang['action_edit'].' '.$label : $cms->language->str('action_edit').' '.$label,
		'name' => 'edit',
		'url' => $cms->module->path.'/edit'
	);
	
	// set page content
	$cms->page->set('content', $page_content);
}

?>