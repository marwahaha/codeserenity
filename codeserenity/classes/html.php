<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity HTML Class
	
	Roles:
		Generate HTML tags and form elements
		Can build a all HTML page from few functions call
*/

class html 
{
	// private properties
	private $cs; // code serenity
	private $source; // hold html source code
	private $tabindex; // track tabindex for form elements
	private $opended = array(); // keep track of opened tagged (so can auto-close it needed)
	
	// supported events handler (jQuery)
	private $events = array('blur','focus','load','resize','scroll','unload','beforeunload','click', 
	'dblclick','mousedown','mouseup','mousemove', 'mouseover','mouseout', 'mouseenter','mouseleave', 
	'change','select','submit','keydown','keypress','keyup','error'); 

	/* supported/valid attributes (expend so check attributes per type
	private $all_attributes = array('accept','alt','checked','disabled','maxlength','name','readonly','size',
	'src','type','value','accesskey','class','dir','lang','xml:lang','title','tabindex','style','cols', 'id',
	'rows','for','type','charset','href','hreflang','media','rel','rev','content','http-equiv','scheme','selected',
	'action','method','enctype','accept-charset','xmlns','profile','defer','border','cellpadding','cellspacing','frame'
	'rules','summary','width','abbr','axis','colspan','rowspan','scope','multiple','headers','coords'); */
	
	// single tags (no "body" content)
	private $singles = array('meta','link','br','hr','img','input');
	
	// input tag types
	private $inputs = array('button','checkbox','file','hidden','image','password','radio','reset','submit','text');
	
	// tag -> valid attributes/events
	private $attributes = array
	(		
		//html tag
		'html' => array('dir','lang','xml:lang','xmlns'),
		
		// head tag
		'head' => array('profile','dir','lang','xml:lang'),
		
		// meta tag
		'meta' => array('name','content','http-equiv','scheme','dir','lang','xml:lang'),
		
		// link tag
		'link' => array('type','charset','href','hreflang','media','rel','rev','class','dir','lang','xml:lang','title','style','id'),
		
		// script tag
		'script' => array('charset','defer','src','type'),
		
		// style tag
		'style' => array('type','media','dir','lang','title','xm:lang'),
		
		// body tag
		'body' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// div tag
		'div' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// p tag
		'p' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// br tag
		'br' => array('title','style','id','class'),
		
		// hr tag
		'hr' => array('title','style','id','class'),
		
		// span tag
		'span' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// table tag
		'table' => array('border','cellpadding','cellspacing','frame','rules','summary','width','class','dir','lang','xml:lang','title','style','id'),
		
		// thead tag
		'thead' => array('class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// tr tag
		'tr' => array('class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// th tag
		'th' => array('abbr','axis','colspan','rowspan','scope','class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// tbody tag
		'tbody' => array('class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// tr tag
		'tr' => array('class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// td tag
		'td' => array('headers','abbr','axis','colspan','rowspan','scope','class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// tfoot tag
		'tfoot' => array('class','dir','lang','xml:lang','title','style','id','align','char','charoff','valign'),
		
		// tt tag
		'tt' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// i tag
		'i' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// b tag
		'b' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// big tag
		'big' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// small tag
		'small' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// em tag
		'em' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// strong tag
		'strong' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// dfn tag
		'dfn' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// code tag
		'code' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// samp tag
		'samp' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// kbd tag
		'kbd' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// var tag
		'var' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// cite tag (badly supported by browsers - force non-use)
		// 'cite' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// sup tag
		'sup' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// sub tag
		'sub' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// form tag
		'form' => array('action','method','enctype','accept-charset','accept','class','dir','lang','xml:lang','title','style','id','name'),
		
		// fieldset tag
		'fieldset' => array('class','dir','lang','xml:lang','title','style','id'),
		
		// legend tag
		'legend' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// label tag
		'label' =>  array('class','dir','lang','xml:lang','title','style','id','for'),
		
		// select tag
		'select' =>  array('disabled','multiple','name','size','class','dir','lang','xml:lang','title','style','id'),
		
		// option tag
		'options' =>  array('disabled','label','selected','value','name','size','class','dir','lang','xml:lang','title','style','id'),
		
		// optgroup tag
		'optgroup' =>  array('disabled','label','class','dir','lang','xml:lang','title','style','id'),
		
		// textarea tag
		'textarea' =>  array('cols','rows','disabled','readonly','name','class','dir','lang','xml:lang','title','style','id'),
		
		// input tag
		'input' => array('accept','alt','checked','disabled','maxlength','name','readonly','size','src','type','value','accesskey','class','dir','lang','xml:lang','title','tabindex','style','id'),
		
		// ul tag
		'ul' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// ol tag
		'ol' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// li tag
		'li' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// dl tag
		'dl' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// dt tag
		'dt' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// dd tag
		'dd' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// img tag
		'img' =>  array('alt','src','height','ismap','longdesc','usemap','width','class','dir','lang','xml:lang','title','style','id'),
		
		// h tags
		'h' =>  array('class','dir','lang','xml:lang','title','style','id'),
		
		// a tag
		'a' =>  array('accesskey','tabindex','coords','charset','href','hreflang','name','rel','rev','shape','class','dir','lang','xml:lang','title','style','id'),
		
		/*
			add more as needed
		*/
	);
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::html::__construct - the passed argument is not an object like expected');
		else return true;
	}
	
	// take and array and return its content as a table ($rows: array of arrays | $headers: array or MetaColumns object)
	public function table($rows, $headers = null)
	{
		// start grid table string
		$grid = $this->add('table')."\n";
		
		// any headers?
		if (!is_null($headers))
		{		
			// open row
			$grid .= $this->add('tr');
			
			// convert headers into a string (change later to it uses the label attribute)
			foreach ($headers as $header)
			{
				// if object get name property
				if (is_object($header)) $header = $header->name;				
				$grid .= $this->add('th').$header.$this->add('/th');
			}
			
			// close row
			$grid .= $this->add('/tr')."\n";		
		}
		
		// loop through rows
		foreach ($rows as $row)
		{
			// open row
			$grid .= $this->add('tr');
			
			// fill with columns
			foreach ($row as $field) $grid .= $this->add('td').$field.$this->add('/td');
			
			// close row			
			$grid .= $this->add('/tr')."\n";
		}
		
		// close table
		$grid .= $this->add('/table')."\n";
		
		// return the table
		return $grid;
	}
	
	// generate a html form
	public function form($form, $settings = array())
	{	
		// build file path
		$file = FORMS.$form.'.xml';
		
		// make sure the file exists
		if (!file_exists($file)) $this->cs->error('html::form - '.$file.' does not exist');
		
		// default settings
		$defaults = array(
			'method' => 'post',
			'action' => $this->cs->paths['full'],
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
		$str .= "\n".$this->add('form', array(
				'method' => $options['method'],
				'action' => $options['action'],
				'name' => $formname
			)
		)."\n".$this->add('fieldset')."\n";
		
		// any legend?
		if (isset($xml['legend'])) $str .= $this->add('legend').$xml['legend'].$this->add('/legend')."\n";
		
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
			$str .= '	'.$this->add('div');
			if (!empty($label)) $str .= $this->add('label',array('for' => $attr['id'])).$label.$this->add('/label');
			$str .= $this->add($tag, $attr).$this->add('/div')."\n";
		}
			
		return $str.$this->add('/fieldset')."\n".$this->add('/form')."\n";
	}
	
	// add a html string to $this->source
	public function add($tag, $settings = array())
	{
		// doctype?
		if ('doctype' == $tag) return $this->_doctype();
		
		// html?
		if ('html' == $tag) return $this->_html_tag($settings);
		
		// is settings is not an array error out
		if (!is_array($settings)) $this->cs->error('html::add - parameter 2 of invalid type - array expected');
		else
		{
			// open the tag
			$str = '<'.$tag.'';
			
			// autoclose? special attribute or script tag with src attribute
			if (isset($settings['close']) || ('script' == $tag && isset($settings['src']) && !empty($settings['src'])))
			{
				// set close to true
				$close = true;
				
				// remove special attribute
				unset($settings['close']);
			}
			else $close = false;
			
			// apply default if needed
			$settings = $this->_mandatory($tag, $settings);
			
			// cache settings size
			$count = count($settings);
			
			// good old $i
			$i = 0;
			
			// loop through settings
			foreach ($settings as $var => $value)
			{
				// hijack content settings (it wont validate!)
				if ('content' == $var)
				{
					// cache the content
					$content = $value;
					
					// remove that settings
					unset($settings['content']);
					
					// skip the rest
					continue;
				}
				
				// make sure only valid attributes are used
				if (in_array($var, $this->attributes[$tag]))
				{
					// add space on first run?
					if ($i == 0) $str .= ' ';
					
					// add attribute
					$str .= $var.'="'.$value.'"';
					
					// add space?
					if ($i < $count) $str .= ' ';
					
					// increase $i
					$i++;
				}				
				// if not error out
				else $this->cs->error('html::add - '.$var.' is an invalid attribute for '.$tag.' html element');
			}			
			
			// close the tag
			if (('xhtml' == FORMAT || 'html5' == FORMAT) && in_array($tag, $this->singles)) $str .= '/>';
			else
			{
				// close tag
				$str .= '>';
				
				// any content
				if (isset($content)) $str .= $content;
				
				// if auto close and not single tag close opened tag
				if ($close && !in_array($tag, $this->singles)) $str .= '</'.$tag.'>';
			}
		}
		return $str;
	}
	
	// return a html string
	public function get($tag, $settings)
	{
		return true;
	}
	
	/*
		private methods
	*/
	
	// create a captcha block
	private function _captcha_wrap($settings = array())
	{
		
	}
	
	// build html element
	private function _html_tag($settings)
	{
		$lang = isset($settings['lang']) ? $settings['lang'] : 'en';
		$lang = isset($settings['xml:lang']) ? $settings['xml:lang'] : 'en';
		
		if ('xhtml' == FORMAT) $str = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang.'">';
		elseif ('html5' == FORMAT) $str = '<html lang="'.$lang.'">';
		else $str = '<html lang="'.$lang.'">';
		
		// save state
		$this->opened['html'] = true;
		
		// return string
		return $str."\n";
	}
	
	// insert mandatory attributes if needed
	private function _mandatory($tag, $settings)
	{		
		switch($tag)
		{
			case 'link':
				if (!isset($settings['media'])) $settings['media'] = 'screen';
				if (!isset($settings['rel'])) $settings['rel'] = 'stylesheet';
				if (!isset($settings['type'])) $settings['type'] = 'text/css';
					break;
			case 'script':
				if (!isset($settings['type']) && 'html' == FORMAT) $settings['type'] = 'text/javascript';
					break;
		}
		
		// return updated arguments
		return $settings;		
	}
	
	// return valid doctype
	private function _doctype()
	{
		if ('html' == FORMAT) return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n";
		elseif ('html5' == FORMAT) return '<!DOCTYPE html>'."\n";
		else return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
	}
}

?>