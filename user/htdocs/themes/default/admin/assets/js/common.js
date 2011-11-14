// bof dom ready
$(function() {

	// Set tableDND plugin on tables with position column
	$('#page table.list').each(function(j) {
		
		// some selecting/caching
		var $this = $(this),
			module = $this.attr('id').replace('mod_',''),
			$thpos = $this.find('th.position'),
			$tdpos = $this.find('td.field-position'),
			old;
		
		// make sure we have positioning
		if ($thpos.size()) {
			
			// mark header row as no droppable/draggable
			$thpos.parent().addClass('nodrop').addClass('nodrag');
			
			// empty the position <td>s
			$tdpos.empty();
			
			// call and set the tableDnD plugin
			$this.tableDnD({
			
				// class of the position <td>s
				dragHandle:'field-position',
				
				// on drag callback
				onDragStart: function(table, td) {
					old = $.tableDnD.serialize();
					$(td).parent().addClass('moving');
					$('#cms-message').children(0).fadeOut(function() {
						$(this).remove();
					});
				},
				
				// on row drop callback
				onDrop: function(table, row) {
				
					// make sure we need to do some changes
					var seria = $.tableDnD.serialize();
					if (seria == old) return false;
					else old = seria;
					
					// start building response string
					var qry = '';
					
					// loop through <td>s
					$(table).find('td.field-position').each(function(i) {
					
						// grab the id from the parent (remove .off from the row in the process)
						var id = $(this).parent().removeClass('moving').attr('id');
						
						// add to the string
						qry += 'id[]='+id+'&position[]='+i+'&';
						
					}).removeClass('even').filter(':even').addClass('even');
					
					// do an ajax query to update the database
					$.ajax({
						url: '/admin/ajax/action/update_position/'+module+'?'+qry,
						dataType: 'json',
						success: function(json, txt) {
							message(json.message, json.status);
						},
						error: function() {
							message('AJAX query failed!', 'error');
						}
					});
				}
			});
		};
	});
	
	// set/display a CMS message
	function message(str, type) {
		$('#cms-message').html(
			$('<div>').hide().addClass(type).append(
				$('<p>').text(str),
				$('<span>').addClass('close').click(function() {
					$(this).parent().fadeOut(function() {
						$(this).remove();
					})
				})
			)
		).children(0).fadeIn();
	};
	
	// filters toggler
	$('#filter_toggler').children().click(function(ev) {		
		$(this).parent().next().toggle().end().children().toggle();
		ev.preventDefault();
	}).end().each(function() {
		var $this = $(this), vis = $this.parent().next('.cs_form').is(':visible');
		if (vis) $this.children().eq(0).hide();
		else  $this.children().eq(1).hide();
	}).show();
	
	// autofocus on first form text element
	$('#page form.cs_form .cs_form_text:eq(0)').focus();
	
	// action-delete confirmation
	$('#page td.actions a.action-delete').click(function(ev) {
		
		// make a prettier box later
		if (!confirm($(this).text()+' '+$(this).parent().parent().children().eq(0).text()+'?')) ev.preventDefault();
	});
	
	// grab navigation items
	var $lis = $('#menu ul li');
	
	// #menu li:hover fix for IE
	if (!$.support.opacity) $lis.bind('mouseover mouseout', function() { $(this).toggleClass('hover'); });
	
	// menu
	$lis.each(function() {
		
		// select/cache
		var
			$this = $(this),
			$label = $this.find('strong').eq(0),
			$toggler = $this.find('span').eq(0).css('opacity', 0.5);
			
		// bind the toggler if present
		if ($toggler.size()) {
			
			// bind this and hide sub-items
			if (!$this.is('.open')) $this.next().hide();
			
			// bind element
			$toggler.click(function(ev) {
				ev.preventDefault();
				$this.next().toggle();
				$(this).toggleClass('open');
			}).hover(function() {
				$toggler.css('opacity', 1);
			}, function() {
				$toggler.css('opacity', 0.5);
			})
		};
	});
	
	// hide back to top button
	$('#back_to_top').hide();
	
	// if the window get scrolled then display it
	$(window).scroll(function() {
		$('#back_to_top').show();
	});
	
	// function to detect the presence of the scrollbar
	function has_scrollbar() {
		var vHeight = 0;
		if (document.all) {
		  if (document.documentElement) vHeight = document.documentElement.clientHeight;
		  else vHeight = document.body.clientHeight;
		} else vHeight = window.innerHeight;

		return (document.body.offsetHeight > vHeight) | false;
	}
});