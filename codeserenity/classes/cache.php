<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity Cache Class
	
	Roles:
		Check cache exists
		Load cache in multiple format (array|string|JSON)
		Cache loaded cache to serve it faster if requested more than once
*/

class cache
{
	// public properties
	public $files = array();
	
	// private properties
	private $cs;
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::cache::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	/*
		public methods
	*/
	
	// write to the cache folder
	public function write($file, $content)
	{
		$fp = fopen(CACHE.$file, 'w');
		fwrite($fp, $content);
		fclose($fp);
	}
	
	// Read from the cache
	public function read($file)
	{
		// return the cache file content
		return implode('', file(CACHE.$file));
	}
	
	// check if a cache file exists
	public function exists($file)
	{
		return file_exists(CACHE.$file);
	}
	
	// get the content of a cache file
	public function get_array($file)
	{
		// is it cache already? if so return data straight away
		if (isset($this->files[$file])) return $this->files[$file];
		
		// make sure file exists
		if($this->exists($file))
		{
			// include the file
			include CACHE.$file;
			
			// make sure our array is there
			if (isset($cache_data) && is_array($cache_data))
			{
				// save the data in $this->files
				$this->files[$file] = $cache_data;
				
				// remove cache_data from memory
				unset($cache_data);
				
				// return the array
				return $this->files[$file];
			}
			else $this->cs->error('cache::get_array - the "cache_array" is missing or is not an array');
		}
		else $this->cs->error('cache::get_array - '.CACHE.$file.' does not exists');
	}
}
?>