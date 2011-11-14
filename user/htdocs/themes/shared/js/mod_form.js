// bof closure
;(function($) {

	// some variables
	var
		images_extension = ['.jpg','.jpeg','.png','.gif','.tif','.bmp'];
	
	// plugin
	$.cms_mod_form = function() {	
		
		// special form stuff
		$('form.cs_form').each(function() {
			
			// cache form element
			var $form = $(this);
			
			// check for file uploaded links
			if ($('span.cs_form_file_link', $form).is(':visible')) {
				
				// loop through the links
				$('span.cs_form_file_link', $form).each(function() {
				
					// some variables
					var 
						href = $(this).children().attr('href'),
						dot = href.lastIndexOf(".");
						
					if(dot != -1) var extension = href.substr(dot,href.length);
					if (extension && $.inArray(extension, images_extension) != -1) {
						$(this).after($('<img>').attr('src', href).addClass('cs_form_file_image'));
					};
				});
			};
			
			// check for slugh/name
			if ($('#name').is(':visible') && ($('#title').is(':visible') || $('#label').is(':visible'))) {
				
				// sort out trigger to use
				$trigger = $('#label').is(':visible') ? $('#label') : $('#title');
				
				// if not super user hide the name field
				/*
				if (!$form.hasClass('su')) {
					$('#name').parents().parents().eq(0).css({
						position:'absolute',
						left:'-10000px',
						top:'-10000px'
					});
				};
				*/
				
				// bind trigger element
				$trigger.bind('keypress keyup keydown blur', function() {
				
					// update name field
					$('#name').val(function() {
						
						// return cleaned string
						return $trigger.val()
							.replace(/[ \t\r\n\v\f]/gi, "-")
								.replace(/[^\w^\-]|\^|_/gi, "")
									.toLowerCase();
					});
				});
			};
		});	
	};

// eof closure
})(jQuery);
$(function() {
	$.cms_mod_form();
});