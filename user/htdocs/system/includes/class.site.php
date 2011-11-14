<?php

class site {

	public $data = array();
	
	public function __construct() {
		
		// grab code serenity
		global $cs;

		// look for a record with the requested domain
		$data = $cs->dbarray('sites', array('where' => 'domain = "'.$_SERVER['SERVER_NAME'].'"'));

		// if we have no result look for default record
		if (count($data) === 0) $data = $cs->dbarray('sites', array('orderby' => 'position ASC'));
		
		// store data
		$this->data = $data[0];
		
		// check for basic auth settings
		if (!empty($this->data['auth_username'])) {
			$cs->basic_auth(array(
				'title' => $this->data['domain'],
				'user' => $this->data['auth_username'],
				'pw' => $this->data['auth_password']
			));
		}
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