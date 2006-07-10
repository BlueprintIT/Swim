{assign var="iv" value=$item->getCurrentVersion($session.variant)}
{if $iv==null}
	{assign var="iv" value=$item->getNewestVersion($session.variant)}
{/if}
{assign var="class" value=$iv->getClass()}
{assign var="sequence" value=$iv->getMainSequence()}
{assign var="name" value=$iv->getField('name')}
<item id="{$item->getId()}" class="{$class->getId()}" name="{$name->toString()|escape}"{if $sequence} contains="{foreach name="classlist" from=$sequence->getVisibleClasses() item="visible"}{$visible->getId()}{if !$smarty.foreach.classlist.last},{/if}{/foreach}">
	{foreach from=$sequence->getItems() item="subitem"}
		{include file="items/treeitem.tpl" item=$subitem}
	{/foreach}
{else}>
{/if}
</item>
