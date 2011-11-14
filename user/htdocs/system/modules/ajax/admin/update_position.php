<?php

// make sure we have something to work with
if (isset($cms->module->vars[2]) && $cs->dbtable_exists($cms->module->vars[2]) && isset($_REQUEST['id'])) {
	$status = 'success';
	for ($i = 0, $c = count($_REQUEST['id']); $i < $c; $i++) {
		if ($cs->dbupdate($cms->module->vars[2], array('position'=>$_REQUEST['position'][$i]), intval($_REQUEST['id'][$i])) != true) $status = 'error';
	}
} else $status = 'error';
?>
{
	"status":"<?=$status;?>",
	"message":"<?php echo $cms->language->str('ajax_update_position_'.$status); ?>"
}