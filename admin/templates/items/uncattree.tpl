<item id="uncat" class="uncategorised" name="Uncategorised Items" {foreach name="contents" from=$section->getVisibleClasses() item="class"}{if $smarty.foreach.contents.first}contains="{else},{/if}{$class->getId()}{if $smarty.foreach.contents.last}"{/if}{/foreach}>
{php}
$items = array();
$_STORAGE = $GLOBALS['_STORAGE'];
$section = $this->get_template_vars('section');
$root = $section->getRootItem();
$results = $_STORAGE->query('SELECT Item.id FROM Item LEFT JOIN Sequence ON Item.id=Sequence.item WHERE ISNULL(Sequence.Item) AND section="'.$_STORAGE->escape($section->getId()).'" AND id!='.$root->getId().' AND (ISNULL(archived) OR archived<>1);');
while ($results->valid())
{
	$item = Item::getItem($results->fetchSingle());
	array_push($items, $item);
}
$this->assign('missing', $items);
{/php}
	{foreach from=$missing item="subitem"}
		{include file="items/treeitem.tpl" item=$subitem}
	{/foreach}
</item>
