<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity DATETIME Class
	
	Roles:
		Handle all convertion of date, time etc
*/

class datetime 
{	
	public $months = array(
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::datetime::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	public function month_from_num($num) {
		if (substr($num, 0, 1) == '0') $num = substr($num, 1, 2);
		return $this->months[intval($num)-1];
	}
	
	
}

?>