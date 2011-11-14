<?php

// set content if we have an id
if (isset($cms->module->vars[2])) {
	$id = $cms->module->vars[2];
	$cs->dbdelete($cms->module->name, intval($id));
	$cs->redirect($cms->module->path);
}

?>