<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity JSBIN Class  
		jQuery support only.... for now...
	
	Roles:
		Generate javascript scripting
		Handle the loading and initialisation of plugin
*/

class css 
{	
	// private properties
	private $files = array(); // store .js to load (full path as custom path can be passed)
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::css::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	public function getfiles() {
		return $this->files;
	}
	
	// minimized a javascript string using JSMIN
	public function min($str)
	{
		// class the JSMIN class if not already available
		if (!class_exists('JSMin')) require EXTERNALS.'jsmin.php';
		
		// return minimized string
		return JSMin::minify($str);
	}
	
	// add a file to load
	public function add($file, $path = null)
	{
		// if string force array
		if (is_string($file))
		{
			$file = !is_null($path) ? $path.$file.'.css' : JSBIN.$file.'.css';
			$this->_add($file);
		}
		elseif (is_array($file))
		{
			// custom path?
			if (!is_null($path))
			{
				if (is_array($path))
				{
					// make sure both array are of the same size
					if (count($file) != count($path)) $this->cs->error('css::add - the numbers of arguments keys does not match');
					else
					{
						for ($i = 0, $count = count($file); $i < $count; $i++) $this->_add($path[$i].$file[$i].'.css');
					}
				}
				elseif (is_string($path))
				{
					foreach ($file as $value) $this->_add($path.$file.'.css');
				}
				else $this->cs->error('css::add - invalid type for 2nd argument - array or string expected');
			}
			else
			{
				foreach ($file as $value) $this->_add(JSBIN.$file.'.css');
			}
		}
		else $this->cs->error('css::add - invalid type for 1st argument - array or string expected');
	}
	
	public function get()
	{
		// generate script tags
		$css = $this->load();
		
		// return the lot
		return $css;
	}
	
	// write the script tags to load the file to the browser
	public function load()
	{
		// holding string
		$str = '';
		
		// loop through the files
		foreach ($this->files as $value) $str .= $this->cs->html('link', array(
			'href' => $value
		))."\n";
		
		// return the string
		return $str;
	}
	
	// add to array files to load if needed
	private function _add($str) {
		if (!in_array($str, $this->files)) $this->files[] = $str;
	}
}


?>