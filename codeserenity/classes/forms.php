<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity FORMS Class
	
	Roles:
		Generate form based on the XML definition file
		Load client side validator (via CSS class markup and jquery plugin)
		Server side validation on form submissions
*/

class forms 
{	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::forms::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	// generate a html form
	public function form($form, $settings = array())
	{	
		// build file path
		$file = FORMS.$form.'.xml';
		
		// make sure the file exists
		if (!file_exists($file)) $this->cs->error('forms::form - '.$file.' does not exist');
		
		// default settings
		$defaults = array(
			'method' => 'post',
			'action' => '',
			'captcha' => false
		);
		
		// sort options out
		$options = $this->cs->extend($defaults, $settings);
		
		// open our XML file
		$xml = simplexml_load_file($file);
		
		// grab the table name if any (otherwise use filename)
		$formname = isset($xml['name']) ? (string) $xml['name'] : $form;
		
		// source holding string
		$str = '';
		
		// any html to inject before
		if (isset($settings['before']) && !empty($settings['before'])) $str .= $settings['before']."\n";
		
		// start form element
		$str .= "\n".'<form action="'.$options['action'].'" method="'.$options['method'].'" name="'.$formname.'">'."\n<fieldset>\n";
		
		// any legend?
		if (isset($xml['legend'])) $str .= '<legend>'.$xml['legend']."</legend>\n";
		
		// loop through the fields
		foreach ($xml->field as $field)
		{
			// attributes array
			$attr = array();
			
			// any type set?
			$attr['type'] = isset($field['type']) ? (string) $field['type'] : 'text';
			
			// name
			$attr['name'] = isset($field['name']) ? (string) $field['name'] : $this->cs->error('html::form - name attribute missing in field tag. Check '.$file);
			
			// id (if none generate one based on the name and formname
			$attr['id'] = isset($field['id']) ? (string) $field['id'] : $formname.'_'.$attr['name'];
			
			// custom title attribute?
			if (isset($field['title'])) $attr['title'] = (string) $field['title'];
			
			// custom label?
			if (isset($field['label']))
			{
				$label = (string) $field['label'];
				if (!isset($attr['title'])) $attr['title'] = $label;
			}
			else $label = ucwords(str_replace("_"," ", $attr['name']));
			
			// special treatment for button, submit and image
			if ('submit' == $attr['type'] || 'button' == $attr['type'] || 'image' == $attr['type']) 
			{
				$attr['value'] = $label;
				unset($label);
			}
			
			// if it is an image
			if ('image' == $attr['type'])
			{
				if (isset($field['alt']))
				{
					$attr['alt'] = (string) $field['alt'];
					if (!isset($attr['title'])) $attr['title'] = $attr['alt'];
				}
				else $attr['alt'] = $attr['name'];
			}		
			
			// lookup types and sort data out
			switch ($attr['type'])
			{
				case 'textarea':
					$tag = $attr['type'];
					$attr['close'] = true;
					unset($attr['type']); 
						break;
				case 'text': 
					$tag = 'input'; 
						break;
				case 'file': 
					$tag = 'input'; 
						break;
				case 'image': 
					$tag = 'input';
					$attr['src'] = isset($field['src']) ? $field['src'] : '';
						break;
				case 'submit': 
					$tag = 'input'; 
						break;
				case 'button': 
					$tag = 'input'; 
						break;
				case 'select':
					$tag = $attr['type'];
					unset($attr['type']);
						break;
			}
			
			// add framework class name (ease styling of form elements and we know what we can look for in javascript)
			if (isset($attr['type'])) $attr['class'] = 'cs_form_'.$attr['type'];
			else $attr['class'] = 'cs_form_'.$tag;
			
			// custom class names?
			if (isset($field['class'])) $attr['class'] .= (string) $field['class'];
			
			// required field?
			if (isset($field['required']) && 'true' == (string) $field['required'])
			{
				// add the class name in
				$attr['class'] .= ' required';
				
				// make sure csValidate will be loaded  for client side validation
				$this->cs->jsadd('csValidate');
			}
			
			// format restriction?
			if (isset($field['format']) && !empty($field['format']))
			{
				// add the class name in
				$attr['class'] .= ' '.$field['format'];
				
				// make sure csValidate will be loaded  for client side validation
				$this->cs->jsadd('csValidate');
			}
			
			// build element
			$str .= '	<div>';
			if (!empty($label)) $str .= '<label for="'.$attr['id'].'">'.$label.'</label>';
			$str .= $this->cs->html($tag, $attr)."</div>\n";
		}
			
		return $str."</fieldset>\n</form>\n";
	}
	
	// validate data submitted (make sure required field there, format respected etc)
	public function validate($posts, $fields)
	{
		foreach($fields as $field)
		{
			if (isset($field['required']) && 'true' == $field['required'])
			{
				if (!isset($_POST[$field['name']]) || empty($_POST[$field['name']])) $this->errors[$field['name']] = 'This field is required';
			}
		}
		
		// all good
		return true;
	}
	
	// extract data needed from $_POST and insert auto-insert tag if needed
	public function extract($fields)
	{
		// array holder
		$array = array();
		
		// loop through the fields given
		foreach($fields as $field) if (isset($_POST[$field['name']])) $array[$field['name']] = $_POST[$field['name']];
		
		// return the array
		return $array;
	}
	
}

?>