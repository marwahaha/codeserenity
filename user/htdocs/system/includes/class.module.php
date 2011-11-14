<?php

class module {

	// public properties
	public $sys_dir;
	public $code_dir;
	public $init_file;
	public $tpl_file;
	public $path = '/';
	public $vars = array();
	public $config = array();
	public $name;
	public $options;
	public $filters;
	public $item;
	public $items;
	public $items_headers;
	public $lang = array();
	
	// private properties
	private $data = array();
	private $lang_cache = array();
	private $crumbs = array();

	// constructor
	public function __construct() {

		// grab codeserenity and CMS objects
		global $cms, $cs;
		
		/*		
			Take this logic out to the init file or as an include
			the reson being is that it makes more sense to path the module name
			in the construct function that way we can overwrite any module loaded
			at any stage after the original module is loaded if necessary.

			Need more work as well so that this class can be use externally as well
			so that multiple module can be loaded at the same time. Think of a clever way
			to assign them to the template engine. Maybe using the module name and a mod_ prefix.
			
			Store the module data in one more dimension so that module can be looped over in the CMS class
			for assignement. Also change the template so that it is provided a loop of module template to load.
			
			The Goal is to be able to handle multiple module into a single page (unbeatable :))
			
			This is min multiple module can have the same path if one the same page.
			
		*/

		// admin request
		if ($cms->is_admin) {
			
			// sort out paths and module to load
			if (isset($cs->path[2]) && $cs->path[2] != 'action') {
				if ($cs->dbtable_data_exists($cs->path[2])) {
					$this->name = $cs->path[2];
					$this->path = '/'.$cms->get('folder').'/'.$cs->path[1].'/'.$this->name.'/';
					$this->crumbs[] = $cs->path[1];
					$this->crumbs[] = $this->name;
				}
				else {
					$this->name = $cs->path[1];
					$this->path = '/'.$cms->get('folder').'/'.$this->name.'/';
				}
			}
			else {
				$this->name = $cs->path[1];
				$this->path = '/'.$cms->get('folder').'/'.$this->name.'/';
					$this->crumbs[] = $this->name;
			}
			
			// check for administraton permissions
			// extend to something a  lot better
			// we overwite the module to load with the login one, the path is left untouched
			if (!isset($_SESSION['admin'])) {
				$this->name = 'login';
				$this->tpl_file = 'login';
			}
		}
		else {
			$this->path = $cms->page->url.'/';
			$this->name = $cms->page->get('module');
		}

		// if the module as a table grab the data for the module
		/*
			Need to be done better here as a module can have a definition XML without having a table		
		*/
		$this->config = $cs->dbtable_data($this->name);

		// set the module directories paths
		$this->sys_dir = CMS_MODULES.$this->name.'/'.$cms->get('folder').'/';
		$this->default_dir = CMS_MODULES.'default/'.$cms->get('folder').'/';
		$this->code_dir = CMS_CODE_MODULES.$this->name.'/'.$cms->get('folder').'/';

		// make sure the module folder exists
		if (!is_dir($this->sys_dir) && !is_dir($this->code_dir) && !is_dir($this->default_dir)) $cms->error('module_folder_missing', $this->code_dir.' OR '.$this->sys_dir.' OR '.$this->default_dir);

		// set the module init file to use
		if (file_exists($this->code_dir.'init.php')) $this->init_file = $this->code_dir.'init.php';
		if (!is_string($this->init_file) && file_exists($this->sys_dir.'init.php')) $this->init_file = $this->sys_dir.'init.php';
		if (!is_string($this->init_file) && file_exists($this->default_dir.'init.php')) $this->init_file = $this->default_dir.'init.php';

		// make sure we have an init file
		if (!is_string($this->init_file)) $cms->error('module_init_missing', $this->code_dir.'init.php OR '.$this->sys_dir.'init.php OR '.$this->default_dir.'init.php');

		// set module variables
		$this->set_vars();
		
		// load module language file
		$this->init_language();
		
		// set basic crumbs
		$this->init_crumbs();
	}
	
	// create initial bread crumbs for the module
	public function init_crumbs() {

		// grab CMS and codeserenity objects
		global $cms, $cs;
		
		$url = '/admin/';
		
		// loop through internal crumbs
		for ($i = 0, $count = count($this->crumbs); $i < $count; $i++) {
			
			// shortcut for module name
			$name = $this->crumbs[$i];
			
			// grab language file
			$lang = $this->get_language_array($name);
			
			// sort label out 
			if (isset($lang[$name])) $label = $lang[$name];
			elseif (isset($this->config['dbtable'][$this->name][$v['name']]['label'])) $label = $this->config['dbtable'][$this->name][$v['name']]['label'];
			else $label = is_string($cms->language->str($name)) ? $cms->language->str($name) : ucwords(str_replace('_', ' ', $name));
			
			// sort url out
			$url .= $name.'/';
			
			// add to CMS crumbs
			$cms->crumbs[] = array(
				'depth' => $i,
				'label' => $label,
				'name' => $name,
				'url' => $url
			);
		}
		
		// check for parent id
		if (isset($_GET['pid'])) {
		
			$loop = true;
			
			if (isset($this->config['admin'][0]['list'])) {
				$list = explode(',', $this->config['admin'][0]['list']);
				foreach ($list as $v) {
					if ('position' != $v) {
						$col = $v;
						break;
					}
				}
				$pid = $_GET['pid'];
				$array = array();
				$depth = count($cms->crumbs)-1;
			} else $loop = false;
			
			// loop to build parents into
			while ($loop) {
				$arr = $cs->dbarray($this->name, array('where' => 'id='.$pid));
				if (!isset($arr[0]['parent_id'])) $loop = false;
				else {
					$url = $this->path.'?pid='.$arr[0]['id'];
					$array[] = array(
						'depth' => $depth,
						'label' => $arr[0][$col],
						'name' => isset($arr[0]['name']) ? $arr[0]['name'] : '',
						'url' => $url
					);
					$depth++;
					$pid = $arr[0]['parent_id'];
					if ($pid == 0) $loop = false;
				}
			}
			for ($c = count($array); $c > 0; $c--) {
				$cms->crumbs[] = $array[$c-1];
			}
			/*
			for ($count = count($array); 0 < $count; $count--) {
				$cms->crumbs[] = $array[$count];
			}*/
		}
	}
	
	public function getfile($file) {
		global $cms;
		
		$filename = '';
		
		// set the module init file to use
		if (file_exists($this->code_dir.$file.'.php')) $filename = $this->code_dir.$file.'.php';
		if (empty($filename) && file_exists($this->sys_dir.$file.'.php')) $filename = $this->sys_dir.$file.'.php';
		if (empty($filename) && file_exists($this->default_dir.$file.'.php')) $filename = $this->default_dir.$file.'.php';
		
		if (file_exists($filename)) return $filename;
		else $cms->error('module_file_missing', $filename);
	}
	
	private function init_language() {		
		$this->lang = $this->get_language_array($this->name);
	}
	
	public function get_language_array($mod) {
		
		// grab CMS object
		global $cms;
		
		// check for cache first
		if (in_array($mod, $this->lang_cache)) return $this->lang_cache[$mod];
		
		// check for language file presence in the module folder
		if (file_exists(CMS_MODULES.$mod.'/'.$cms->get('folder').'/languages/'.$cms->language->value.'.php')) {
			$file = CMS_MODULES.$mod.'/'.$cms->get('folder').'/languages/'.$cms->language->value.'.php';
		}
		
		// check for language file presence in the module folder
		if (file_exists(CMS_CODE_MODULES.$mod.'/'.$cms->get('folder').'/languages/'.$cms->language->value.'.php')) {
			$file = CMS_CODE_MODULES.$mod.'/'.$cms->get('folder').'/languages/'.$cms->language->value.'.php';
		}
		
		// include the file
		if (isset($file)) {
			require $file;
			if(isset($lang)) {
				$this->lang_cache[$mod] = $lang;
				return $lang;
			}
		}
	}
	
	// create items data for the template engine based on the XML definition
	// uses the currently set module name or a given one (so other module data can be called within another module)
	public function init_items($settings = array()) {
		
		// grab codeserenity and CMS objects
		global $cms, $cs;
		
		// make sure we have a table for the module
		if ($cs->dbtable_exists($this->name)) {
			
			// does it has a valid list first (attribute present and not empty
			if (!isset($this->config[$cms->get('folder')][0]['list']) || empty($this->config[$cms->get('folder')][0]['list'])) $cms->error('module_list_attribute_empty', '/'.$cms->get('folder').'/');
			
			// shortcut variable
			$list = $this->config[$cms->get('folder')][0]['list'];
			
			// array of the list without modification so we can check which field need displaying
			$visibles = explode(',', $list);			

			// declare items_headers as an array
			$this->items_headers = array();
			
			// loop through dbtable fields
			foreach ($this->config['dbtable'][$this->name] as $k => $v) {
			
				// make sure the field is in the list
				if (in_array($v['name'], $visibles)) {					
					
					// sort label out 
					if (isset($this->lang['field_'.$v['name']])) $label = $this->lang['field_'.$v['name']];
					elseif (isset($this->config['dbtable'][$this->name][$v['name']]['label'])) $label = $this->config['dbtable'][$this->name][$v['name']]['label'];
					else $label = is_string($cms->language->str('field_'.$v['name'])) ? $cms->language->str('field_'.$v['name']) : ucwords(str_replace('_', ' ', $v['name']));
					
					// add data to the item_headers array
					$this->items_headers[] = array(
						'name' =>  $v['name'],
						'label' => $label
					);
				}
			}
			
			// check for updated field (grab if present so we we provide i to the template)
			if (isset($this->config['dbtable'][$this->name]['updated'])) {
				$has_updated = true;
				$list .= ',updated';
			} else $has_updated = false;
			
			// check for status field (grab if present, in admin mode we generate tools to change status from the list)
			if (isset($this->config['dbtable'][$this->name]['status'])) {
				$has_status = true;
				$list .= ',status';
			} else $has_status = false;
			
			// query the database for the items
			$rows = $cs->dbarray($this->name, array(
				'select' => 'id,'.$list,
				'where' => isset($settings['where']) ? $settings['where'].$this->do_filters_where(true) : $this->do_filters_where(),
				'orderby' => isset($this->config[$cms->get('folder')][0]['order-by']) ? $this->config[$cms->get('folder')][0]['order-by'] : ''
			));
			
			// check for actions attribute
			if (isset($this->config[$cms->get('folder')][0]['actions']) && !empty($this->config[$cms->get('folder')][0]['actions'])) {
				$actions = explode(',', $this->config[$cms->get('folder')][0]['actions']);
			} else $actions = false;
			
			// loop through the result rows to build our items array
			for ($i =0, $count = count($rows); $i < $count; $i++) {
				
				// items data holdig array
				$arr = array();
				
				// grab update
				if ($has_updated) $updated = $rows[$i]['updated'];
				else $updated = false;
				
				// now we loop through the fields
				foreach ($rows[$i] as $k => $v) {				
					
					// build items rows array
					$arr[$k] = array(
						'name' => $k,
						'value' => $v,
						'visible' => (in_array($k, $visibles)) ? true : false
					);				
				}
				
				// build items array
				$this->items[] = array(
					'id' => $rows[$i]['id'],
					'fields' => $arr,
					'updated' => $updated,
					'has_children' => isset($this->config['dbtable'][$this->name]['parent_id']) ? true : false,
					'actions' => $actions ? $this->do_item_actions($rows[$i]['id'], $actions) : false, // return a multi-dimensional array
					'status' => $has_status ? $this->do_item_status($rows[$i]['id'], $rows[$i]['status']) : false // return an array
				);
			}
		}
	}
	
	// build a where clause based on the XML definition filters settings and the request variables available
	public function do_filters_where($append = false) {
		
		// grab codeserenity and CMS object
		global $cs, $cms;
		
		// holding where string
		$where = $and = '';
		if ($append) $and = ' AND ';
		
		// Check for keywords-filter first
		$filters = (isset($this->config[$cms->get('folder')][0]['keywords-filter']) && !empty($this->config[$cms->get('folder')][0]['keywords-filter'])) ? $this->config[$cms->get('folder')][0]['keywords-filter'].',' : false;
		
		// first check there is filters or keywords-filter attributes in the XML definition
		if ($filters) {		
			
			$filters = array_unique(explode(',', $filters));
			foreach ($filters as $filter) {
				
				if (isset($_REQUEST['filter_keyword']) && !empty($_REQUEST['filter_keyword']) && !empty($filter)) {
					$where .= $and.$filter.' LIKE '.$cs->dbstring('%'.$_REQUEST['filter_keyword'].'%');
					if (!empty($where)) $and = ' AND ';
				}
			}
		}
		
		// other filters
		$filters = (isset($this->config[$cms->get('folder')][0]['filters']) && !empty($this->config[$cms->get('folder')][0]['filters'])) ? $this->config[$cms->get('folder')][0]['filters'].',' : false;
		
		// first check there is filters or keywords-filter attributes in the XML definition
		if ($filters) {		
			
			$filters = array_unique(explode(',', $filters));
			foreach ($filters as $filter) {
				
				if (isset($_REQUEST[$filter]) && !empty($_REQUEST[$filter])) {
					if (is_int($_REQUEST[$filter]))  $where .= $where_next.$filter.' = '.intval($_REQUEST[$filter]);
					else $where .= $and.$filter.' LIKE '.$cs->dbstring('%'.$_REQUEST[$filter].'%');
					if (!empty($where)) $and = ' AND ';
				}
			}
		}
		
		// check for parent filtering
		if (isset($this->config['dbtable'][$this->name]['parent_id'])) {
			if (isset($_REQUEST['pid'])) $where .= $and.'parent_id='.intval($_REQUEST['pid']);
			else $where .= $and.'parent_id=0';
		}
		
		// return the where clasue
		return $where;
	}
	
	// build an array of actions for an item
	public function do_item_actions($id, $actions) {

		// grab CMS object
		global $cms;
		
		// holding array
		$arr = array();
		
		// loop through actions array
		foreach($actions as $action) {
		
			// sort out the label for it
			if (isset($this->lang['action_'.$action]) && !empty($this->lang['action_'.$action])) $label = $this->lang['action_'.$action];
			else $label = is_string($cms->language->str('action_'.$action)) ? $cms->language->str('action_'.$action) : ucwords(str_replace('_',' ', $action));
			
			// add action array
			$arr[] = array(
				'url' => $this->path.'action/'.$action.'/'.$id,
				'name' => $action,
				'label' => $label
			);
		}
		
		// return array
		return $arr;
	}
	
	// build the status tools
	public function do_item_status($id, $status) {
		
		// grab CMS object
		global $cms;
		
		// sort action based on status
		$action = ($status == 1) ? 'disable' : 'enable';
		$state = ($status == 0) ? 'disabled' : 'enabled';
		
		// sort out the label for it
		if (isset($this->lang['action_'.$action])) $label = $this->lang['action_'.$action];
		else $label = is_string($cms->language->str('action_'.$action)) ? $cms->language->str('action_'.$action) : ucwords(str_replace('_',' ', $action));
		
		// return data array
		return array(
			'url' => $this->path.'action/'.$action.'/'.$id,
			'name' => 'status',
			'label' => $label,
			'state' => $state
		);
	}
	
	// create filters data for the template engine based on the XML definition
	// uses the currently set module name or a given one (so other module data can be called within another module)
	public function init_filters($settings = array()) {
		
		// grab codeserenity and CMS objects
		global $cms, $cs;

		// current module or custom module?
		$mod = isset($settings['module']) ? $settings['module'] : $this->name;

		// module requested the same as current module?
		if ($mod = $this->name) {

			// grab data from config array
			$data = $this->config;
			
			// grab language file from the language object
			$lang = $this->lang;
			
		} else {
			
			// request module data
			$data = $cs->dbtable_data($mod);
			
			// request the language file for the module
			$lang = $this->get_language_array($mod);				
		}
			
		// create an holding array for the filters data
		$arr = array();
		
		// make sure we have a tag with the filters attribute
		if (isset($data[$cms->get('folder')][0]['keywords-filter'])) {
		
			// sort value out
			$value = isset($_REQUEST['filter_keyword']) ? $_REQUEST['filter_keyword'] : '';
				
			// store data
			$arr[] = array(
				'name' => 'filter_keyword',
				'label' => $cms->language->str('label_keyword_s'),
				'input' => $cs->html('input', array(
					'type' =>'text',
					'name' => 'filter_keyword',
					'class' => 'cs_form_text form-filter_keyword',
					'value' => $value
				))
			);
		}
		
		// make sure we have a tag with the filters attribute
		if (isset($data[$cms->get('folder')][0]['filters'])) {
		
			// refine to just the filters data
			$filters = $data[$cms->get('folder')][0]['filters'];
			
			// convert filters to an array
			$filters = explode(',', $filters);
			
			// loop through filters
			foreach ($filters as $filter) {
			
				// sort out the label (language file -> label attribute -> filter value)
				if (isset($lang['field_'.$filter])) $label = $lang['field_'.$filter];
				elseif (isset($data['dbtable'][$mod][$filter]['label'])) $label = $data['dbtable'][$mod][$filter]['label'];
				else $label = ucwords(str_replace('_', ' ', $filter));
				
				$value = isset($_REQUEST[$filter]) ? $_REQUEST[$filter] : '';
				
				// store data
				$arr[] = array(
					'name' => $filter,
					'label' => $label,
					'input' => $cs->dbinput($mod, $data['dbtable'][$mod][$filter]['name'], $value)
				);
			
			}
		}
		
		// assign to public property if there is data
		if (count($arr)) $this->filters = $arr;
	}
	
	// create options data for the template engine based on the XML definition
	// uses the currently set module name or a given one (so other module data can be called within another module)
	public function init_options($settings = array()) {
		
		// grab codeserenity and CMS objects
		global $cms, $cs;
		
		// current module or custom module?
		$mod = isset($settings['module']) ? $settings['module'] : $this->name;
		
		// module requested the same as current module?
		if ($mod = $this->name) {
			
			// grab data from config array
			$options = $this->config;
			
			// grab language file from the language object
			$lang = $this->lang;
			
		} else {
			
			// request module data
			$options = $cs->dbtable_data($mod);
			
			// request the language file for the module
			$lang = $this->get_language_array($mod);
			
		}
		
		// make sure we have a tag with the option attribute
		if (isset($options[$cms->get('folder')][0]['options'])) {
		
			// refine to just the options data
			$options = $options['admin'][0]['options'];
			
			// convert options to an array
			$options = explode(',', $options);
			
			// create an holding array for the options data
			$arr = array();
			
			// loop through options array
			for ($i = 0, $count = count($options); $i < $count; $i++) {
				
				if (isset($lang[$options[$i]])) $label = $lang[$options[$i]];
				elseif (is_string($cms->language->str('action_'.$options[$i]))) $label = $cms->language->str('action_'.$options[$i]);
				else $label = ucwords(str_replace('_','',$options[$i]));
				
				$name = $options[$i];
				if (!empty($_SERVER['QUERY_STRING'])) $url = $name.'?'.$_SERVER['QUERY_STRING'];
				else $url = $name;
				
				$arr[] = array(
					'url' => $url,
					'name' => $name,
					'label' => $label
				);
			}
			
			// store the resulting array
			if (count($arr)) $this->options = $arr;
		}
	}
	
	// generate and return a list of the XML definition used by the system
	public function get_xml_list() {
		
		// grab codeserenity object
		global $cs;

		// get both path directory list
		$sysarr = $cs->dirlist(CMS_MODULES, 'dir');
		$codearr = $cs->dirlist(CMS_CODE_MODULES, 'dir');
		
		// merge them into one array
		$folders = $cs->extend($sysarr, $codearr);
		
		// declare a blank array to store results
		$list = array();
		
		// loop through folders
		for ($i = 0, $count = count($folders); $i < $count; $i++) {
			
			// track aquisition of a defintion file
			$done = false;
			
			// check for XML definition presence in the code folder
			if (file_exists(CMS_CODE_MODULES.$folders[$i].'/def.xml')) {
				$list[] = CMS_CODE_MODULES.$folders[$i].'/def.xml';
				$done = true;
			}
			
			// if we still don"t have a file we check the system folder
			if (file_exists(CMS_MODULES.$folders[$i].'/def.xml') && !$done) $list[] = CMS_MODULES.$folders[$i].'/def.xml';
		}
		
		// return both the files list and the folder list (not all module have XML definition)
		return array(
			'files' => $list,
			'folders' => $folders
		);
	}
	
	// check for WYSIWYG needs, create initialization string and store data
	public function wysiwyg() {
		
		// grab CMS object
		global $cms;
		
		// do we need WYSIWYG interface
		if (isset($this->config['wysiwyg'])) {
			
			// grab fields
			$fields = isset($this->config['wysiwyg'][0]['fields']) ? $this->config['wysiwyg'][0]['fields'] : '';
			
			// make sure we have some fields before we continue
			if (!empty($fields)) {
				
				// tiny MCE include
				$file = '<script src="/themes/shared/js/tiny_mce/tiny_mce.js"></script>';
				/*
				
					Here we need to create a new function that will generate the tiny settings string
					based on the main config XML definition or the requested module XML definition
				
				*/
				// tiny MCE initialization
				$init = '<script>tinyMCE.init({
				mode : "exact",
				elements : "'.$this->config['wysiwyg'][0]['fields'].'",
				theme : "advanced",
				skin : "o2k7",
				skin_variant : "silver",
				auto_cleanup_word : true,
				plugins : "safari,pagebreak,style,layer,table,advhr,advimage,advlink,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,paste",
				extended_valid_elements : "span[style],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style]",
				theme_advanced_buttons1 : "forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,blockquote,|,link,unlink,|,hr,anchor,|,pasteword,|,code,|,fullscreen",
				theme_advanced_buttons2 : "",
				theme_advanced_buttons3 : "",
				theme_advanced_blockformats : "p,h2,h3,h4,h5,h6",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				theme_advanced_resize_horizontal : false,
				doctype: "<!DOCTYPE html>",
				width : "100%",
				height : "400px",
				relative_urls : false,
				remove_script_host : true,
				content_css : "/themes/'.$cms->get('theme').'/public/assets/css/init.css",
				body_class : "wysiwyg"
			});</script>';
			
			$cms->page->set('wysiwyg', array(
				'file' => $file,
				'init' => str_replace(
					array("\n","\r","	"),
					array("","",""),
					$init
				)
			));
			}
		}
	}
	
	// build the wysiwyg setting
	public function wysiwyg_init_string($arr = array()) {
		
		// make sure the wysiwyg is setup
		if (isset($this->config['wysiwyg'][0])) {
		
			// default settings
			$defaults = $this->config['wysiwyg'][0];
		
		}
	}
	
	// set template file to use
	public function set_template($file) {
		$this->tpl_file = $file;
	}
	
	// set a new data to be stored
	public function get($var) {
		return $this->data[$var];
	}
	
	// return the data array of the object
	public function getdata() {
		return $this->data;
	}
	
	// return the data array of the object
	public function setdata($data) {
		return $this->data = $data;
	}
	
	// set a variale in the data array
	public function set($var, $value) {
		$this->data[$var] = $value;
	}
	
	// delete a variable of the data array
	public function del($var) {
		unset($this->data[$var]);
	}
	
	// set module variables (aka module path bits)
	private function set_vars() {
	
		global $cs;
		$str = str_replace($this->path, '', $cs->url['path'].'/');
		$arr = explode('/', $str);
		foreach ($arr as $k => $v) {
			if (!empty($v)) $this->vars[$k] = $v;
		}
	}

}

?>