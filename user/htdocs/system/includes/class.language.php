<?php

class language {

	// public properties
	public $value = 'en';
	
	// private properties
	private $errors = array();
	private $strings = array();
	
	// constructor
	public function __construct() {
	
		// grab CMS obejct
		global $cms;
		
		// make sure the file exists
		if (file_exists(CMS_LANGUAGES.$cms->get('folder').'/'.$this->value.'.php')) {
			
			// call the file
			require CMS_LANGUAGES.$cms->get('folder').'/'.$this->value.'.php';
			
			// check for code overwrite file
			if (file_exists(CMS_CODE_LANGUAGES.$cms->get('folder').'/'.$this->value.'.php'))  {
				require CMS_CODE_LANGUAGES.$cms->get('folder').'/'.$this->value.'.php';
			}
			
			// assign data
			$this->errors = $lang_error;
			$this->strings = $lang;
		}
	}
	
	public function str($key) {
		if (isset($this->strings[$key])) return $this->strings[$key];
		else return false;
	}
	
	public function error($key) {
		if (isset($this->errors[$key])) return $this->errors[$key];
		else return false;
	}
	
	public function getstrings() {
		return $this->strings;
	}
	
	public function geterrors() {
		return $this->errors;
	}

}

?>