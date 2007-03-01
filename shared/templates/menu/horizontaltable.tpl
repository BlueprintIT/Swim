{if count($items)>0}
	<table class="horizmenu menu{if $popup}popup{/if}">
		<tr>
			{foreach name="itemlist" from=$items item="subitem"}
				<td class="{if $subitem === $item}selected {/if}menuitem">
					<a {if $subitem === $item}class="selected" {/if}href="{$subitem->url}"><span>{$subitem->name}</span></a>
					{if $depth>0}
						{include file="shared:menu/verticaltable.tpl" popup="true" items=$subitem->mainsequence depth=$depth-1}
					{/if}
				</td>
			{/foreach}
		</tr>
	</table>
{/if}
