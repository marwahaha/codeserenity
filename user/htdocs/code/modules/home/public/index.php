<?php

$arr = $cs->dbarray('pages', array(
	'orderby' => 'position ASC'
));

for ($i = 0, $l = count($arr); $i< $l; $i++) {
	$arr[$i]['content'] = str_replace(array('\\"', "\\'"), array('"', "'") , $arr[$i]['content']);
}

$cms->page->set('result', $arr);

?>