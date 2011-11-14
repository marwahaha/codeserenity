<!-- Inline for now, integrate better when it's all working -->
<script>
// dom ready
$(function() {

	// initialize Jcrop
	var api = $.Jcrop('#original_image', {
		onChange: crop_update,
		onSelect: crop_update
	});
	
	// reset button handler
	$('#crop-reset').click(function() {
		api.release();
	});
	
	// save button handler, request the image to be cropped
	$('#crop-save').click(function() {
		var src = $('#original_image').attr('src');
		src = src.substring(1, src.length);
		$.ajax({
			url: '/admin/ajax/crop_image?file='+src+'&x='+$('#x1').val()+'&y='+$('#y1').val()+'&w='+$('#w').val()+'&h='+$('#h').val(),
			dataType: 'json',
			complete: function(json, status) {
				if (status == 'success') {
					alert('File cropped');
					window.location = '/admin/libraries/images/';
				} else alert('Cropping failed!');
			}
		});
	});
	
	// save as button handler, ask for the cropped image details, create the crop images and save it as a new record
	$('#crop-save_as').click(function() {
		var name = prompt('Type a new title for the cropped image'),
		src = $('#original_image').attr('src');
		src = src.substring(1, src.length);
		if (!name) {
			alert('You must enter a new title in order to continue');
			return false;
		};
		$.ajax({
			url: '/admin/ajax/crop_image?file='+src+'&x='+$('#x1').val()+'&y='+$('#y1').val()+'&w='+$('#w').val()+'&h='+$('#h').val()+'&title='+name,
			dataType: 'json',
			complete: function(json, status) {
				if (status == 'success') {
					alert('File cropped and saved as '+name);
					window.location = '/admin/libraries/images/';
				} else alert('Cropping failed!');
			}
		});
	});
});

// function
function crop_update(c) {
	
	$('#x1').val(c.x);
	$('#y1').val(c.y);/*
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);*/
	$('#w').val(c.w);
	$('#h').val(c.h);
	$('#crop-preview').css({
		width:c.w+'px',
		height:c.h+'px',
		backgroundPosition:'-'+c.x+'px -'+c.y+'px'
	});

};
</script>
<form action="" class="cs_form">
<fieldset id="crop-ui">
	<legend>Crop image</legend>
	<div id="crop-toolbar">
		<input type="button" name="reset" id="crop-reset" value="Reset" class="cs_form_button" />
		{* <input type="button" name="preview" id="crop-preview" value="Preview" class="cs_form_button" /> *}
		<input type="button" name="save" id="crop-save" value="Save" class="cs_form_button" />
		<input type="button" name="save_as" id="crop-save_as" value="Save as" class="cs_form_button" />
	</div>
	<div id="crop-image">
		<div id="crop-original"><img src="/uploads/images/{$module_item.file}" alt="{$module_item.name}" id="original_image" /></div>
		<div id="crop-preview" style="background-image:url('/uploads/images/{$module_item.file}');"></div>
	</div>
	<div id="crop-stats">
		X: <input type="text" name="x1" id="x1" size="3" readonly="readonly" />
		Y: <input type="text" name="y1" id="y1" size="3" readonly="readonly" />
		Width: <input type="text" name="w" id="w" size="3" readonly="readonly" />
		Height: <input type="text" name="h" id="h" size="3" readonly="readonly" />
	</div>
</fieldset>
</form>