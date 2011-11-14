<div id="filter_toggler">
	<a href="#hide">{$lang.filter_off}</a>
	<a href="#show">{$lang.filter_on}</a>
</div>
<form action="" class="cs_form">
	<fieldset class="cs_fieldset">
		<div class="fields cms_clear">
			{foreach $module_filters as $filter}
			<div class="cs_field">
				<label for="form_{$filter.name}">{$filter.label}</label>
				{$filter.input}
			</div>
			{/foreach}
			<div class="cs_button">
				<input type="submit" name="filter" value="{$lang.filter}" class="cs_form_submit" />
			</div>
		</div>
	</fieldset>
</form>