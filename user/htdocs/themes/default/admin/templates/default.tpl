<!DOCTYPE html>
<html lang="{$cms.lang}">
<head>
	<meta http-equiv="Content-Type" content="{$cms.content_type}; charset={$cms.charset}" />
	{include file="inc/css.tpl"}
	{include file="inc/js.tpl"}
	<title>CMS Admin: {strip_tags($page.title)}</title>
</head>
<body id="top" class="{$module_name}">
<div id="wrap">
	<div id="head" class="cms_clear">
		{if $_session.admin}<div id="log-status">{$lang.logged_in_as} {$_session.username}. <a href="/admin/login/out">{$lang.log_out}</a></div>{/if}
		<div id="cms-message">{if $cms.message}<div class="{$cms.message.type}">{$cms.message.string}</div>{/if}</div>
	</div>
	<div id="page">
		<div id="container" class="cms_clear">
			<div id="incontainer" class="column">
				<h1>{if $crumbs[0]}<ul id="crumbs" class="cms_clear">{foreach from=$crumbs item=v name=bread}<li class="depth-{$v.depth}">{if !$smarty.foreach.bread.last}<a href="{$v.url}">{$v.label}</a>{else}{$v.label}{/if}{if !$smarty.foreach.bread.last} &gt;{/if}</li>{/foreach}</ul>{else}{$page.title}{/if}</h1>
				{if $page.back_button}{include file="default/back_button.tpl"}{/if}
				{if $page.content}<div>{$page.content}</div>{/if}
				{if $page.wysiwyg}{$page.wysiwyg.file}{$page.wysiwyg.init}{/if}
				{if $module_tpl}{include file="$module_tpl.tpl"}{/if}
				{if $module_options}<div class="options cms_clear">{include file="default/options.tpl"}</div>{/if}
				{if $module_filters}<div class="filters">{include file="default/filters.tpl"}</div>{/if}
				{if $module_items}<div class="items">{include file="default/items.tpl"}</div>{/if}
				{if $page.back_button}{include file="default/back_button.tpl"}{/if}
			</div>
			<div id="menu" class="column">{include file="default/menu.tpl"}</div>
		</div>
	</div>
	<div id="foot">
		<a href="#top" id="back_to_top"><span>{$lang.back_to_top}</span></a>
		<p>Copyright &copy; 2010 Gilles Cochez</p>
		<p>
			<a href="http://validator.w3.org/check?uri={$cs->paths.url}"><img src="{$theme.img}valid_html5.png" alt="Valid HTML5" /></a>
			<a href="http://jigsaw.w3.org/css-validator/validator?uri={$cs->paths.url}"><img src="{$theme.img}valid_css.png" alt="Valid CSS" /></a>
		</p>
		{if $cms_load_time}<p>{$cms_load_time}</p>{/if}
	</div>
</div>
</body>
</html>