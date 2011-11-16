<!DOCTYPE html>
<html lang="{$cms.lang}">
<head>
	<meta http-equiv="Content-Type" content="{$cms.content_type}; charset={$cms.charset}" />
	<link href="{$theme.css}init.css" type="text/css" rel="stylesheet" />
	<script src="{$theme.shared.js}jquery.js"></script>
	<script src="{$theme.shared.js}easing.js"></script>
	<script src="{$theme.shared.js}cooltabs.js"></script>
	<script src="{$theme.js}common.js"></script>
	<title>Gilles Cochez - {$page.title}</title>
</head>
<body>
<div id="head">
	<h1>Gilles Cochez</h1>
	<h2>Developing with passion since 1997</h2>
	<ul id="social">
		<li>
			<a href="http://blog.gillescochez.info" title="View my development blog">
				Development blog &gt;
			</a>
		</li>
		<li>
			<a href="http://uk.linkedin.com/in/gcochez" title="View my linkedIn profil">
				LinkedIn profil &gt;
			</a>
		</li>
		<li>
			<a href="https://github.com/gillescochez/" title="View my github profil">
				Github profil &gt;
			</a>
		</li>
	</ul>
</div>
<div id="content">
	{section name=i loop=$page.result}
		<div class="cooltabs-content">
			<h3>{$page.result[i].title}</h3>
			<div class="wysiwyg">{$page.result[i].content}</div>
		</div>
	{/section}
</div>
<div id="foot">
	&copy; 2011 Gilles Cochez
</div>
</body>
</html>