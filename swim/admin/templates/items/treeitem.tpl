{assign var="iv" value=$item->getCurrentVersion($variant)}
{assign var="class" value=$iv->getClass()}
{assign var="sequence" value=$iv->getMainSequence()}
{assign var="name" value=$iv->getField('name')}
<item id="{$iv->getId()}" class="{$class->getId()}" name="{$name->toString()}">
{if $sequence}
	{foreach from=$sequence->getItems() item="subitem"}
		{include file="items/treeitem.tpl" item=$subitem}
	{/foreach}
{/if}
</item>
