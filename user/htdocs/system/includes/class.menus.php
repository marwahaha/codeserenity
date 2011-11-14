<?php

class menus {

	// private properties
	private $data = array();

	// constructor
	public function __construct() {

		// grab codeserenity and CMS objects
		global $cms;

		// set menus data
		if ($cms->is_admin) $this->admin_menus();
		else $this->public_menus();
	}
	
	// public menus (using the pages table)
	private function public_menus() {

		// grab codeserenity and CMS objects
		global $cs, $cms;

		// query the database for anything under and equal to the current level + 1
		// so we grab one level under if present, make it easier to display submenus of the menu open
		$arr = $cs->dbarray('pages', array(
			'where' => 'depth <= '.($cms->page->depth+1).' AND status = 1 AND site_id = '.$cms->site->get('id'),
			'orderby' => 'position ASC'
		));
		
		$keys = array();

		// build the menus arrays
		for ($i = 0, $count = count($arr); $i < $count; $i++) {

			// check if the page is active (meaning within the requested path)
			if ((!$cs->path[0] && $arr[$i]['depth'] == 0 && $i == 0) || (isset($cs->path[$arr[$i]['depth']]) && $cs->path[$arr[$i]['depth']] == $arr[$i]['name'])) {
				$arr[$i]['__active'] = true;
			}

			// store in format depthX and parentX for easy access
			//$this->data['depth'.$arr[$i]['depth']][] = $arr[$i];
			$this->data['parent'][$arr[$i]['parent_id']][] = $arr[$i];
			
			// here take care of special menu_X items but only for dept = 0 items
			if ($arr[$i]['depth'] == 0) {

				// loop through the column
				foreach ($arr[$i] as $k => $v) {

					// do we find menu_X pattern?
					if (strpos($k, 'menu_') !== false) {

						// check item is allow in this menu
						if ($arr[$i][$k] == 1) {

							// build reference
							$key = str_replace('menu_', '', $k);

							// store the $key in an array if not present already
							if (!in_array($key, $keys)) $keys[] = $key;

							// store the menu data
							$this->data[$key][] = $arr[$i];
						}
					}
				}
			}
		}
		
		// go through arrays again and build submenus if needed
		for ($i = 0, $count = count($keys); $i < $count; $i++) {
			for ($j = 0, $count2 = count($keys); $j < $count2; $j++) {
				if (isset($this->data[$keys[$i]][$j]['__active'])) {
					$this->submenus($keys[$i], $this->data[$keys[$i]][$j]['id']);
				}
			}
		}
	}
	
	// build submenus from the menu_X pattern items
	private function submenus($key, $id) {
		if (isset($this->data['parent'][$id])) {
			$this->data[$key.'__sub'] = $this->data['parent'][$id];
			foreach ($this->data['parent'][$id] as $v) {
				if ($v['__active']) $this->submenus($key.'__sub', $v['id']);
			}
		}
	}
	
	// administration menus (using the modules table)
	private function admin_menus() {
		
		// grab codeserenity object
		global $cs, $cms;
		
		// set the first item
		$this->data['main'][] = array(
			'name' => 'overview',
			'label' => 'Overview',
			'__active' => ($cs->path[1] == 'overview') ? true : false,
			'__has_sub' => false
		);
		
		// grab list of installed modules from the database
		$arr = $cs->dbarray('modules', array(
			'where' => 'status = 1',
			'orderby' => 'position ASC'
		));
		
		// loop through results
		for ($i = 0, $count = count($arr); $i < $count; $i++) {
			if ($arr[$i]['parent_id'] == 0) $this->data['main'][] = $arr[$i];
			else $this->data['main_sub'][$arr[$i]['parent_id']][] = $arr[$i];
		}
		
		// loop through main items again
		for ($i = 0, $count = count($this->data['main']); $i < $count; $i++) {
			
			// mark item has having sub items if it's the case
			if (isset($this->data['main_sub'][$this->data['main'][$i]['id']])) {
				$this->data['main'][$i]['__has_sub'] = true;
			}
			
			// mark the item as active if needed
			if ($this->data['main'][$i]['name'] == $cs->path[1]) {
				$this->data['main'][$i]['__active'] = true;
			}
		
			// loop through subitems
			for ($j = 0, $count2 = count($this->data['main_sub'][$this->data['main'][$i]['id']]); $j < $count2; $j++) {
				
				// mark the item as active if needed
				if ($this->data['main_sub'][$this->data['main'][$i]['id']][$j]['name'] == $cs->path[2]) {
					$this->data['main_sub'][$this->data['main'][$i]['id']][$j]['__active'] = true;
					$this->data['main'][$i]['__open'] = true;
				}
			}
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