{if empty($depth)}
	{assign var="depth" value="0"}
{/if}
{if count($items)>0}
	<ul class="vertmenu menu{if $popup}popup{/if}">
		{foreach name="itemlist" from=$items item="subitem"}
			<li class="{if $subitem === $item}selected {/if}menuitem">
				<a {if $subitem === $item}class="selected" {/if}href="{$subitem->url}"><span>{$subitem->name}</span></a>
				{if $depth>0}
					{include file="shared:menu/verticallist.tpl" popup="true" items=$subitem->mainsequence depth=$depth-1}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}
