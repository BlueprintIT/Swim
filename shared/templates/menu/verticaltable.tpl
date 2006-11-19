{if count($items)>0}
	<table class="vertmenu menupopup">
		{foreach name="itemlist" from=$items item="subitem"}
			<tr>
				{if $subitem === $item}
					<td class="selected menuitem">
				{else}
					<td class="menuitem">
				{/if}
					<a href="{$subitem->url}"><span>{$subitem->name}</span></a>
					{if $depth>0}
						{include file="shared:menu/verticaltable.tpl" items=$subitem->mainsequence depth=$depth-1}
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
