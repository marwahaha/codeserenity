<?php

// set default page title based on the module name
$actions = array(
	'update' => true,
	'update_position' => true,
	'crop_image' => true
);

// sort out which file to include
if (isset($cs->path[2])) $inc = $cs->path[2];
else $inc = 'error';

// call the requested file
require $cms->module->getfile($inc);

// die here as the ajax response has been sent
die();

?>