<?php

// temporary stuff linked up to database
if (isset($_POST['login'])) {
	
	// query the database
	$arr = $cs->dbarray('users', array(
		'where' => 'username = '.$cs->dbstring(trim($_POST['username'])).' AND password = "'.$cs->encrypt($_POST['password']).'" AND group_id = 1'
	));
	
	// if valid we log the person in
	if (count($arr) === 1) {
		
		// set come session variables
		$_SESSION['admin'] = true;
		$_SESSION['username'] = $arr[0]['username'];
		$_SESSION['user_data'] = $arr[0];
		
		// update the logged field
		$cs->dbupdate('users', array('logged' => $cs->dbdate_now()), $arr[0]['id']);
		
		// redirect
		$cs->redirect($cs->paths['url']);
	}
}

// login out?
if (isset($cms->module->vars[0]) && 'out' == $cms->module->vars[0]) {
	session_destroy();
	$cs->redirect('/admin/overview');
}

// set page title
$cms->page->set('title', $cms->language->str('title_users_login'));
$cms->crumbs = array();

?>