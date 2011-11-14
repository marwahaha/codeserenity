<?php

// set content if we have an id
if (isset($cms->module->vars[2])) {
	$id = $cms->module->vars[2];
	$cs->dbupdate($cms->module->name, array('status'=>1), intval($id));
	$cs->redirect($cms->module->path);
}

?>