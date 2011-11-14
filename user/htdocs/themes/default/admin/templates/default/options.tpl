<ul class="module_options cms_clear">
	{foreach $module_options as $v}
		<li id="option-{$v.name}"><a href="{$module_path}action/{$v.url}"><span></span>{$v.label}</a></li>
	{/foreach}
</ul>