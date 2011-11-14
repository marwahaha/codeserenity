<?php

/* Code Serenity v.2.0
 * Copyright (c) 2009 Gilles Cochez
 * All rights reserved
 */
 
/*
	Code Serenity EMAIL Class
	
	Roles:
		Handle email sending
*/

class email 
{	
	// private properties
	private $mail;
	
	// constructor
	public function __construct($parent = null)
	{
		// assign core if present and valid
		if (!is_null($parent) && is_object($parent)) $this->cs = $parent;
		elseif (!is_null($parent) && !is_object($parent)) die('codeserenity::email::__construct - the passed argument is not an object like expected');
		else return true;
		
		// grab phpmailer
		include EXTERNALS.'phpmailer/class.phpmailer.php';
		$this->mail = new PHPMailer();
	}
	
	// one function to generate and send email
	/*
		$settings = array(
			'from' = 'string',
			'to' = 'string' or array,
			'cc' = 'string' or array,
			'bcc' = 'string' or array,
			'replyto' = 'string' or array,
			'subject' = 'string',
			'bodytext' = 'string',
			'bodyhtml' = 'string',
			'charset' = 'string'
		);
	
	*/
	public function quicksend($settings)
	{
		// extract options
		extract($settings);
		
		// message string holder
		$message = '';
		
		// start building headers
		$headers = 'From: '.$from."\n";
		
		// sort out to, cc, bcc and reply-to headers if any
		$arr = array('to','cc','bcc','replyto');
		foreach($arr as $it) {
			if (isset($settings[$it])) {
				if (is_array($it)) $it = implode(",", $it);
				$label = ('repltyto' == $it) ? 'Reply-To' : ucwords($it);
				$headers .= $label.': '.$it."\n";
			}
		}
		
		// add mime version
		$headers .= 'MIME-Version: 1.0'."\n";
		
		// multi-part?
		if (isset($bodytext) || isset($bodyhtml))
		{
			// boundary limiter for multi-part email
			$boundary = "--------".md5(time());
			
			// set multi-part header
			$headers .= 'Content-Type: multipart/alternative; boundary="'.$boundary.'"'."\n";
			
			// add boundary
			$message .= '--'.$boundary."\n";
			
			// set html encoding
			$message .= 'Content-Type: text/plain; charset=UTF-8'."\n";
			$message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
			
			// add html body
			$message .= isset($bodytext) ? $bodytext : strip_tags($bodyhtml);
			
			// add boundary
			$message .= "\n\n".'--'.$boundary."\n";
			
			// set html encoding
			$message .= 'Content-Type: text/html; charset=UTF-8'."\n";
			$message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
			
			// add html body (if none convert text one - improve convertion later ;))
			$message .= isset($bodyhtml) ? $bodyhtml : nl2br($bodytext);
		}
		
		return @mail($to, $subject, $message, $headers);
	}
}

?>