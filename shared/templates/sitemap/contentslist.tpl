<li>{if $item->allowsLink}<a href="{$item->url}">{/if}{$item->name}{if $item->allowsLink}</a>{/if}
	{if $depth!=0}
		{assign var="sequence" value=$item->mainsequence}
		{if count($sequence)>0}
			<ul>
				{foreach from=$sequence item="subitem"}
					{include file="shared:sitemap/contentslist.tpl" item=$subitem depth=$depth-1}
				{/foreach}
			</ul>
		{/if}
	{/if}
</li>
