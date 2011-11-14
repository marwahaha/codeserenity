<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity FILESYSTEM Class
	
	Roles:
		Handle file system manipulation
*/

class filesystem 
{	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::email::__construct - the passed argument is not an object like expected');
		else return true;
	}

	public function upload($it, $dir, $name)
	{
		global $_FILES;
		$tmp = $_FILES[$it]['tmp_name'];

		// Check that the file is uploaded
		if (is_uploaded_file($tmp))
		{
			// Check if the folder need to be created
			if (!is_dir($dir))
			{
				// Try to make the missing directory
				if (!mkdir($dir, 0777)) $this->cs->error('Folder "'.$dir.'" does not exist nor could it be created!');
			}
			if (@move_uploaded_file($tmp, $dir.$name)) return true;
			else
			{
				unlink($tmp);
				return false;
			}
		}
		else
		{
			// Error occur. Delete temporary file and print error message.
			unlink($tmp);
			$this->cs->error('File attack detected!');
		}
	}

	// return an array with files and directories name
	public function flist($dir, $filter = false)
	{
		//using the opendir function
		$o = @opendir($dir) or $this->cs->error("Unable to open $dir");
		
		// blank array
		$data = array();

		//running the while loop
		while ($file = readdir($o)) 
		{
		   // filter dots out
		   if ($file != "." && $file != "..")
		   {
				// if no filter add straight away
				if (!$filter) $data[] = $file;
				else
				{
					// make sensible check based on the filter use
					if ('file' == $filter && is_file($dir.$file)) $data[] = $file;
					elseif ('dir' == $filter && is_dir($dir.$file)) $data[] = $file;
				}
		   }
		}

		//closing the directory
		closedir($o);
		
		// return data
		return $data;

	}
	
	
}

?>