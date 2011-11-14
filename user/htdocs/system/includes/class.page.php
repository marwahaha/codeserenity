<?php

class page {

	// public properties
	public $config = array();
	public $parents = array();
	public $depth = 0;
	public $path = array();
	public $url = '';
	
	// private properties	
	private $data = array();

	public function __construct() {
	
		// grab codeserenity and the cms objects
		global $cs, $cms;
		
		// grab XML configuration
		$this->config = $cs->dbtable_data('pages');
		
		// load public or admin page depending on CMS mode
		if ($cms->is_admin) $this->admin_page();
		else $this->public_page();
	}
	
	// generate data for the admin view page
	private function admin_page() {
	
		// set depth, here it is fixed other bits are module arguments
		$this->depth = 2;	
	}

	// generate data for the public view page
	private function public_page() {
	
		// grab codeserenity and the cms objects
		global $cms, $cs;
		
		// loop through paths
		foreach($cs->path as $k => $v) {
			
			// only use integer key values
			if (is_int($k)) {
			
				// query the database
				if ($data = $cs->dbarray('pages', array('where' => 'name LIKE "'.$v.'" AND depth = '.$this->depth.' AND status = 1 AND site_id = '.$cms->site->get('id')))) {
					$cleaned = $this->clean_escapes($data[0]);
					// store all information about all parents, can be useful, check memmory usage tho and filter content data if it uses too much memory
					$this->parents[$this->depth] = $cleaned;					
					$this->data = $cleaned;
					$this->url .= '/'.$this->data['name'];
					$this->path[] = $this->data['name'];
					
					// add data to the CMS crumbs (we build crumbs in the CMS object as both page and module can add to it
					$cms->crumbs[] = array(
						'depth' => $this->data['depth'],
						'name' => $this->data['name'],
						'label' => $this->data['label'],
					);
					
					// increase depth
					$this->depth++;
				}
			}
		}
	}
	
	public function clean_escapes($arr) {
		
		// if we don't have a wysiwyg tag return array
		if (!isset($this->config['wysiwyg'])) return $arr;
		
		$fields = explode(',', $this->config['wysiwyg'][0]['fields']);
		
		// loop through the array
		foreach ($arr as $k => $v) {
		
			// clean if needed
			if (in_array($k, $fields)) $arr[$k] = str_replace('\"', '"', $v);
		}
		
		// return clean array
		return $arr;
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