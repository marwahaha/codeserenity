<?php

class template {

	public $tpl;
	
	// constructor
	public function __construct($parent = null)
	{		
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::template::__construct - the passed argument is not an object like expected');
		else return true;
		
		// start the msarty object
		include EXTERNALS.'smarty/Smarty.class.php';
		$this->tpl = new smarty();
	}
	
	// setup the template paths and settings
	public function setup($settings) {

		// set the correct template directories
		$this->tpl->setTemplateDir($settings['template_dir']);
		$this->tpl->setConfigDir($settings['config_dir']);
		$this->tpl->setCompileDir($settings['compile_dir']);
		$this->tpl->setCacheDir($settings['cache_dir']);
	
	}
	
	// assign a variable in the template engine
	public function display($template, $cache_id, $compile_id, $parent) {
		$this->tpl->display($template, $cache_id, $compile_id, $parent);
	}
	
	// assign a variable in the template engine
	public function assign($var, $value, $nocache, $scope = SMARTY_LOCAL_SCOPE) {
		return $this->tpl->assign($var, $value, $nocache, $scope);
	}
	
	// append to an assign variable (or create if it doesn't exists)
	public function append($var, $value, $merge, $nocache, $scope = SMARTY_LOCAL_SCOPE) {
		return $this->tpl->append($var, $value, $nocache, $scope);
	}

}

?>