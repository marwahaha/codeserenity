<?php

// make sure we have something to work with
if (isset($cms->module->vars[2]) && $cs->dbtable_exists($cms->module->vars[2]) && isset($_REQUEST['id'])) {

	$id = $_REQUEST['id'];
	$get = array();
	$status = 'error';
	$table = $cms->module->vars[2];
	$xml = $cs->dbtable_data($table);
	foreach ($_REQUEST as $k => $v) {
		if ('id' != $k && isset($xml['dbtable'][$table][$k])) $get[$k] = $v;
	}
	if (is_string($id)) {
		if ($cs->dbupdate($table, $get, intval($id))) $status = 'success';
	}
	elseif (is_array($id)) {		
		for ($i = 0, $c = count($id); $i < $c; $i++) {
			$sqldata = array();
			foreach($get as $k => $v) $sqldata[$k] = $v[$i];
			if ($cs->dbupdate($table, $sqldata, intval($id[$i]))) $status = 'success';
		}		
	}
	else $status = 'error';

} else $status = 'error';
?>
{
	"status":"<?=$status;?>",
	"message":"<?php echo $cms->language->str('ajax_update_'.$status); ?>"
}