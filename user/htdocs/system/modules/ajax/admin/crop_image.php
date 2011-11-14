<?php

function crop_image($file, $x, $y, $w, $h, $rename = false) {
	
	// get file extension
	$ext = get_file_extension($file);
	
	// create original image
	switch($ext) {
		case 'jpg'; $image = imagecreatefromjpeg($file); break;
		case 'jpeg'; $image = imagecreatefromjpeg($file); break;
		case 'gif'; $image = imagecreatefromgif($file); break;
		case 'png'; $image = imagecreatefrompng($file); break; 
	}
	
	// create cropped image holer
	$crop = imagecreatetruecolor($w, $h);
	
	// crop the image
	imagecopy($crop, $image, 0, 0, $x, $y, $w, $h);
	
	if ($rename) $save_file = str_replace('.'.$ext, '_'.$x.'-'.$y.'-'.$w.'-'.$h.'.'.$ext, $file);
	else $save_file = $file;
	
	// create the image
	switch($ext) {
		case 'jpg'; imagejpeg($crop, $save_file, 100); break; 
		case 'jpeg'; imagejpeg($crop, $save_file, 100); break;
		case 'gif'; imagegif($crop, $save_file, 100); break;
		case 'png'; imagepng($crop, $save_file, 100); break; 
	}
	
	// clean memory
	imagedestroy($image);
	imagedestroy($crop);
	
	return $save_file;
}

function get_file_extension($str) {
	$i = strrpos($str,'.');
	if (!$i) return '';
	$ext = substr($str, $i+1, (strlen($str) - $i));
	return $ext;
}

// do we need to rename the file
if (isset($_GET['title'])) $rename = true;
else $rename = false;

// crop file and grab returned filename for the cropped image
$filename = crop_image($_GET['file'], $_GET['x'], $_GET['y'], $_GET['w'], $_GET['h'], $rename);

// remove the path from the filename
$filename = basename($filename);

// if we renamed the file we need to insert a new row in the database
if ($rename) {

	// grab the title name for he record
	$title = trim($_GET['title']);
	
	// build name/slug based on the title name
	$name = preg_replace('/[^a-z0-9]/', '', $title);
	
	// insert data
	$cs->dbinsert('images', array(
		'name' => $name,
		'label' => $title,
		'file' => $filename
	));
}

// if up to here we no error we assume all went fine (need to add more check later on)
?>{"status":"success"}