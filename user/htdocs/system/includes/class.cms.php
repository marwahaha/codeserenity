<?php

class cms {

	// public objects
	public $site;
	public $language;
	public $page;
	public $module;
	public $theme;
	public $config;
	public $cssbin = array();
	public $jsbin = array();
	public $crumbs = array();
	
	// public properties
	public $is_admin = false;
	public $display_menus = true;
	
	// private properties
	private $data = array();
	private $start_time;
	private $load_time;
	
	// initialize the CMS
	public function __construct() {

		// grab codeserenity
		global $cs;
		
		// start CMS load counter
		$this->start_timer();
		
		// grab CMS config		
		$this->config = $cs->store_getarray('config', CMS_CODE);
		
		// Basic auth check/use
		$this->authenticate();
		
		// check for admin request
		if ($cs->path[0] == 'admin') {
			if (!isset($cs->path[1])) $this->redirect('/admin/overview');
			$this->data['folder'] = 'admin';
			$this->is_admin = true;
		}
		else $this->data['folder'] = 'public';
		
		if ($this->is_admin && !isset($_SESSION['admin'])) $this->display_menus = false;
		
		// set default template file to use
		$this->data['template_file'] = 'default';
		
		// set the language (improve later for multiple languages support)
		$this->data['lang'] = $this->get_lang_str();
		
		// set the charset (improve later for multiple charsets support)
		$this->data['charset'] = $this->config['output'][0]['charset'];
		
		// set the charset (improve later for multiple charsets support)
		$this->data['content_type'] = $this->config['output'][0]['type'];
		
		// sort out browser information
		$this->data['browser'] = $this->browser_info();
	}
	
	// set a report message which can be collected in the template
	// type: error,warning,message,success (red/amber/blue/green)
	public function report($msg, $type = 'message', $title = '') {
		$report = array(
			'title' => $title,
			'type' => $type,
			'msg' => $msg
		);
		$_SESSION['cms_report'] = $report;
		$this->data['report'] = $report;
	}
	
	// check if authentication is required and prompt if it is
	private function authenticate() {
	
		// grab code serenity
		global $cs;
		
		// check for authentication data in the config
		if (isset($this->config['authentication'][0])) {
		
			// set as false unless requirements are met
			$auth = false;
			
			// make sure we have both username and password set
			if (isset($this->config['authentication'][0]['username']) && isset($this->config['authentication'][0]['password'])) $auth = true;
			
			// make sure the tag is not disabled
			if (isset($this->config['authentication'][0]['enable']) && 'false' == $this->config['authentication'][0]['enable']) $auth = false;
			
			// if requirements are met continue
			if ($auth) {
			
				// temporary basic auth protection
				$cs->basic_auth(array(
					'title' => isset($this->config['authentication'][0]['title']) ? $this->config['authentication'][0]['title'] : '',
					'user' => $this->config['authentication'][0]['username'],
					'pw' => $this->config['authentication'][0]['password']
				));
			}
		}
	}
	
	// grab browser info
	private function browser_info($agent=null) {
		
		// Declare known browsers to look for
		$known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko');

		// Clean up agent and build regex that matches phrases for known browsers
		// (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
		// version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
		$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
		$pattern = '#(?<browser>'.join('|', $known).')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

		// Find all phrases (or return empty array if none found)
		if (!preg_match_all($pattern, $agent, $matches)) return array();

		// Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
		// Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
		// in the UA).  That's usually the most correct.
		$i = count($matches['browser'])-1;
		return array($matches['browser'][$i] => $matches['version'][$i]);
		
		// Various browser tests you can do with the returned array ...
		// if ($array['firefox']) ... // true
		// if ($array['firefox'] > 3) ... // true
		// if ($array['firefox'] > 4) ... // false
		// if ($array['browser'] == 'firefox') ... // true
		// if ($array['version'] > 3.5) ... // true
		// if ($array['msie']) ... // false ('msie' key not defined)
		// if ($array['opera'] > 3) ... // false ('opera' key not defined)
		// if ($array['safari'] < 3) ... // false also ('safari' key not defined)
	}
	
	// return the currently set language as a string
	public function get_lang_str() {
		return 'en';
	}
	
	// return the current charset as a string
	public function get_charset_str() {
		return 'utf-8';
	}
	
	// load a new site object
	public function load_site() {
	
		// call in the site class
		require CMS_INCLUDES.'class.site.php';
		$this->site = new site();
		
		// set site theme
		$this->data['theme'] = $this->site->get('theme');
	}
	
	// load a new site object
	public function load_language() {
	
		// call in the site class
		require CMS_INCLUDES.'class.language.php';
		$this->language = new language();
	}
	
	// load a new menus object
	public function load_menus() {
	
		// call in the site class
		require CMS_INCLUDES.'class.menus.php';
		$this->menus = new menus();
	}
	
	// load a new page object
	public function load_page() {
	
		// call in the site class
		require CMS_INCLUDES.'class.page.php';
		$this->page = new page();
		
		// check for page"s theme
		if ($this->page->get('theme') != '') $this->data['theme'] = $this->page->get('theme');
	}
	
	// load a new module object
	public function load_module() {
	
		// call in the site class
		require CMS_INCLUDES.'class.module.php';
		$this->module = new module();
	}
	
	// load a new module object
	public function load_theme() {
	
		// call in the site class
		require CMS_INCLUDES.'class.theme.php';
		$this->theme = new theme($this->data['theme'], $this->data['folder']);
	}
	
	// check for redirection or 404
	public function redirect($url = '') {
	
		// grab codeserenity
		global $cs;
		
		if (empty($url)) {
		
			// check for redirects in the database
			$str = !empty($cs->paths['path']) ? $cs->paths['path'] : '/';
			$data = $cs->dbarray('redirects', array('where' => 'original = "'.$str.'"'));
			
			// if nothing just 404
			if (count($data) === 0) {
				require CMS_ERRORS.'404.php';
				die();
			}
			
			// if not redirect :)
			$cs->redirect($data[0]['new']);
		}
		else $cs->redirect($url);
	}
	
	private function start_timer() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$this->start_time = $time;
	}
	
	private function stop_timer() {
		$time = microtime();
		$time = explode(" ", $time);
		$time = $time[1] + $time[0];
		$this->load_time = round(($time - $this->start_time), 6);
	}
	
	// start template engine, assign data and output
	public function render() {
	
		// grab codeserenity
		global $cs;
	
		// Initialize the template engine
		$this->init_template();

		// re-asign data from the object in case it was altered by the module
		$this->assign_data();
		
		// stop timer
		$this->stop_timer();
		
		// assign load value
		$cs->template_assign('cms_load_time', $this->load_time);

		// output the lot
		$cs->template_display($this->data['template_file'].'.tpl');
	}
	// Assign data to the template engine
	// This need more work so it is more flexible and is able to handle multiple modules
	// Will also need updating when the module class is being rewritten so it is cleaner and
	// can be used for multiple modules.
	private function assign_data() {
	
		// grab codeserenity
		global $cs;

		// assign all objects data to the template engine
		$cs->template_assign('cs', $cs);
		$cs->template_assign('cms', $this->data);
		$cs->template_assign('cssbin', $this->cssbin);
		$cs->template_assign('jsbin', $this->jsbin);
		$cs->template_assign('site', $this->site->getdata());
		$cs->template_assign('page', $this->page->getdata());
		$cs->template_assign('page_url', $this->page->url);
		$cs->template_assign('page_path', $this->page->path);
		$cs->template_assign('theme', $this->theme->getdata());
		$cs->template_assign('lang', $this->language->getstrings());
		$cs->template_assign('crumbs', $this->crumbs);
		
		// assign menu data if needed
		if (isset($this->menus)) $cs->template_assign('menus', $this->menus->getdata());
		
		// assign module data if needed
		if (isset($this->module)) {
			$cs->template_assign('module', $this->module->getdata());
			$cs->template_assign('module_name', $this->module->name);
			$cs->template_assign('module_options', $this->module->options);
			$cs->template_assign('module_filters', $this->module->filters);
			$cs->template_assign('module_items_headers', $this->module->items_headers);
			$cs->template_assign('module_items', $this->module->items);
			$cs->template_assign('module_item', $this->module->item);
			$cs->template_assign('module_path', $this->module->path);
			$cs->template_assign('module_vars', $this->module->vars);
			$cs->template_assign('module_tpl', $this->module->tpl_file);
		}
		
		// assign $_POST, $_GET $_REQUEST and $_SESSION variables
		$cs->template_assign('_session', $_SESSION);
		$cs->template_assign('_request', $_REQUEST);
		$cs->template_assign('_post', $_POST);
		$cs->template_assign('_get', $_GET);
	}
	
	// start template engine and set variables
	public function init_template() {
	
		// grab codeserenity
		global $cs;
		
		// set the template directory up
		$cs->template_setup(array(
			'template_dir' => CMS_THEMES.$this->data['theme'].'/'.$this->data['folder'].'/templates',
			'config_dir' => CMS_THEMES.$this->data['theme'].'/'.$this->data['folder'].'/templates/config',
			'compile_dir' => CMS_VAR.$this->site->get('domain').'/compile/'.$this->data['folder'],
			'cache_dir' => CMS_VAR.$this->site->get('domain').'/cache/'.$this->data['folder']
		));
	}
	
	// handle CMS error reporting/logging
	public function error($key, $extra) {
		die($this->language->error($key).' :: '.$extra);
	}
	
	// check that all the minimum requirements for the CMS to run are met
	// for the minute it just return true as requirements will be known when CMS is completed
	public function installed() {
		return true;
	}
	
	// set a new data to be stored
	public function get($var) {
		return $this->data[$var];
	}
	
	// add a CSS file to the JS bin array
	public function addjs($file, $shared = false) {
		$path = $shared ? '/themes/shared/js/' : '/themes/'.$this->theme.'/'.$this->folder.'/js/';
		if (!in_array($path.$file.'.js',$this->jsbin)) $this->jsbin[] = $path.$file.'.js';
	}
	
	// add a CSS file to the CCS bin array
	public function addcss($file, $shared = false) {
		$path = $shared ? '/themes/shared/css/' : '/themes/'.$this->theme.'/'.$this->folder.'/css/';
		if (!in_array($path.$file.'.css',$this->cssbin)) $this->cssbin[] = $path.$file.'.css';
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