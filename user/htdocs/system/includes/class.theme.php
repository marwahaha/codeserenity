<?php

class theme {

	// private properties
	private $data = array();

	// constructor
	public function __construct($theme, $folder) {
	
		// grab the CMS object
		global $cms;
		
		// set some data for fast access to assets from the templates
		$this->data['path'] = '/'.str_replace(CMS_ROOT, '', CMS_THEMES);
		$this->data['folder'] = '/'.str_replace(CMS_ROOT, '', CMS_THEMES).$theme.'/'.$folder.'/assets/';
		$this->data['css'] = $this->data['folder'].'css/';
		$this->data['img'] = $this->data['folder'].'img/';
		$this->data['js'] = $this->data['folder'].'js/';
		$this->data['shared'] = array(
			'css' => $this->data['path'].'shared/css/',
			'js' =>  $this->data['path'].'shared/js/'
		);
	}
	
	// set a new data to be stored
	public function get($var) {
		return $this->data[$var];
	}
	
	// return the data array of the object
	public function getdata() {
		return $this->data;
	}
	
	// set a variale in the data array
	public function set($var, $value) {
		$this->data[$var] = $value;
	}
	
	// delete a variable of the data array
	public function del($var) {
		unset($this->data[$var]);
	}

}

?>