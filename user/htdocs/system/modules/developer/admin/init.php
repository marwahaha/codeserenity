<?php

$dir = dirname(__FILE__);
$inc = isset($cms->module->vars[1]) ? $cms->module->vars[1] : 'index';
if (file_exists($dir."/".$inc.".php")) require $dir."/".$inc.".php";
else $cms->error("module_file_missing", $dir."/".$inc.".php");

?>