<?php

$cms->page->set('result', $cs->dbarray('pages', array(
	'orderby' => 'position ASC'
)));

?>