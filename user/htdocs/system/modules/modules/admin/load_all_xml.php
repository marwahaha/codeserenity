<?php

// Set title
$cms->page->set('title','Developer tools :: Load all XML definitions');

// Configuration file loading
$cfg = CMS_CODE.'config.xml';
$str = '<table class="list"><tr><th>Configuration XML definition</th><th style="width:150px;">Status</th></tr>';
$res = $cs->dbcreate_from_xml('config', CMS_CODE);
if ($res) $status = '<span style="color:darkgreen;">OK</span>';
else $status = '<span style="color:darkred;">ERROR</span>';
$str .= '<tr><td class="label">'.str_replace(CMS_ROOT,'',CMS_CODE.'config.xml').'</td><td>'.$status.'</td></tr>';
$str .= '</table>';

// Modules XML defintion files
$defs = $cms->module->get_xml_list();
$str .= '<table class="list"><tr><th>Module XML defintion</th><th style="width:150px;">Status</th></tr>';

// loop through modules folders
for ($i = 0; $i < count($defs['files']); $i++) {
	$res = $cs->dbcreate_from_xml('def', str_replace('def.xml','',$defs['files'][$i]));
	
	switch($res) {
		case 0:
			$color = 'darkred';
			$msg = $cms->language->str('error');
				break;
		case 1:
			$color = 'darkgreen';
			$msg = $cms->language->str('ok').' ('.$cms->language->str('no_dbtable').')';
				break;
		case 2:
			$color = 'darkgreen';
			$msg = $cms->language->str('ok');
				break;
	}
	
	$status = '<span style="color:'.$color.';">'.$msg.'</span>';
	$str .= '<tr><td class="label">'.str_replace(CMS_ROOT, '', $defs["files"][$i]).'</td><td>'.$status.'</td></tr>';
}

$str .= '</table>';

$cms->page->set('content', $str);

?>