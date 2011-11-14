<ul>
	{foreach name=main from=$menus.main item=v}
		<li class="item item-{$v.name}{if $v.__active} active{/if}{if $v.__open} open{/if}">
			<a href="/admin/{$v.name}"><strong>{$v.label}</strong>{if $v.__has_sub}<span></span>{/if}</a>
		</li>
		{if $v.__has_sub}
		<li>
			<ul>
				{foreach name=sub_main from=$menus.main_sub[$v.id] item=var}
					<li class="item item-{$var.name}{if $var.__active} active{/if}">
						<a href="/admin/{$v.name}/{$var.name}"><strong>{$var.label}</strong></a>
					</li>
				{/foreach}
			</ul>
		</li>
		{/if}
	{/foreach}
</ul>