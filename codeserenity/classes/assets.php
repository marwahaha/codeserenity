<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity ASSETS Class
	
	Roles:
*/

class assets 
{	
	// properties
	public $type;
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::email::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	
}

?>