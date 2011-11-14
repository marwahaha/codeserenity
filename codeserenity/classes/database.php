<?php

/* Code Serenity v.2.0 - Database class
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
		TO DO
		
		1. rename key to primarykey accross the board (key can also not be primary!)
		2. do pager functionality and make it use a basic template file (filepath can be given)
		3. make the form use a basic template file (filepath can be given)		
		
		NOTES
		
		The main point of 2 and 3 is to store the markup pattern outside the class so it
		can be easily customizable per application. Ideally use filepath and leave the 
		default files alone :)
*/

// database methods
class database
{
	// private properties
	private $errors = array(); // store mostly errors from form processing to pass back to the user
	private $results = array(); // store query results for faster access on re-use
	private $session_opened = false; // track class session use
	private $tables2headers = array(); // use to cache tables column names
	private $tables2labels = array(); // use to cache tables column XML labels
	private $tables2keys = array(); // use to cache tables primary key
	private $tables2data = array(); // use to cache tables data
	private $queries_count = 0; // track number of queries made
	private $types2inputs = array(
		'varchar' => 'text',
		'char' => 'text',
		'password' => 'password',
		'file' => 'file',
		'text' => 'textarea',
		'longtext' => 'textarea',
		'int' => 'text',
		'auto' => 'text',
		'float' => 'text',
		'decimal' => 'text',
		'date' => 'text',
		'datetime' => 'text',
		'boolean' => 'checkbox'
	); // supported field type
	
	// constructor
	public function __construct($parent = null)
	{		
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::database::__construct - the passed argument is not an object like expected');
		else return true;
		
		// db config present?
		if (defined('DB_CONFIG') && DB_CONFIG != '')
		{		
			// database layer
			include EXTERNALS.'adodb5/adodb.inc.php';
			$this->db = ADONewConnection(DB_CONFIG);
			
			// change results array format to associative 
			$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		}
		else $this->cs->error('database::__construct - Database configuration not available');
	}
	
	// universal data gathering function
	public function data($table, $options) {
	
		
	
	}
	
	// direct query
	public function query($sql)
	{
		// if cached serve results
		if (isset($this->results[$sql])) return $this->results[$sql];
		
		// query database
		$res = $this->db->Execute($sql);
		
		// update query count
		$this->queries_count++;
		
		// error check
		if (!$res) $this->cs->error('database::query - '.$this->db->ErrorMsg());
		else return $res;
	}
	
	public function date_now() {
		return str_replace("'", "", $this->db->DBDate(date('Y-m-d h:i:s')));
	}
	
	// update a database record
	public function update_where($table, $data, $where)
	{
		// get table data
		$xmldata = $this->_getdata($table);
		
		// to auto update
		$array = array();
		
		// loop through data
		foreach($xmldata['dbtable'][$table] as $field)
		{
			// update attribute present?
			if (isset($field['update']))
			{
				// now?
				if ('now' == $field['update']) $data[$field['name']] = str_replace("'","",$this->db->DBDate(date('Y-m-d h:i:s')));
			}
		}
		
		// update query count
		$this->queries_count++;
		
		// performance hit? (benchmark later)
		if (!$this->db->AutoExecute(PREFIX.$table, $data, 'UPDATE', $where)) $this->cs->error('database::update - '.$this->db->ErrorMsg());
		else return true;
	}
	
	// update a database record
	public function update($table, $data, $primarykey)
	{
		// get table data
		$xmldata = $this->_getdata($table);
		
		// to auto update
		$array = array();
		
		// loop through data
		foreach($xmldata['dbtable'][$table] as $field) {
		
			// update attribute present?
			if (isset($field['update'])) {
			
				// now?
				if ('now' == $field['update']) $data[$field['name']] = str_replace("'","",$this->db->DBDate(date('Y-m-d h:i:s')));
			}
		}
		
		// update query count
		$this->queries_count++;
		
		// performance hit? (benchmark later)
		if (!$this->db->AutoExecute(PREFIX.$table, $data, 'UPDATE', $this->_getkey($table).' = '.$primarykey)) $this->cs->error('database::update - '.$this->db->ErrorMsg());
		else return true;
	}
	
	// insert query
	public function insert($table, $data)
	{		
		// get table data
		$xmldata = $this->_getdata($table);
		
		// to auto update
		$array = array();
		
		// loop through data
		foreach($xmldata['dbtable'][$table] as $field)
		{
			// update attribute present?
			if (isset($field['update']))
			{
				// now?
				if ('now' == $field['update']) $data[$field['name']] = str_replace("'","",$this->db->DBDate(date('Y-m-d h:i:s')));
				else $data[$field['name']] = $field['update'];
			}
			
			// insert attribute present?
			if (isset($field['insert']))
			{
				// now?
				if ('now' == $field['insert']) $data[$field['name']] = str_replace("'","",$this->db->DBDate(date('Y-m-d h:i:s')));
				else $data[$field['name']] = $field['insert'];
			}
		}
		
		// update query count
		$this->queries_count++;
		
		// performance hit? (benchmark later)
		if ($this->db->AutoExecute(PREFIX.$table, $data, 'INSERT') === false) $this->cs->error('database::insert - '.$this->db->ErrorMsg());
	}
	
	// delete a row or multiple rows (integer|array)
	public function delete($table, $primarykey)
	{
		if (is_int($primarykey)) $this->query('DELETE FROM '.PREFIX.$table.' WHERE '.$this->_getkey($table).'='.$this->dbsafe($primarykey));
		elseif (is_array($primarykey)) $this->query('DELETE FROM '.PREFIX.$table.' WHERE '.$this->_getkey($table).' IN('.implode(',', $primarykey).')');
		else $this->cs->error('database::detete - the second argument type is invalid - expectting integer or array of integer');	
	}
	
	// delete a row or multiple rows without using a primary key
	public function delete_where($table, $where)
	{
		$this->query('DELETE FROM '.PREFIX.$table.' WHERE '.$where);
	}
	
	// create a data list with pagination
	// inspirartion from ADODB_Pager written "Pablo Costa" <pablo@cbsp.com.br>
	/*
		TO DO:
			- Order the column as per XML file
			- before and add support
				(possibility to pass pattern to that even the data from fields that are not shown can be use to create link
				i.e the id column pattern idea <td><a href="/edit/{$id}">Edit</a></td> ???)
	*/
	public function pager($table, $settings = array())
	{
		// is a specific page requested?
		if (isset($_GET['cs_page'])) $_SESSION['cs_page'] = (integer) $_GET['cs_page'];
		else $_SESSION['cs_page'] = 1;
		if (empty($_SESSION['cs_page'])) $_SESSION['cs_page'] = 1;
		
		// defaults
		$defaults = array(
			'select' => '',
			'select_add' => '',
			'headers' => true,
			'patterns' => array(),
			'limit' => 10,
			'first' =>'|&lt;',
			'prev' => '&lt;&lt;',
			'next' => '&gt;&gt;',
			'last' => '&gt;|'
		);
		
		// sort options
		$options = $this->cs->extend($defaults, $settings);
		
		// set page and extract options
		$page = isset($_SESSION['cs_page']) ? $_SESSION['cs_page'] : 1;
		extract($options);
		
		// get the showable (if no specific column have been requested
		if (empty($select)) $select = implode(",", $this->_getshowable($table));
		
		// add extra string to select query if not empty and create an array of "hidden" field
		if (!empty($select_add))
		{
			$select .= ','.$select_add;
			$hiddens = explode(",", $select_add);
			foreach($hiddens as $k => $v) $hiddens[$k] = trim($v);
		} else $hiddens = array();
		
		// update query count
		$this->queries_count++;
		
		// query database for the list
		$res = $this->db->PageExecute('SELECT '.$select.' FROM '.PREFIX.$table, $limit, $page);
		$rows = $this->res2array($res);
		$labels = $this->_getlabels($table, explode(',', $select));
		 
		// start grid table string
		$grid = $this->cs->html('table')."\n";
		
		// headers enabled?
		if ($headers)
		{		
			// open row
			$grid .= $this->cs->html('tr');
			
			// convert headers into a string (change later to it uses the label attribute)
			foreach ($labels as $label)
			{
				// if object get name property
				if (is_object($label)) $label = $label->name;				
				$grid .= $this->cs->html('th').$label.$this->cs->html('/th');
			}
			
			// close row
			$grid .= $this->cs->html('/tr')."\n";		
		}
		
		// loop through rows
		foreach ($rows as $row)
		{
			// open row
			$grid .= $this->cs->html('tr');
			
			// extract all data for the row (to be used with patterns after)
			$keys = $values = array();
			foreach ($row as $k => $v)
			{
				$keys[] = '$'.$k;
				$values[] = $v;
			}
			
			// fill with columns
			foreach ($row as $k => $v)
			{
				// add the block
				if (!in_array($k, $hiddens))
				{
					// any patterns for it?
					if (in_array($k, array_keys($patterns))) $v = str_replace($keys, $values, $patterns[$k]);
					
					// build string
					$grid .= $this->cs->html('td').$v.$this->cs->html('/td');
				}
			}
			
			// close row			
			$grid .= $this->cs->html('/tr')."\n";
		}
		
		// close table
		$grid .= $this->cs->html('/table')."\n";
		
		// generate the uri string
		if (isset($_GET))
		{
			// temp string
			$str = '';
			
			// loop through get
			foreach ($_GET as $key => $value)
			{
				// store the one we dont set
				if ('cs_page' != $key) $str .= $key.'='.$value.'&amp;';
			}
			
			if ('' != $str) $uri = '?'.$str;
			else $uri = '?';
		}
		else $uri = '?';
		
		// generate the first link
		$first_url = $uri.'cs_page=1';
		$first = '<a href="'.$uri.'cs_page=1">'.$first.'</a>';
		
		// generate previous link
		if (intval($res->AbsolutePage()-1) > 0)
		{
			$prev_url = $uri.'cs_page='.intval($res->AbsolutePage()-1);
			$prev = '<a href="'.$prev_url.'">'.$prev.'</a>';
		}
		else $prev_url = '';
		
		// generate next link
		if (intval($res->AbsolutePage()+1) <= $res->LastPageNo())
		{
			$next_url = $uri.'cs_page='.intval($res->AbsolutePage()+1);
			$next = '<a href="'.$next_url.'">'.$next.'</a>';
		}
		else $next_url = '';
		
		// generate last link
		$last_url = $uri.'cs_page='.$res->LastPageNo();
		$last = '<a href="'.$last_url.'">'.$last.'</a>';
		
		// pages number out of total
		$pages =  $page.'/'.$res->LastPageNo();
		
		// array return for better use in template engine?
		return array(
			'grid' => $grid,
			'first' => $first,
			'prev' => $prev,
			'next' => $next,
			'last' => $last,
			'first_url' => $first_url,
			'prev_url' => $prev_url,
			'next_url' => $next_url,
			'last_url' => $last_url,
			'pages' => $pages,
			'pagetotal' => $res->LastPageNo()
		);
	}
	
	// check if a table exists
	public function table_exists($table) {
		return in_array(PREFIX.$table, $this->db->MetaTables('TABLES'));
	}
	
	// check if a table data exists (if table exists it should but we never know)
	public function table_data_exists($table) {
		$arr = $this->_getdata($table);
		if (count($arr) > 0) return true;
		else return false;
	}
	
	// check if a table data exists (if table exists it should but we never know)
	public function table_data($table) {
		return $this->_getdata($table);
	}
	
	// create a table based on a table content
	public function do_edit_list($table, $fields, $patterns)
	{	
		$headers = $this->_getlabels($table);
		$data = $this->_getdata($table);
		$res = $this->getarray($table, array(
			'select' => implode($this->_getshowable($table), ',')
		));
		$table_data = $array = array();
		for ($i = 0, $count = count($res); $i < $count; $i++) $table_data[] = $res[$i];		
		return $this->cs->htmltable($table_data, $headers);
	}
	
	// create a table based on a table content
	public function do_list_viewable($table)
	{	
		$headers = $this->_getlabels($table);
		$data = $this->_getdata($table);
		$res = $this->getarray($table, array(
			'select' => implode($this->_getshowable($table), ',')
		));
		$table_data = $array = array();
		for ($i = 0, $count = count($res); $i < $count; $i++) $table_data[] = $res[$i];		
		return $this->cs->htmltable($table_data, $headers);
	}
	
	// create a table based on a table content
	public function do_list($table, $settings)
	{	
		$table_data = array();
		$table_headers = $this->_getheaders($table);
		$array = $this->getarray($table, $settings);
		for ($i = 0, $count = count($array); $i < $count; $i++) $table_data[] = $array[$i];
		return $this->cs->htmltable($table_data, $table_headers);
	}
	
	// query the database and return the number of records
	public function record_count_all($table, $field, $where = '1') {
		$res = $this->db->query('SELECT '.$field.' FROM '.PREFIX.$table.' WHERE '.$where);
		return $res->RecordCount();
	}
	
	// return number of queries made so far
	public function getcount() {
		return $this->queries_count;
	}
	
	// take a database object and return it's fields as array
	public function res2array($res)
	{
		// set holding array
		$array = array();
		
		// loop through results
		while(!$res->EOF)
		{
			$array[] = $res->fields;
			$res->MoveNext();
		}
		
		// return the array
		return $array;
	}
	
	// list table content based on settings
	public function getarray($table, $settings)
	{
		// defaults
		$defaults = array(
			'select' => '*',
			'where' => '',
			'limit' => '',
			'orderby' => ''
		);
		
		// sort options (extract too)
		extract($this->cs->extend($defaults, $settings));
		
		if (!empty($where)) $where = 'WHERE '.$where;
		if (!empty($limit)) $limit = 'LIMIT 0,'.$limit;
		if (!empty($orderby)) $orderby = 'ORDER BY '.$orderby;
		
		// build query
		$sql = 'SELECT '.$select.' FROM '.PREFIX.$table.' '.$where.' '.$orderby.' '.$limit;
		
		// do the query
		$res = $this->query($sql);
		
		// set holding array
		$array = array();
		
		// loop through results
		while(!$res->EOF) {
			$array[] = $res->fields;
			$res->MoveNext();
		}
		
		// return the array
		return $array;
	}
	
	// clean a database table
	public function clean_table($table)
	{
		// get table data
		$data = $this->_getdata($table);
		
		// get the column in the database
		$dbdata = $this->_getheaders(PREFIX.$table);
		
		// array to save column to drop
		$array = array();
		
		// loop fields from database
		foreach ($dbdata as $k => $v)
		{
			// if it doesn't exists in xml add to the list to drop
			if (!isset($data[$table]['fields'][strtolower($k)])) $array[] = strtolower($k);
		}
		
		// cleaning needed?
		if (count($array) > 0)
		{
			// drop the columns
			$dict = NewDataDictionary($this->db);
			$sql = $dict->DropColumnSQL(PREFIX.$table, $array);
			$dict->ExecuteSQLArray($sql);
			
			// update query count
			$this->queries_count++;
		}
		
	}
	
	// process the return of a submitted "dbform"
	public function form_process($formid)
	{
		$table = $_SESSION['cs_dbform'][$formid]['name'];
		$primarykey = isset($_SESSION['cs_dbform'][$formid]['primarykey']) ? $_SESSION['cs_dbform'][$formid]['primarykey'] : false;
		
		// reset errors
		$this->errors = array();
		
		// captcha verification?
		if (isset($_SESSION['cs_dbform'][$formid]['captcha']))
		{
			if (!class_exists('Securimage')) include EXTERNALS.'securimage/securimage.php';
			$img = new Securimage();
			if (!$img->check($_POST['cs_captcha'])) $this->errors['cs_captcha'] = 'Captcha verification failed';
			unset($_SESSION['securimage_code_value']);
		}
		
		// get the table data
		$data = $this->_getdata($table);
		$data = $data['dbtable'][$table];
		
		// extract only the data we need from $_POST
		$posts = $this->_extract($data);
		
		// server side validation
		$this->_validate($posts, $data, $primarykey);
		
		// server side encryption for field marked as such
		$posts = $this->_form_encrypt($posts, $data);
		
		// check session for addiotional values
		if (isset($_SESSION['cs_dbform'][$formid]['sql_insert_array'])) {
			foreach ($_SESSION['cs_dbform'][$formid]['sql_insert_array'] as $k => $v) $posts[$k] = $v;
		}
		
		// if errors stop, else continue
		if (count($this->errors) > 0) {
			$_SESSION['cs_errors'] = $this->errors;
			return false;
		}
		else
		{
			// sort out uploaded files
			$posts = $this->_form_uploads($posts, $data);
			
			// insert or update?
			if ($primarykey) $this->update($table, $posts, $primarykey);
			else $this->insert($table, $posts);
			
			// store posts into the session so they can be retrieve (clear session on data grab)
			$this->_store_form_info($table, $posts, isset($primarykey) ? $primarykey : false);			
			
			// set success message
			if (!isset($_SESSION['errors'])) $url = $_SESSION['cs_dbform'][$formid]['success_url'];
			else $url = $_SESSION['cs_dbform'][$formid]['error_url'];
			
			// redirect (so the form doesn't re-submit on refresh)
			$this->cs->redirect($url);
		}
	}
	
	private function _form_encrypt($posts, $fields) {
		foreach ($fields as $field) {
			if ($field['type'] == 'password' && isset($field['encrypt']) && $field['encrypt'] !== 'false') {
				if (isset($posts[$field['type']])) $posts[$field['type']] = $this->cs->encrypt($posts[$field['type']]);				
			}
		}
		return $posts;
	}
	
	private function _form_resize_image($img, $field)
	{
		list($width, $height) = getimagesize(ROOT.$field['path'].$img);
		$maxwidth = isset($field['maxwidth']) ? $field['maxwidth'] : false;
		$maxheight = isset($field['maxheight']) ? $field['maxheight'] : false;
		$ratio = $height/$width;
		if ($maxwidth && $maxwidth < $width)
		{
			$newwidth = $maxwidth;
			$newheight = $ratio*$newwidth;
		}
		if (isset($newheight))
		{
			if ($maxheight && $maxheight < $newheight)
			{
				$newheight = $maxheight;
				$newwidth = $newheight/$ratio;
			}
		}
		else
		{
			if ($maxheight && $maxheight < $height)
			{
				$newheight = $maxheight;
				$newwidth = $newheight/$ratio;
			}

		}
		if (isset($newwidth) && isset($newheight)) $this->cs->img_resize(ROOT.$field['path'], $img, $newwidth, $newheight);
		
	}
	
	private function _form_uploads($posts, $fields)
	{
		foreach ($fields as $field)
		{
			if ($field['type'] == 'file' && isset($field['path']))
			{
				if (isset($_FILES[$field['name']]) && $_FILES[$field['name']]['size'] > 0)
				{
					/*
						DO MORE CHECK HERE TO CHECK FOR:
							FILE TYPE RESTRICTION
							FILE SIZE RESTRICTION
							IMAGE RESIZING
							NAMING FORMAT
							
							mime_content_type($file)
					*/
					
					// upload the file
					$this->cs->upload($field['name'], ROOT.$field['path'], $_FILES[$field['name']]['name']);
					
					if (isset($field['maxwidth']) || isset($field['maxheight'])) $this->_form_resize_image($_FILES[$field['name']]['name'], $field);
					
					// save string to store in database
					$posts[$field['name']] = $_FILES[$field['name']]['name'];
				}
			}
		}
		return $posts;
	}
	
	public function extract_form_info($table)
	{
		$data = array();
		if (isset($_SESSION['dbform'][$table]))
		{
			$data = $_SESSION['dbform'][$table];
			unset($_SESSION['dbform'][$table]);
		}
		return $data;
	}
	
	private function _store_form_info($table, $posts, $primarykey)
	{
		if (isset($primarykey)) $_SESSION['dbform'][$table]['primaryKey'] = $primarykey;
		foreach ($posts as $v => $k) $_SESSION['dbform'][$table][$v] = $k;
	}
	
	private function _booleans($posts, $fields)
	{
		foreach($fields as $k => $v) {
			if ($v['type'] == 'boolean' && isset($posts[$v['name']]) && empty($posts[$v['name']])) $posts[$v['name']] = 1;
		}
		
		return $posts;
	}
	private function _special($posts, $fields)
	{
		return $posts;
	}
	
	// return a row data as an array
	public function row($table, $primarykey, $settings = array())
	{		
		// custom select?
		$select = isset($settings['select']) ? $settings['select'] : '*';
		
		// update query count
		$this->queries_count++;
		
		// return query result
		return $this->db->getRow('SELECT '.$select.' FROM '.PREFIX.$table.' WHERE id = '.$primarykey);
	}
	
	// create html input/select/textarea
	public function input($table, $searchfield, $value = '') 
	{
		// get the tables data
		$data = $this->_getdata($table);
		$field = $data['dbtable'][$table][$searchfield];
		
		// db type to html input type
		$html_type = $this->types2inputs[$field['type']];
		
		// set the most used type (less check to do in the switch underneath)
		$tag = 'input';
		
		// common element attributes
		$attributes['name'] = $field['name'];
		$attributes['id'] = isset($field['id']) ? 'form-'.$field['id'] : 'form-'.$field['name'];
		$attributes['value'] = $value;
		
		// build tag from html type
		switch($html_type) {
			case 'textarea';
				$tag = $html_type;
				unset($field['type']);
				unset($field['length']);
				$attributes['close'] = true;
				$attributes['rows'] = isset($field['rows']) ? $field['rows'] : '3';
				$attributes['cols'] = isset($field['cols']) ? $field['cols'] : '40';
			break;
			case 'file';
				$enctype = 'multipart/form-data';
			break;
		}
		
		// if tag still = input then set the type
		if ('input' == $tag) $attributes['type'] = $html_type;
		
		// any length restriction? (make sure the type is appropriate)
		if ( ('text' == $html_type || 'password' == $html_type) && isset($field['length']) ) $attributes['maxlength'] = $field['length'];
		
		// if password reset value
		if ('password' == $html_type) $attributes['value'] = '';
		
		// add framework class name (ease styling of form elements)
		if (isset($attributes['type'])) $attributes['class'] = 'cs_form_'.$attributes['type'];
		else $attributes['class'] = 'cs_form_'.$tag;
		
		// required field? marked with class for client side validation
		if (isset($field['required']) && 'true' == $field['required']) {
		
			// make sure csValidate will be loaded  for client side validation
			$attributes['class'] .= ' required';
			$this->cs->jsadd('csValidate');
		}
		
		// unique field? marked with class for client side validation
		if (isset($field['unique']) && 'true' == $field['unique']) $attributes['class'] .= ' unique';
		
		// is there a server side error attach to this field?
		if (isset($this->errors[$field['name']])) $attributes['class'] .= ' error';
		
		// is there a server side error attach to this field?
		if (isset($attributes['content'])) $attributes['content'] = str_replace('\"', '"', $attributes['content']);
		
		// if class is still empty remove it
		if (empty($attributes['class'])) unset($attributes['class']);
		else $attributes['class'] = trim($attributes['class']);
		
		// build the tag
		if (isset($field['type']) && ('date' == $field['type'] || 'datetime' == $field['type'])) return $this->_date_tag($attributes, $field);
		elseif (isset($field['labels']) && isset($field['values'])) {
			if (!isset($field['htmltype']) || $field['htmltype'] == 'select') return $this->_select_tag($attributes, $field);
			elseif ($field['htmltype'] == 'checkbox') return $this->_checkbox_tag($attributes, $field);
			elseif ($field['htmltype'] == 'radio') return $this->_radio_tag($attributes, $field);
		}
		else return $this->cs->html($tag, $attributes);
	}
	
	// create a html form based on table xml definition
	public function form($table, $settings)
	{
		// get the table data
		$data = $this->_getdata($table);		
		$data = $data['dbtable'][$table];
		
		// create a unique "id" for the form
		$formid = uniqid();
		
		// source holding string
		$str = '';
		
		// any html to inject before
		if (isset($settings['before']) && !empty($settings['before'])) $str .= $settings['before']."\n";
		
		// unset any previous table settings if any and no session have been opened yet!
		if (!$this->session_opened) unset($_SESSION['cs_dbform']);
		
		// edit or insert?
		if (isset($settings['primarykey']))
		{
			// save the primary key in session
			// if session available we check  later that noone is trying to alter database by changing the primarykey
			// considering using session only tho.... unsure at the moment... would be useful and a lot safier...
			$_SESSION['cs_dbform'][$formid]['primarykey'] = $settings['primarykey'];
			$_SESSION['cs_dbform'][$formid]['name'] = $table;
			
			// get the edited row current content
			$row = $this->row($table, $settings['primarykey']);
		}
		else
		{
			// save table name in session
			// use for security check (see the update version above for details)
			$_SESSION['cs_dbform'][$formid]['name'] = $table;
		}
		
		// any insert string to add up?
		if (isset($settings['sql_insert_array'])) $_SESSION['cs_dbform'][$formid]['sql_insert_array'] = $settings['sql_insert_array'];
		
		// store return urls
		$_SESSION['cs_dbform'][$formid]['success_url'] = isset($settings['success_url']) ? $settings['success_url'] : $this->cs->paths['full'];
		$_SESSION['cs_dbform'][$formid]['error_url'] = isset($settings['error_url']) ? $settings['error_url'] : $this->cs->paths['full'];
		
		// make sure session is tracked as opened
		$this->session_opened = true;
		
		// open the form element
		$str .= $this->cs->html('fieldset', array('class'=>'cs_fieldset'));
		
		/*
			insert HTML after the fields list
		*/
		if (isset($settings['before_fields'])) $str .= $settings['before_fields'];
		
		// loop through the fields
		foreach($data as $field)
		{
			// first make sure the field need displaying
			if ((isset($field['show']) && 'false' == (string) $field['show'])) continue;
			
			if (isset($settings['hide']) && is_int(strpos($settings['hide'], $field['name']))) continue;
			 
			// db type to html input type
			$html_type = $this->types2inputs[$field['type']];
			
			// set the most used type (less check to do in the switch underneath)
			$tag = 'input';
			
			// common element attributes
			$attributes['name'] = $field['name'];
			$attributes['id'] = isset($field['id']) ? 'form-'.$field['id'] : 'form-'.$field['name'];
			
			// build tag from html type
			switch($html_type)
			{
				case 'textarea';
					$tag = $html_type;
					unset($field['type']);
					unset($field['length']);
					$attributes['close'] = true;
					$attributes['rows'] = isset($field['rows']) ? $field['rows'] : '3';
					$attributes['cols'] = isset($field['cols']) ? $field['cols'] : '40';
				break;
				case 'file';
					$enctype = 'multipart/form-data';
				break;
			}
			
			if (isset($row))
			{
				if ('input' == $tag)
				{
					$attributes['value'] = $row[$field['name']];
					if ($field['type'] == 'boolean' && $row[$field['name']] == 1) $attributes['checked'] = 'checked';
				}
				else $attributes['content'] = $row[$field['name']];
			}
			
			// if tag still = input then set the type
			if ('input' == $tag) $attributes['type'] = $html_type;
			
			// any length restriction? (make sure the type is appropriate)
			if ( ('text' == $html_type || 'password' == $html_type) && isset($field['length']) ) $attributes['maxlength'] = $field['length'];
			
			// if password reset value
			if ('password' == $html_type) $attributes['value'] = '';
			
			// add framework class name (ease styling of form elements)
			if (isset($attributes['type'])) $attributes['class'] = 'cs_form_'.$attributes['type'];
			else $attributes['class'] = 'cs_form_'.$tag;
			
			// required field? marked with class for client side validation
			if (isset($field['required']) && 'true' == $field['required'])
			{
				// make sure csValidate will be loaded  for client side validation
				$attributes['class'] .= ' required';
				$this->cs->jsadd('csValidate');
			}
			
			// unique field? marked with class for client side validation
			if (isset($field['unique']) && 'true' == $field['unique']) $attributes['class'] .= ' unique';
			
			// is there a server side error attach to this field?
			if (isset($this->errors[$field['name']])) $attributes['class'] .= ' error';
			
			// is there a server side error attach to this field?
			if (isset($attributes['content'])) $attributes['content'] = str_replace('\"', '"', $attributes['content']);
			
			// if class is still empty remove it
			if (empty($attributes['class'])) unset($attributes['class']);
			else $attributes['class'] = trim($attributes['class']);
			
			/*
				Expend the template system to allow load from file
				this will simplify integration with theme based application
			*/
			
			$error_msg = isset($this->errors[$field['name']]) ? '<span class="cs_error">'.$this->errors[$field['name']].'</span>' : '';
			
			// build the tag
			if (isset($field['type']) && ('date' == $field['type'] || 'datetime' == $field['type'])) $input_tag = $this->_date_tag($attributes, $field);
			elseif (isset($field['labels']) && isset($field['values']))
			{				
				if (!isset($field['htmltype']) || $field['htmltype'] == 'select')
				{
					if (isset($row)) $input_tag = $this->_select_tag($attributes, $field, $row);
					else $input_tag = $this->_select_tag($attributes, $field);
				}
				elseif ($field['htmltype'] == 'checkbox')
				{
					if (isset($row)) $input_tag = $this->_checkbox_tag($attributes, $field, $row);
					else $input_tag = $this->_checkbox_tag($attributes, $field);
				}
				elseif ($field['htmltype'] == 'radio')
				{
					if (isset($row)) $input_tag = $this->_radio_tag($attributes, $field, $row);
					else $input_tag = $this->_radio_tag($attributes, $field);
				}
			}
			else $input_tag = $this->cs->html($tag, $attributes);
			
			// check for file type and build link to file
			if (isset($field['type']) && 'file' == $field['type'] && isset($row[$field['name']]))
			{
				$file_link = '<span class="cs_form_file_delete">'.$this->cs->html('input', array(
					'type' => 'checkbox',
					'id' => $field['name'].'__delete',
					'name' => $field['name'].'__delete'
				))
				.' <label for="'.$field['name'].'__delete">Delete this file</label></span>'
				.'<span class="cs_form_file_link"><a href="'.WEBROOT.$field['path'].$row[$field['name']].'">View or download this file</a></span>';
			}
			else $file_link = '';
			
			// build the html bit (templaye used?)			
			if (isset($settings['template']) && !empty($settings['template']))
			{
				// make sure the template is right (can't have to much check and erros it help debugging!)
				// We are making only {field} mandatory tho - for more flexibility)
				if (strpos($settings['template'], '{field}') === false) $this->cs->error('database::form - Template provided invalid - {field} expected');
				else
				{		
					$label = isset($field['label']) ? $field['label'] : $field['name'];
					if (isset($settings['lang']) && isset($settings['lang']['field_'.$field['name']])) $label = $settings['lang']['field_'.$field['name']];
					$str .= str_replace(
								array('{field}', '{label}'), 
								array($input_tag.$file_link.$error_msg,'<label for="'.$attributes['id'].'">'.$label.'</label>'),
								$settings['template']
							)."\n";
					unset($label);
				}
			}
			else
			{
				$label = isset($field['label']) ? $field['label'] : ucwords(str_replace("_"," ",$attributes['name']));
				if (isset($settings['lang']) && isset($settings['lang']['field_'.$field['name']])) $label = $settings['lang']['field_'.$field['name']];
				$str .= '<div class="cs_field form-'.$attributes['name'].'"><div class="cs_label"><label for="'.$attributes['id'].'"><span>'.$label.'</span></label></div>
				<div class="cs_input">'.$input_tag.$file_link.$error_msg.'</div></div>'."\n";
			}
			
			// delete settings array (reset all keys)
			unset($attributes);
		}
		
		/*
			Do a proper captcha function in codeserenity::html
			and use it here instead of this rubbish
		*/
		
		// are we using captch validation
		if (isset($settings['captcha']) && $settings['captcha'] === true) 
		{
			/*
				This is crap and temporary, need to improve it a lot more!
			*/
			
			$captcha_error = isset($this->errors['cs_captcha']) ? '<span class="error_msg">'.$this->errors['cs_captcha'].'</span>' : '';
			$captcha_error_class = isset($this->errors['cs_captcha']) ? 'error' : '';
			$_SESSION['cs_dbform'][$formid]['captcha'] = true;
			
			// add the image tag
			$str .= $this->cs->html('div').'<label class="cs_label">Captcha Image</label>'.$this->cs->html('img',array(
						'src' => 'cs/captcha',
						'class' => 'captcha_img'
					)
				).$this->cs->html('/div')."\n";
			
			// add the input for it
			$str .= $this->cs->html('div').'<label for="cs_captcha" class="cs_label">Captcha code</label>'.$this->cs->html('input',array(
						'id' => 'cs_captcha',
						'name' => 'cs_captcha',
						'type' => 'text',
						'class' => trim('cs_form_text required '.$captcha_error_class)
					)
				).$captcha_error.$this->cs->html('/div')."\n";
		}
		
		
		
		/*
			insert HTML after the fields list
		*/
		if (isset($settings['after_fields'])) {
			$str .= $this->cs->html('/fieldset')."\n";
			$str .= $this->cs->html('fieldset')."\n";
			$str .= $settings['after_fields'];
			$str .= $this->cs->html('/fieldset')."\n";
			$str .= $this->cs->html('fieldset')."\n";
		}
		
		// add submit button (check for custom one)
		/*if (isset($settings['submit']) && !empty($settings['submit'])) $str .= $settings['submit']."\n";
		else*/ 
		$str .= $this->cs->html('div', array('class'=>'cs_button')).$this->cs->html('input', array(
				'id' => isset($settings['submit_id']) ? $settings['submit_id'] : $formid,
				'name' => $formid,
				'type' => 'submit',
				'value' => isset($settings['submit_label']) ? $settings['submit_label'] : 'Submit',
				'class' => 'cs_form_submit'
			)).$this->cs->html('/div')."\n";		
		
		// close the fieldset
		$str .= $this->cs->html('/fieldset')."\n";
		
		// any html to inject after
		if (isset($settings['after']) && !empty($settings['after'])) $str .= $settings['after']."\n";
		
		$form_options = array(
			'action' => $this->cs->paths['full'],
			'class'  => 'cs_form',
			'method' => 'post');

		if (isset($enctype)) $form_options['enctype'] = $enctype;
		
		// return the generate form
		return $this->cs->html('form', $form_options)."\n".$str.$this->cs->html('/form');
	}
	
	// generate a where clause to skip some values/labels in queries used to generate tags
	private function _skip_where($field, $label_field, $value_field) {
		
		// holding string
		$where = $and = '';
		
		// check if we need to skip some values
		if (isset($field['skip-values'])) {
			$arr = explode(',', $field['skip-values']);
			foreach ($arr as $v) {
				$where .= $and.$value_field.'!='.$this->dbsafe($v);
				$and = ' AND ';
			}
		}
		
		// check if we need to skip some values
		if (isset($field['skip-labels'])) {
			$arr = explode(',', $field['skip-labels']);
			foreach ($arr as $v) {
				$where .= $and.$label_field.'!='.$this->dbsafe($v);
				$and = ' AND ';
			}
		}
		
		// check for custom skip clause
		if (isset($field['where'])) $where .= $and.$field['where'];
		
		// return holding string
		return $where;
	}
	
	// create checkbox tags based on the XML definition
	private function _checkbox_tag($attr, $field, $row = array())
	{
		$tag = '';
		
		// is it a database based one?
		if ($field['labels'][0] == '{')
		{
			// clean and collect data required
			$field['labels'] = str_replace(array('{','}'),array('',''), $field['labels']);
			$field['values'] = str_replace(array('{','}'),array('',''), $field['values']);
			$arr = explode('.', $field['labels']);
			$table = $arr[0];
			$label_field = $arr[1];
			$arr = explode('.', $field['values']);
			$value_field = $arr[1];

			// query the database
			$arr = $this->getarray($table, array(
				'select' => $label_field.','.$value_field,
				'where' => $this->_skip_where($field, $label_field, $value_field)
			));
			
			// make sure we have data
			if (count($arr) > 0)
			{
				for ($i = 0, $count = count($arr); $i < $count; $i++)
				{
					$attrs = array(
						'name' => $field['name'].'[]',
						'type' => 'checkbox',
						'class' => 'cs_form_checkbox',
						'value' => $arr[$i][$value_field],
						'id' => $field['name'].'_'.$i
					);
					
					if (count($row) > 0 && $row[$field['name']] == $arr[$i][$value_field]) $attrs['checked'] = 'checked';
					
					//echo $row[$field['name']].'=='.$arr[$i][$value_field];
					$tag .= '<div>'.$this->cs->html('input', $attrs).'<label for="#'.$field['name'].'_'.$i.'">'.$arr[$i][$label_field].'</label></div>';
				}
			}
		}
		else
		{
		
		}
		
		return $tag;
	}
	
	// create radio tags based on field data and attributes
	private function _radio_tag($attr, $field, $row = array())
	{
		$tag = '';
		
		// is it a database based one?
		if ($field['labels'][0] == '{')
		{
			// clean and collect data required
			$field['labels'] = str_replace(array('{','}'),array('',''), $field['labels']);
			$field['values'] = str_replace(array('{','}'),array('',''), $field['values']);
			$arr = explode('.', $field['labels']);
			$table = $arr[0];
			$label_field = $arr[1];
			$arr = explode('.', $field['values']);
			$value_field = $arr[1];
			
			// query the database
			$arr = $this->getarray($table, array(
				'select' => $label_field.','.$value_field
			));
			
			// make sure we have data
			if (count($arr) > 0)
			{
				for ($i = 0, $count = count($arr); $i < $count; $i++)
				{
					$attrs = array(
						'name' => $field['name'],
						'type' => 'radio',
						'class' => 'cs_form_radio',
						'value' => $arr[$i][$value_field],
						'id' => $field['name'].'_'.$i
					);
					if (count($row) > 0 && $row[$field['name']] == $arr[$i][$value_field]) $attrs['checked'] = 'checked';
					
					$tag .= $this->cs->html('input', $attrs).'<label for="#'.$field['name'].'_'.$i.'">'.$arr[$i][$label_field].'</label>';
				}
			}
		}
		else
		{
		
		}
		
		return $tag;
	}
	
	// create a select tag based on field data and attributes
	private function _select_tag($attr, $field, $row = array())
	{
		// is it a database based one?
		if ($field['labels'][0] == '{')
		{
			// clean and collect data required
			$field['labels'] = str_replace(array('{','}'),array('',''), $field['labels']);
			$field['values'] = str_replace(array('{','}'),array('',''), $field['values']);
			$arr = explode('.', $field['labels']);
			$table = $arr[0];
			$label_field = $arr[1];
			$arr = explode('.', $field['values']);
			$value_field = $arr[1];
			
			// query the database
			$arr = $this->getarray($table, array(
				'select' => $label_field.','.$value_field
			));
			
			$tag = '<select class="cs_form_select" name="'.$field['name'].'">'."\n";
			
			// is there a false value?
			if (isset($field['falsevalue'])) {
				$tag .= '<option value="'.$field['falsevalue'].'">';
				if (isset($field['falselabel'])) $tag .= $field['falselabel'];
				$tag .= '</option>';
			}
			
			// make sure we have data
			if (count($arr) > 0)
			{
				for ($i = 0, $count = count($arr); $i < $count; $i++)
				{
					$tag .= '<option value="'.$arr[$i][$value_field].'"';
					if ((isset($attr['value']) && $attr['value'] == $arr[$i][$value_field]) || (count($row) > 0 && $row[$field['name']] == $arr[$i][$value_field])) $tag .= ' selected="selected"';
					$tag .= '>'.$arr[$i][$label_field].'</option>'."\n";
				}
				$tag .= '</select>'."\n";
			}
			else $tag .= '<option value=""></option></select>'."\n";
		}
		else
		{
		
		}
		
		return $tag;
	}
	
	// create tables from XML files
	public function create_from_xml($files, $path, $error = true)
	{
		// make sure we have something to work with
		if (is_string($files)) $files = array($files);
		
		// if we still dont have an array error out
		if (!is_array($files)) $this->cs->error('dbcreate_from_xml - invalid argument type - Array|String accepted');
		
		// array to store table config
		// queries built will be run after all files have been processed without errors
		$sql_tables = array(); // $sql_tables[TABLE_NAME] = QUERY
		
		// loop through array of files
		foreach ($files as $file)
		{
			// build the file path
			$name = $file;
			$file = $path.$file.'.xml';
			
			// make sure the file exists before opening it
			if (!file_exists($file)) $this->cs->error('xml2db - '.$file.' does not exists');
			else $arr = $this->xml2ary(file_get_contents($file));
			
			// handle dbtable tag if present
			if (isset($arr['module'])) {
			
				if (isset($arr['module']['child']['dbtable'])) {
				
					if (0 != $this->_db_from_array($this->_dbtable_to_array($arr['module']['child']['dbtable']))) {
						$this->cs->store_write('module_'.$arr['module']['attr']['name'], $this->_array2store_str('module', $arr));
						return 2;
					}
					else {
						$this->cs->error('database::create_from_xml:database manipulation failed for table "'.$arr['module']['attr']['name'].'"!');
						return 0;
					}	
				}
				
				// store new data
				$this->cs->store_write('module_'.$arr['module']['attr']['name'], $this->_array2store_str('module', $arr));
				return 1;
			}
			else {
				
				$file_name = isset($arr['module']['attr']['name']) ? 'module_'.$arr['module']['attr']['name'] : $name;
				
				// store new data
				$this->cs->store_write($file_name, $this->_array2store_str($name, $arr));
				return 1;
			}
		}
	}
	
	public function fields_to_show($table) {
		return $this->_getshowable($table);
	}
	
	// return database error message
	public function error() {
		return $this->db->ErrorMsg();
	}
	
	// cleanse string
	public function safestr($str) {
		return $this->db->qstr($str, get_magic_quotes_gpc());
	}
	
	// clean value depending on their type
	public function dbsafe($value)
	{		
		// not sure this is really necessary???
		if (is_int($value)) return intval($value); // enforce int with intval
		elseif (is_string($value)) return $this->safestr($value); // use adodb cleaning function
		else $this->cs->error('dbclean - wrong paramater type');
	}
	
	/*
		private methods
	*/
	
	// return what the current date format character is
	private function _date_char_is_what($char)
	{
		$list = $this->_date_char_list();
		foreach ($list as $k => $v) {
			if (in_array($char, $v)) return $k;
		}
		return true;
	}
	// return what the current date format character is
	private function _date_char_list()
	{
		// return the list
		return array(
			'day' => array('d','D','j','l','N','S','w','z'),
			'week' => array('W'),
			'month' => array('F','m','M','n','t'),
			'year' => array('L','o','Y','y'),
			'hour' => array('g','G','h','H'),
			'min' => array('i'),
			'sec' => array('s'),
			'ampm' => array('a','A')
		);
	}
	
	// convert the array based on the XML definition into a string to store
	private function _array2store_str($name, $arr)
	{
		$str = '';
		
		// module tag
		if (isset($arr[$name]['attr']['name']) && !empty($arr[$name]['attr']['name'])) $str .= '$data["'.$name.'"]["name"] = "'.$arr['module']['attr']['name'].'";'."\n";
		
		// dbtable tags
		if (isset($arr['module']['child']['dbtable']))
		{
			// shortcut
			$dbtable = $arr['module']['child']['dbtable'];
			unset($arr['module']['child']['dbtable']);
			
			// force integer key formating array
			if (!isset($dbtable[0])) $dbtable = array($dbtable);
			
			// loop through dbtable tags
			foreach ($dbtable as $table)
			{
				// loop through children (field tags)
				foreach ($table['child']['field'] as $field)
				{
					// loop through the attributes
					foreach ($field['attr'] as $attr => $val) $str .= '$data["dbtable"]["'.$table['attr']['name'].'"]["'.$field['attr']['name'].'"]["'.$attr.'"] = "'.$val.'";'."\n";

				}
			}
		}
		
		// discard dbform tags for the sec
		//if (isset($arr['module']['child']['group'])) unset($arr['module']['child']['group']);
		
		
		// simple tags 
		if (isset($arr[$name]['child']))
		{
			foreach ($arr[$name]['child'] as $child_name => $child_data)
			{
				$i = 0;
				
				// loop through the attributes
				if (isset($child_data['attr']))
				{
					if (isset($child_data['attr']['name']) && !empty($child_data['attr']['name'])) $key = '"'.$child_data['attr']['name'].'"';
					else $key = $i;
					
					foreach ($child_data['attr'] as $attr => $val)
					{
						$str .= '$data["'.$child_name.'"]['.$key.']["'.$attr.'"] = "'.$val.'";'."\n";
					}
					$i++;
				}
				elseif (isset($child_data[0]))
				{
					for ($j = 0, $count = count($child_data); $j < $count; $j++)
					{
						if (isset($child_data[$j]['attr']['name']) && !empty($child_data[$j]['attr']['name'])) $key = '"'.$child_data[$j]['attr']['name'].'"';
						else $key = $j;
						
						foreach ($child_data[$j]['attr'] as $attr => $val)
						{
							$str .= '$data["'.$child_name.'"]['.$key.']["'.$attr.'"] = "'.$val.'";'."\n";
						}
					}
					$i++;
				}

			}
		}
		return $str;
	}
	
	// create date tags for date/time fields
	private function _date_tag($attributes, $field)
	{
		// some variables
		$str = '';
		$name = $attributes['name'];
		
		// sort format bits out		
		$format = isset($field['format']) ? $field['format'] : 'd-m-Y H:i:s';
		$format_arr = array();
		
		// create a clean array with the format (so we know the order of each bits)
		for ($i = 0, $count = strlen($format); $i < $count; $i++) {
			if ($format[$i] !== '-' && $format[$i] !== ':' && $format[$i] !== ' ') array_push($format_arr, $format[$i]);
		}
		
		$set = array();
		
		// now loop through the format array
		for ($i = 0, $count = count($format_arr); $i < $count; $i++) {
			array_push($set, $this->_date_char_is_what($format_arr[$i]));
		}

		// sort basic attributes out
		$attributes['close'] = true;
		$attributes['class'] = 'cs_form_select';
		unset($attributes['type']);
		
		if (isset($attributes['value']))
		{
			$datetime = explode(" ", $attributes['value']);
			$date = explode("-", $datetime[0]);
			$time = explode(":", $datetime[1]);
			$Y = $date[0];
			$M = $date[1];
			$D = $date[2];
			$h = $time[0];
			$m = $time[1];
			$s = $time[2];
			unset($attributes['value']);
		}
		else
		{
			$Y = intval(date("Y"));
			$M = intval(date("m"));
			$D = intval(date("d"));
			$h = intval(date("H"));
			$m = intval(date("i"));
			$s = intval(date("s"));
		}
		
		
		// loop through requested type
		foreach ($set as $value)
		{
			// sort element id and name out
			$attributes['name'] = $name.'__'.$value;
			$attributes['id'] = $name.'__'.$value;
			$attributes['content'] = '';
			
			// now sort out options for the select box
			switch($value)
			{
				case 'year':
					for ($i = $Y-10; $i < $Y+5; $i++)
					{
						$attributes['content'] .= '<option value="'.$i.'"';
						if ((string) $i == $Y) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$i.'</option>';
					}
						break;
				case 'month':
					for ($i = 0; $i < 12; $i++)
					{
						$v = (string) $i+1;
						if (strlen($v) == 1) $v = '0'.$v;
						$attributes['content'] .= '<option value="'.$v.'"';
						if ($v == $M) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$v.'</option>';
					}
						break;
				case 'day':
					for ($i = 0; $i < 31; $i++)
					{
						$v = (string) $i+1;
						if (strlen($v) == 1) $v = '0'.$v;
						$attributes['content'] .= '<option value="'.$v.'"';
						if ($v == $D) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$v.'</option>';
					}
						break;
				case 'hour':
					for ($i = 0; $i < 24; $i++)
					{
						$v = (string) $i;
						if (strlen($v) == 1) $v = '0'.$v;
						$attributes['content'] .= '<option value="'.$v.'"';
						if ($v == $h) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$v.'</option>';
					}
						break;
				case 'min':
					if (isset($field['mins_by'])) $i_increase = $field['mins_by'];
					else $i_increase = 1;
					$v = 0;
					for ($i = -1; $v < 60; $i++)
					{
						$v = (string) ($i+1)*$i_increase;
						if (strlen($v) == 1) $v = '0'.$v;
						$attributes['content'] .= '<option value="'.$v.'"';
						if ($v == $m) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$v.'</option>';
					}
						break;
				case 'sec':
					for ($i = 0; $i < 60; $i++)
					{
						$v = (string) $i;
						if (strlen($v) == 1) $v = '0'.$v;
						$attributes['content'] .= '<option value="'.$v.'"';
						if ($v == $s) $attributes['content'] .= ' selected="selected"';
						$attributes['content'] .= '>'.$v.'</option>';
					}
						break;
			}
			unset($attributes['value']);
			// add the select box
			$str .= $this->cs->html('select', $attributes).' ';
		}
		
		return $str;
	}
	
	// XML to Array
	public function xml2ary(&$string) {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $string, $vals, $index);
		xml_parser_free($parser);

		$mnary=array();
		$ary=&$mnary;
		foreach ($vals as $r) {
			$t=$r['tag'];
			if ($r['type']=='open') {
				if (isset($ary[$t])) {
					if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
					$cv=&$ary[$t][count($ary[$t])-1];
				} else $cv=&$ary[$t];
				if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['attr'][$k]=$v;}
				$cv['child']=array();
				$cv['child']['_p']=&$ary;
				$ary=&$cv['child'];

			} elseif ($r['type']=='complete') {
				if (isset($ary[$t])) { // same as open
					if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
					$cv=&$ary[$t][count($ary[$t])-1];
				} else $cv=&$ary[$t];
				if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['attr'][$k]=$v;}
				$cv['value']=(isset($r['value']) ? $r['value'] : '');

			} elseif ($r['type']=='close') {
				$ary=&$ary['_p'];
			}
		}    
		
		$this->_del_p($mnary);
		return $mnary;
	}

	// _Internal: Remove recursion in result array
	public function _del_p(&$ary) {
		foreach ($ary as $k=>$v) {
			if ($k==='_p') unset($ary[$k]);
			elseif (is_array($ary[$k])) $this->_del_p($ary[$k]);
		}
	}

	// Insert element into array
	public function ins2ary(&$ary, $element, $pos) {
		$ar1=array_slice($ary, 0, $pos); $ar1[]=$element;
		$ary=array_merge($ar1, array_slice($ary, $pos));
	}

	
	private function _db_from_array($arr)
	{
		$res = 0;
		$dict = NewDataDictionary($this->db);
		foreach ($arr as $var => $value)
		{
			// create and execute query
			$sql = $dict->ChangeTableSQL($var, $value);
			
			// update query count
			$this->queries_count++;
			$res = $dict->ExecuteSQLArray($sql);
		}
		return $res;
	}
	
	private function _dbtable_to_array($dbtable)
	{		
		if (!isset($dbtable[0])) $dbtable = array($dbtable);
		
		// loop through the tables
		foreach ($dbtable as $table)
		{
			// string to hold the fields data
			$fields = '';

			// loop through all the fields
			foreach ($table['child']['field'] as $_field)
			{	
				// reset field array
				$field = array();
				
				// stringify all attributes and store them
				foreach($_field['attr'] as $k => $v) $field[$k] = $v;
				
				// type as a string
				$type = (string) $field['type'];
				
				// add the file name
				$fields .= $field['name'].' '.$this->_dbtype($type);
				
				// if it is a file set size to 255
				if ('file' == $type || ('C' == $this->_dbtype($type) && !isset($field['length']))) $fields .= '(255) ';
				else
				{				
					// length attribute?
					if (isset($field['length']) && !empty($field['length']))
					{
						$length = (string) $field['length'];
						$fields .= '('.$length.') ';
					}
					else $fields .= ' ';
				}
				
				// auto incremention?
				if ('auto' == $type || 'autoincrement' == $type || 'serial' == $type) $fields .= 'AUTO ';
				
				// primary key?
				if (isset($field['primarykey'])) $fields .= 'KEY ';
				
				// default value?
				if (isset($field['default']))
				{
					// add default (handle _now_ with care
					if('now' == $field['default'])  $fields .= 'DEFDATE ';
					else $fields .= "DEFAULT '".$field['default']."' ";
				}
				
				$fields .= ',';
			}
			
			// save the fields
			$array[PREFIX.$table['attr']['name']] = substr($fields, 0, -1);
		}
		
		return $array;
	}
	
	// return the table column XML labels (cached for better performance)
	private function _getlabels($table, $select = array())
	{
		if (isset($this->tables2labels[$table])) return $this->tables2labels[$table];		
		$headers = $this->_getshowable($table);
		$data = $this->_getdata($table);
		$array = array();
		foreach ($headers as $field)
		{
			$field = is_object($field) ? (string) $field->name : (string) $field;
			if (isset($data['dbtable'][$table]['fields'][$field]['label'])) $header = $data['dbtable'][$table]['fields'][$field]['label'];
			if (!strpos($header,'__'))
			{
				if (count($select) == 0 || in_array(trim($field), $select)) $array[] = $header;
			}
		}
		return $array;
	}
	
	// return the column name of column that can be displayed by XML settings
	private function _getshowable($table)
	{
		$headers = $this->_getheaders($table);
		$data = $this->_getdata($table);
		$array = array();
		foreach ($headers as $header)
		{
			$header = is_object($header) ? (string) $header->name : (string) $header;
			if ((!isset($data['dbtable'][$table]['field'][$header]['show']) || 'true' == $data['dbtable'][$table]['field'][$header]['show'])
				&& !strpos($header, '__')) $array[] = $header;
		}
		return $array;
	}
	
	// return the table column names (headers) (cached for better performance)
	private function _getheaders($table) {
	
		if (isset($this->tables2labels[$table])) return $this->tables2labels[$table];		
		return $this->db->MetaColumns(PREFIX.$table);
	}
	
	// return the table information data (cached for better performance)
	private function _getdata($table) {
	
		if (isset($this->tables2data[$table])) return $this->tables2data[$table];		
		$data = $this->cs->store_getarray('module_'.$table);
		$this->tables2data[$table] = $data;
		return $data;
	}
	
	// return the primary key of a table (cache result)
	private function _getkey($table) {
	
		// cached?
		if (isset($this->tables2keys[$table])) return $this->tables2keys[$table];
		
		// get the tables2files data
		$data = $this->_getdata($table);
		
		// loop through the table fields in search of our primary key
		foreach ($data['dbtable'][$table] as $field) {
			if (isset($field['primarykey']) && 'true' == $field['primarykey']) {
				// cache the result
				$this->tables2keys[$table] = $field['name'];
				
				// return the key
				return $field['name'];
			}
		}
	}
	
	// validate data submitted (make sure required field there, format respected etc)
	private function _validate($posts, $fields, $update) {
		foreach($fields as $field) {
			if (isset($field['required']) && 'true' == $field['required']) {
				if (!isset($_POST[$field['name']]) || empty($_POST[$field['name']])) {
					if (($update && $field['type'] != 'password') || !$update) $this->errors[$field['name']] = 'This field is required';
				}
			}
		}
		
		// all good
		return true;
	}
	
	// extract data needed from $_POST and insert auto-insert tag if needed
	private function _extract($fields)
	{
		// array holder
		$array = array();
		$special = array('datetime','date','time');
		
		// loop through the fields given
		foreach($fields as $field)
		{
			if (!in_array($field['type'], $special)) {
				if (isset($_POST[$field['name']])) {
					$array[$field['name']] = $_POST[$field['name']];
					if ('boolean' == $field['type']) $array[$field['name']] = 1;
				}
				elseif ('boolean' == $field['type']) {
					if (!isset($field['show']) || (isset($field['show']) && $field['show'] != 'false')) $array[$field['name']] = 0;
				}
			}
			else
			{
				if (($field['type'] == 'date' || $field['type'] == 'datetime'))
				{
					$year = (isset($_POST[$field['name'].'__year']) ? $_POST[$field['name'].'__year'] : date("Y"));
					$month = (isset($_POST[$field['name'].'__month']) ? $_POST[$field['name'].'__month'] : date("m"));
					$day = (isset($_POST[$field['name'].'__day']) ? $_POST[$field['name'].'__day'] : date("d"));
					
					if ($field['name'] != 'updated' && $field['name'] != 'created')
					{
						$hour = (isset($_POST[$field['name'].'__hour']) ? $_POST[$field['name'].'__hour'] : '00');
						$min = (isset($_POST[$field['name'].'__min']) ? $_POST[$field['name'].'__min'] : '00');
						$sec = (isset($_POST[$field['name'].'__sec']) ? $_POST[$field['name'].'__sec'] : '00');
					}
					else
					{
						$hour = (isset($_POST[$field['name'].'__hour']) ? $_POST[$field['name'].'__hour'] : date('h'));
						$min = (isset($_POST[$field['name'].'__min']) ? $_POST[$field['name'].'__min'] : date('i'));
						$sec = (isset($_POST[$field['name'].'__sec']) ? $_POST[$field['name'].'__sec'] : date('s'));
					}
					
					if (strlen($day) < 2) $day = '0'.$day;
					if (strlen($month) < 2) $month = '0'.$month;
					if (strlen($hour) < 2) $hour = '0'.$hour;
					if (strlen($min) < 2) $min = '0'.$min;
					if (strlen($sec) < 2) $sec = '0'.$sec;
					
					$array[$field['name']] = $year.'-'.$month.'-'.$day.' '.$hour.':'.$min.':'.$sec;
				}
			}
		}
		
		// return the array
		return $array;
	}
	
	// convert databse type to adodb string
	private function _dbtype($var)
	{
		/*
		  C:  Varchar, capped to 255 characters.
		  X:  Larger varchar, capped to 4000 characters (to be compatible with Oracle). 
		  XL: For Oracle, returns CLOB, otherwise the largest varchar size.
		  C2: Multibyte varchar
		  X2: Multibyte varchar (largest size)
		  B:  BLOB (binary large object)
		  D:  Date (some databases do not support this, and we return a datetime type)
		  T:  Datetime or Timestamp
		  L:  Integer field suitable for storing booleans (0 or 1)
		  I:  Integer (mapped to I4)
		  I1: 1-byte integer
		  I2: 2-byte integer
		  I4: 4-byte integer
		  I8: 8-byte integer
		  F:  Floating point number
		  N:  Numeric or decimal number
		*/ 
		$array = array(
		
			// Varchar, capped to 255 characters.
			'varchar' 	=> 'C',
			'char' 		=> 'C',
			'password' 	=> 'C',
			'file' 		=> 'C',
			
			// Multibyte varchar
			'text' 		=> 'X',
			
			// Multibyte varchar (largest size)
			'longtext' 	=> 'X2',
			
			// 4-byte integer
			'auto'		=> 'I',
			'int'		=> 'I',
			'integer'	=> 'I',
			
			// boolean
			'boolean' 	=> 'L',
			
			// Floating point number
			'float'		=> 'F',
			
			// Numeric or decimal number
			'decimal' 	=> 'N',
			
			// Datetime or Timestamp
			'date' 		=> 'T',
			'time' 		=> 'T',
			'datetime' 	=> 'T'
		);
		
		// return Adodb type
		return $array[$var];
	}
}
?>