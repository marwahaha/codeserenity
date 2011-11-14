<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity Store Class
	
	Roles:
		Code Serenity personal storage handler
		Can also handle different storage location (maybe use fior caching too?)
*/

class store
{
	// public properties
	public $files = array();
	
	// private properties
	private $dir = STORE;
	private $cs;
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::store::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	/*
		public methods
	*/
	
	// write a file to the store
	public function write($file, $content)
	{
		// build file path
		$file = $this->dir.$file.'.data';
		
		// open/create file
		$fp = fopen($file, "w");
		
		// all good?
		if ($fp)
		{
			// Write the data to the file
			fwrite($fp, $content);
			
			// Close the file
			fclose($fp);
			
			// end function
			return true;
		}
		else return false;
	}
	
	// append content to a file (check for duplicate first)
	public function append($file, $content = '')
	{		
		// build file path
		$file = $this->dir.$file.'.data';
		
		// file exists? if not create it
		if (!file_exists($file)) $fp = fopen($file, "w");
		else
		{
			// open file to append it
			$fp = fopen($file, "a+");
			
			// get current content
			$cur_content = fread($fp, filesize($file));
			
			// string alredy in?
			if (isset($cur_content) && !empty($cur_content) && strpos($cur_content, $content) !== false) return true;
		}
		
		// Open the file and erase the content if any
		if ($fp)
		{
			// Write the data to the file
			fwrite($fp, $content."\n");
			
			// Close the file
			fclose($fp);
			
			// end function
			return true;
		}
		else return false;
	}
	
	// copy a file to store
	public function copy($file)
	{		
		if (!file_exists($file)) $this->cs->error('store::copy - '.$file.' does not exists');
		else
		{
			$data = @file_get_contents($file);
			$fp = fopen($this->dir.basename($file).'.data', "w");
			fwrite($fp, $data);
			fclose($fp);
		}
		return true;
    } 
	
	// extract an array from the store
	public function getarray($file, $dir = '')
	{
		$data = array();
		
		// build full path
		if (empty($dir)) $file = $this->dir.$file.'.data';
		else  $file = $dir.$file.'.data';
		
		// eval the file data to produce the array
		if (file_exists($file)) eval(implode("\n", file($file)));
		
		// return array
		return $data;
	}
	
	// set the store directory
	public function set_directory($dir)
	{
		// make sure the directory exists and is writtable
		if (!is_dir($dir) || !is_writable($dir)) $this->cs->error('store::set_directory - '.$dir.' does not exists or is not writtable');
		else $this->dir = $dir;
	}
}
?>