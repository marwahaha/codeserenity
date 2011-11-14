<link href="{$theme.css}init.css" type="text/css" rel="stylesheet" />
	{if !$cms.browser.msie}<link href="{$theme.css}css3.css" type="text/css" rel="stylesheet" />{/if}
	{if $cms.browser.msie}<link href="{$theme.shared.css}tools-ie.css" type="text/css" rel="stylesheet" />{/if}
	{foreach $cssbin as $file}<link href="{$file}" type="text/css" rel="stylesheet" />{/foreach}