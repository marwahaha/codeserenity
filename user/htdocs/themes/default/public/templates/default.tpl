<!DOCTYPE html>
<html lang="{$cms.lang}">
<head>
	<meta http-equiv="Content-Type" content="{$cms.content_type}; charset={$cms.charset}" />
	<link href="{$theme.css}init.css" type="text/css" rel="stylesheet" />
	<script src="{$theme.shared.js}jquery.js"></script>
	<script src="{$theme.js}common.js"></script>
	<title>{$page.title}</title>
</head>
<body>
<a href="#main" id="skip">skip to main content</a>
<div id="wrap">
	<div id="page">
		<div id="head">
			<div id="site-search">
				<form action="/search">
					<fieldset>
						<legend>Search this site</legend>
						<div class="field">
							<label for="search-keyword">Keyword</label>
							<input type="text" name="search-keyword" id="search-keyword" /><input type="submit" name="submit" value=" Search " id="search-submit" />
						</div>
					</fieldset>
				</form>
			</div>
			<div id="menu">
				<ul role="menu">
				{section name=i loop=$menus.main}
					<li{if $menus.main[i].__active} class="active"{/if} role="menuitem"><a href="/{$menus.main[i].name}"><span>{$menus.main[i].label}</span></a>
				{/section}
				</ul>
			</div>
		</div>
		<div id="content">
			<div id="main">
				<div id="crumbs">
					<strong>You are here:</strong>
					<ul>
					{foreach $crumbs as $v}
						<li><a href="{$v.name}" class="depth-{$v.depth}">{$v.label}</a></li>
					{/foreach}
					</ul>
				</div>
				<h1>{$page.title}</h1>
				<div class="wysiwyg">{$page.content}</div>
				{if $module_items}<div class="items">{include file="default/items.tpl"}</div>{/if}
				{if $module_tpl}{include file="$module_tpl.tpl"}{/if}
			</div>
			<div id="side">
				<div id="twitter-feed"></div>
			</div>
		</div>
	</div>
</div>
</body>
</html>