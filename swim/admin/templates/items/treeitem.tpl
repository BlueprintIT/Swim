{assign var="iv" value=$item->getCurrentVersion($session.variant)}
{if $iv==null}
	{assign var="iv" value=$item->getNewestVersion($session.variant)}
{/if}
{assign var="class" value=$iv->getClass()}
{assign var="sequence" value=$iv->getMainSequence()}
{assign var="name" value=$iv->getField('name')}
<item id="{$item->getId()}" class="{$class->getId()}" name="{$name->toString()}">
{if $sequence}
	{foreach from=$sequence->getItems() item="subitem"}
		{include file="items/treeitem.tpl" item=$subitem}
	{/foreach}
{/if}
</item>
