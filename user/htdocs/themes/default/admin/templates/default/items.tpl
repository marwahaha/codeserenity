<table class="list" id="mod_{$module_name}">
	<thead>
		<tr>
			{foreach $module_items_headers as $v}<th class="{$v.name}">{$v.label}</th>{/foreach}
			{if $module_items[0].actions}<th class="actions">{$lang.actions}</th>{/if}
			{if $module_items[0].status}<th class="status">{$lang.status}</th>{/if}
		</tr>
	</thead>
	<tbody>
		{section name=i loop=$module_items}
			<tr{if $smarty.section.i.index % 2 == 0} class="even"{/if} id="{$module_items[i].id}">
				{foreach $module_items[i].fields as $field}
					{if $field.visible}<td class="field-{$field.name}">{$field.value}</td>{/if}
				{/foreach}
				{if $module_items[i].actions}
				<td class="actions">
				{if $module_items[i].has_children}
					<a href="{$module_path}?pid={$module_items[i].id}" class="action-children"><span>{$lang.children}</span></a>
				{/if}
				{foreach $module_items[i].actions as $k => $v}
					<a href="{$v.url}" class="action-{$v.name}"><span>{$v.label}</span></a>
				{/foreach}
				</td>
				{/if}
				{if $module_items[i].status}
				<td class="status">
					<a href="{$module_items[i].status.url}" class="switch"><span class="{$module_items[i].status.state}">&nbsp;<!--{$lang[{$module_items[i].status.state}]}--></span></a>
					<!--<a href="{$module_items[i].status.url}" class="action-{$module_items[i].status.name}"><span>{$module_items[i].status.label}</span></a>-->
				</td>
				{/if}
			</tr>
		{/section}
	</tbody>
</table>