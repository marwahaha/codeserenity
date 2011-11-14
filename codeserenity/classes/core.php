<?php

/* Code Serenity v.2.0 
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity Core Class
	
	Roles:
		Lazy loader - classes are loaded when needed only
		Contain all the framework helper methods
*/


/*

	TODO:
		LOGGING:
			Add a method to handle write to the log. Would be nice to expand so that we
			have methods to be able to read, edit logs as well.
		ERROR HANDLING:
			Expand the error command so that error are stored with the core and can be accessed
			via the core object or the session. Add method to read and reset errors messages.
			Save the error message to the log file.
		WARNING HANDLING:
			Similar to the error handling but here it is for warning messages
		MESSAGE HANDLING:
			Similar to the error handling but here it is for normal messages


*/

// core class 
class codeserenity
{
	// public properties
	public $url = array();
	public $path = array();
	
	// private properties
	private $_cspaths = array();
	
	// constructor
	public function __construct()
	{		
		// make sure it is not an assets request
		if (!defined('MODE_ASSETS'))
		{
			// start a session if needed
			if('' == session_id()) session_start();
			
			// check for (db)form submission
			$this->_dbsubmit();
			
			// extract paths / deal with paths hooks
			$this->ressolve_paths();
		}
	}
	
	private function ressolve_paths() {
		
		// get URL
		$this->paths['url'] = $this->getURL();
	
		// store info about the request
		$this->url = parse_url($this->paths['url']);
		
		// sort out web paths (key = depth)
		$array = explode('/', $this->url['path']);
		$i = 0;
		
		// loop through array (cleaning up)
		foreach ($array as $key => $value) {
		
			// make sure there is a value
			if (!empty($value)) {
			
				// sotre the path
				$this->path[$i] = $value;
				
				/*
					Here check for special path to serve various file from the public folder.
					This is so stuff like javascript libraries and the rest can be accessible easily
					no need to upload it to your project. This can be extended so those file are hosted on
					another domain ir order to increase page loading.
					
					Format??
					
					/__public/js/jquery-1.4.2
					__public/css/reset
					
					(with options after as path bits)
					
					options to force selection of an encryption
					/__public/js/jquery-1.4.2/source
					/__public/js/jquery-1.4.2/min
					/__public/js/jquery-1.4.2/gzip
					
				
				*/
				
				// increase key
				$i++;
			}			
		}
	}
	
	public function getURL() { 
		
		$uri = !isset($_SERVER["REQUEST_URI"]) ? $_SERVER["PHP_SELF"] : $_SERVER["REQUEST_URI"];
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 		
		return $protocol."://".$_SERVER["SERVER_NAME"].$port.$uri; 
	}
	public function strleft($s1, $s2) { 
		return substr($s1, 0, strpos($s1, $s2)); 
	}
	
	/*
		Template methods
	*/
	public function template_setup($settings) {
		return $this->_call('template','setup', array($settings));
	}
	public function template_assign($var, $value = null, $nocache = false) {
		return $this->_call('template','assign', array($var, $value, $nocache));
	}
	public function template_append($var, $value = null, $merge = false, $nocache = false) {
		return $this->_call('template','append', array($var, $value, $merge, $nocache));
	}
	public function template_display($template, $cache_id = null, $compile_id = null, $parent = null) {
		return $this->_call('template','display', array($template, $cache_id, $compile_id, $parent));
	}
	
	/*
		images manipulation methods
	*/
	public function img_resize($path, $img, $w, $h) {
		return $this->_call('image','resize', array($path, $img, $w, $h));
	}
	
	/*
		date printing methods
	*/
	public function month_from_num($num) {
		return $this->_call('datetime','month_from_num', array($num));
	}
	
	/*
		File system methods
	*/
	
	// list files and directories, second argument is optional
	// ("file" to list files only or "dir" to list directories only)
	public function dirlist($dir, $type = '') {
		return $this->_call('filesystem','flist', array($dir, $type));
	}
	
	// upload a file to a given directory
	public function upload($it, $dir, $name) {
		return $this->_call('filesystem','upload', array($it, $dir, $name));
	}
	
	/*
		Email methods
	*/
	
	// alternative to mail function (easier to use tho)
	public function mail($obj) {
		return $this->_call('email','quicksend', array($obj));
	}
	
	/*
		gzip methods
	*/
	public function gzip($str) {
		return gzencode($str, 9);
	}
	
	/*
		CSS methods
	*/
	public function cssget() {
		return $this->_call('css', 'get');
	}
	public function cssadd($file, $path = null) {
		return $this->_call('css', 'add', array($file, $path));
	}
	public function cssload() {
		return $this->_call('css', 'load');
	}
	public function cssgetfiles() {
		return $this->_call('css', 'getfiles');
	}
	public function cssmin($str) {
		return $this->_call('css','min', array($str));
	}
	/*
		Javascript methods
	*/
	public function jsget() {
		return $this->_call('javascript', 'get');
	}
	public function jsadd($file, $path = null) {
		return $this->_call('javascript', 'add', array($file, $path));
	}
	public function jsload() {
		return $this->_call('javascript', 'load');
	}
	public function jsdomready($str) {
		return $this->_call('javascript', 'domready', array($str));
	}
	public function jsgetfiles() {
		return $this->_call('javascript', 'getfiles');
	}
	public function jsgetdomrdy() {
		return $this->_call('javascript', 'getdomrdy');
	}
	public function jsmin($str) {
		return $this->_call('javascript','min', array($str));
	}
	public function jslib() {
		return $this->_call('javascript','load_library');
	}
	
	/*
		Storage methods
	*/	
	public function store_write($file, $content) {		
		return $this->_call('store', 'write', array($file, $content));
	}
	
	public function store_append($file, $content = '') {		
		return $this->_call('store', 'append', array($file, $content));
	}
	
	public function store_getarray($file) {		
		return $this->_call('store', 'getarray', $file);
	}
	
	public function store_copy($file) {		
		return $this->_call('store', 'copy', $file);
	}
	
	/*
		Database methods
	*/
	
	// return query result from direct query
	public function dbquery($sql) {
		return $this->_call('database', 'query', $sql);
	}
	
	// return query result from direct query
	public function dbarray($table, $data = array()) {
		return $this->_call('database', 'getarray', array($table, $data));
	}
	
	// insert a record into a database table
	public function dbinsert($table, $data = array()) {
		return $this->_call('database', 'insert', array($table, $data));
	}
	
	// update a record inside a database table based on a  where close
	public function dbupdate_where($table, $data, $where) {
		return $this->_call('database', 'update_where', array($table, $data, $where));
	}
	
	public function dbdate_now() {
		return $this->_call('database', 'date_now');
	}
	
	// update a record inside a database table base on a primary key
	public function dbupdate($table, $data, $key) {
		return $this->_call('database', 'update', array($table, $data, $key));
	}
	
	// delete one or multiple record inside a database table
	public function dbdelete($table, $key) {
		return $this->_call('database', 'delete', array($table, $key));
	}
	
	// delete one or multiple record inside a database table
	public function dbdelete_where($table, $where) {
		return $this->_call('database', 'delete_where', array($table, $where));
	}
	
	// return viewable column names
	public function dbfields($table) {
		return $this->_call('database', 'fields_to_show', array($table));
	}
	
	// return a HTML table containing the viewable
	public function dblist_viewable($table) {
		return $this->_call('database', 'do_list_viewable', array($table));
	}
	
	
	public function dblist($table, $settings = array()) {
		return $this->_call('database', 'do_list', array($table, $settings));
	}
	
	// query a table and return a selected row data as an array
	public function dbrow($table, $primarykey, $settings = array()) {
		return $this->_call('database', 'row', array($table, $primarykey, $settings));
	}
	
	// query the database and create a paginated list based on the results
	public function dbpager($table, $settings = array()) {
		return $this->_call('database','pager', array($table, $settings));
	}
	
	// sanatize string to prevent sql injection
	public function dbstring($str) {
		return $this->_call('database', 'safestr', $str);
	}
	
	// return the number of row for a query
	public function dbcount_all($table, $field, $where) {
		return $this->_call('database','record_count_all', array($table, $field, $where));
	}
	
	// return the number of queries made
	public function dbqueries_count() {
		return $this->_call('database', 'getcount');
	}
	
	// database error message
	public function dberror() {
		return $this->_call('database', 'error');
	}	
	
	// create database table from XML files
	public function dbcreate_from_xml($data = array(), $path = false) {
		return $this->_call('database', 'create_from_xml', array($data, $path));
	}
	
	// remove column from a table if they are not present in the xml
	public function dbclean_table($table) {
		return $this->_call('database', 'clean_table', $table);
	}
	
	// create an html input/select/textarea based on a table XML
	public function dbinput($table, $field, $value = '') {
		return $this->_call('database', 'input', array($table, $field, $value));
	}
	
	// create and populate an empty form based on a table XML for SQL insertion
	// settings: id, return_url, error_url, success_url
	// if id use the form processing will result in an update, otherwise an insert
	public function dbform($table, $settings = array()) {
		return $this->_call('database', 'form', array($table, $settings));
	}
	
	// return info from submitted form
	public function dbform_info($table) {
		return $this->_call('database', 'extract_form_info', array($table));
	}
	
	// create a login form based on an XML definition
	public function dblogin($table, $settings = array()) {
		return $this->_call('database', 'login', array($table, $settings));
	}
	
	// chech if a table exists in the currtently open database
	public function dbtable_exists($table) {
		return $this->_call('database', 'table_exists', array($table));
	}
	
	// chech if a table data exists (if it has been loaded and know from code serenity)
	public function dbtable_data_exists($table) {
		return $this->_call('database', 'table_data_exists', array($table));
	}
	
	// grab and return the table data store by code serenity while processing them
	public function dbtable_data($table) {
		return $this->_call('database', 'table_data', array($table));
	}
	
	/*
		HTML writing methods
	*/
	
	// generate a html table from arrays
	public function htmltable($rows, $headers = null) {
		return $this->_call('html','table',array($rows, $headers));
	}
	
	// generate a html form from arrays
	public function htmlform($form, $settings = array()) {
		return $this->_call('html','form',array($form, $settings));
	}
	
	// add a generated element to the source
	public function html($type, $settings = array()) {
		return $this->_call('html', 'add', array($type, $settings));
	}
		
		// cache methods
		//////
	
	// check if file exists
	public function cached($file) {
		return $this->_call('cache','exists',array($file));
	}
	
	// write content to the cache
	public function cache($file, $content) {
		return $this->_call('cache','write',array($file, $content));
	}
	
	// read content from the cache
	public function cache_read($file) {
		return $this->_call('cache','read',array($file));
	}
	
	// get a cached array
	function cache_get_array($file) {
		return $this->_call('cache', 'get_array', $file);
	}
		// files/directory methods
		///////
		
	public function file_load($file) {
		return @implode("\n", file($file));
	}
		// Misc.
		////////
		
	// take 2 arrays. the second overwrite the first one keys and values if existing in the second array and adding them if non-existant in array1
	public function extend($array1, $array2)
	{
		// overwrite existing keys
		foreach ($array1 as $k => $v) if (isset($array2[$k])) $array1[$k] = $array2[$k];
		
		// add new keys
		foreach ($array2 as $k => $v)  if (!isset($array1[$k])) $array1[$k] = $array2[$k];
		
		// return array1 modified
		return $array1;
	}
		
	// dp basic http auth
	public function basic_auth($settings)
	{
		// make sure we have at least a username
		if (!isset($settings['user'])) $this->error('basic_auth - no username provided');
		else
		{
			// set password
			$pw = isset($settings['pw'])? $settings['pw'] : '';
			
			// set title of the prompt
			$title = isset($settings['title'])? $settings['title'] : 'Authentication';
		}
		
		// get the logged status
		$logged = (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == $settings['user'] && $_SERVER['PHP_AUTH_PW'] == $pw); 
		
		// if not logged prompt
		if (!$logged)
		{   
			header('WWW-Authenticate: Basic realm="'.$title.'"');   
			header('HTTP/1.0 401 Unauthorized');
			die('<h1>401 - Unauthorized Access</h1>');
		}
		
		// else jusr carry on
		else return true;
	}
		
	// encrypt a string (in md5 by default)
	public function encrypt($str) {	
		return md5($str);
	}
	
	// force www prefix on request
	public function force_www() {
		if (WEBROOT != 'http://'.$_SERVER['HTTP_HOST'].'/'.PARENT_PATH) {
			$this->redirect($this->paths['full']);
		}
	}

	// 	redirect to an other url
	public function redirect($url) {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location:'.$url);
	}
	
	// to expand later
	public function url_str($str) {
		return htmlspecialchars($str);
	}
	
	// error reporting function
	public function error($str) {
		die('codeserenity::'.$str);
	}
		
		
	/*
		Private methods
	*/
	
	// assign load stats to the template engine
	private function _assign_stats()
	{
		$now = explode(" ", microtime());
		$stop = $now[1] + $now[0];
		$loadtime = ($stop - CS_START);
		$loadtime = round($loadtime*1000, 0);
		$this->assign('CS_PAGE_LOAD', $loadtime);
		$this->assign('CS_NB_QUERIES', $this->dbqueries_count());
	}
	
	// create and output a captcha image
	private function _captcha($settings = array())
	{
		include EXTERNALS.'securimage/securimage.php';
		$img = new securimage();
		$img->show();
		die();
	}
	
	// "magic" call function
	private function _call($class, $method, $arguments = null)
	{
		// does the class exists
		if (!class_exists($class))
		{
			// is the class file there? if so call it, or error out
			if (!file_exists(CLASSES.$class.'.php')) $this->error('_call - '.CLASSES.$class.'.php does not exist');
			else include CLASSES.$class.'.php';
		}
		
		// class loaded into the core? if not load it
		if (!isset($this->$class)) $this->$class = new $class($this);
		
		// method exists? if it does call the method else error out
		if (!method_exists($class, $method)) $this->error('_call - The method '.$class.'::'.$method.' does not exists');
		else
		{
			// valid arguments?
			if (is_string($arguments) || is_array($arguments))
			{
				// arguments as array? (weird issue when the arguments take by the helper is an array already ence the current $class != 'database' hack - to resolve!)
				return call_user_func_array(array($this->$class, $method), $arguments);
			}
			// call without arguments
			else return call_user_func(array($this->$class, $method));		
		}
	}
	
	// assign dbform submittion if needed
	private function _dbsubmit()
	{
		// any automatic form was submitted?
		if (isset($_SESSION['cs_dbform']) && count($_SESSION['cs_dbform']) > 0 && count($_POST) > 0)
		{
			// loop through table keys
			foreach (array_keys($_SESSION['cs_dbform']) as $formid)
			{
				// check if it is the form submitted
				if (isset($_POST[$formid])) return $this->_call('database','form_process',array($formid));
			}			
		}
	}	
}
 
?>