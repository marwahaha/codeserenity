<?php

// check for require variable
if (isset($cms->module->vars[2])) {
	
	// grab id
	$id = $cms->module->vars[2];
	
	// set module template to use
	$cms->module->set_template('images_resize');
	
	// grab the image data from the database
	$arr = $cs->dbarray('images', array(
		'where' => 'id='.$id
	));
	
	// set module item
	$cms->module->item = $arr[0];
	
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
		'label' => isset($cms->module->lang['action_resize']) ? $cms->module->lang['action_resize'].' '.$label : $cms->language->str('action_resize').' '.$label,
		'name' => 'resize',
		'url' => $cms->module->path.'/resize'
	);
}

?>