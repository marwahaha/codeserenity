<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity IMAGE Class
	
	Roles:
*/

class image 
{	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::image::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	// resizing function
	public function resize($path, $img, $w, $h)
	{
		$info = pathinfo($path.$img);
		$ext = strtolower($info['extension']);
		
		if (!is_numeric($w) || $w < 1 || $w > 2000 || !is_numeric($h) || $h < 1 || $h > 2000) exit;
		list($in_w, $in_h) = getimagesize($path.$img);

		if ($ext == "jpg" || $ext == "jpeg") $in = imagecreatefromjpeg($path.$img);
		if ($ext == "gif") $in = imagecreatefromgif($path.$img);
		if ($ext == "png") $in = imagecreatefrompng($path.$img);
		
		$out = imagecreatetruecolor($w, $h);
		imagecopyresampled($out, $in, 0, 0, 0, 0, $w, $h, $in_w, $in_h);
		
		if ($ext == "jpg" || $ext == "jpeg") imagejpeg($out, $path.$img, 80);
		if ($ext == "gif") imagegif($out,$path.$img);
		if ($ext == "png") imagepng($out,$path.$img);

		imagedestroy($in);
		imagedestroy($out);
	}
	
	
}

?>