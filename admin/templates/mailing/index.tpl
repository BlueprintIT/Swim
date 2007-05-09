{secure contacts="read" login="true"}
{include file='includes/adminheader.tpl' title="Mailing Options"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{stylesheet href="$CONTENT/styles/mailingtree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/sitetree`$smarty.config.YUI`.js"}
{apiget var="section" type="section" id=$request.query.section}
<script type="text/javascript">
function onTreeItemClick(id)
{ldelim}
	if (!SiteTree.dragging) {ldelim}
		var src = '';
		if (id == 'contacts') {ldelim}
			src = '{encode method="admin" path="mailing/contacts.tpl" section=$section->getId()}';
		{rdelim}
		else if ((id == 'mailings') || (id == 'drafts') || (id == 'archive') || (id == 'pending')) {ldelim}
			var node = SiteTree.getItems(id);
			node[0].toggle();
		{rdelim}
		else if (id.substr(0, 8) == 'mailing_') {ldelim}
			var request = new Request();
			request.setMethod('admin');
			request.setPath('mailing/maildetails.tpl');
			request.setQueryVar('section', '{$request.query.section}');
			request.setQueryVar('mailing', id.substr(8));
			src = request.encode();
		{rdelim}
		else {ldelim}
			var request = new Request();
			request.setMethod('admin');
			request.setPath('mailing/details.tpl');
			request.setQueryVar('item', id);
			src = request.encode();
		{rdelim}
		document.getElementById('main').src = src;
	{rdelim}
{rdelim}

var maildata = [
	{ldelim}
		id: "contacts",
		name: "Contacts",
		class: "_contactcategory",
		published: true
	{rdelim},
	{ldelim}
		id: "mailings",
		name: "Mailings",
		class: "category",
		published: true,
		contains: "mailing",
		subitems: [
{foreach from=$section->getMailings() item="mailing"}
			{ldelim}
				id: "mailing_{$mailing->getId()}",
				name: "{$mailing->getName()}",
				class: "mailing",
				published: true
			{rdelim},
{/foreach}
		]
	},
	{ldelim}
		id: "drafts",
		name: "Draft Mailings",
		class: "category",
		published: true,
		contains: "draftmail",
		subitems: [
{assign var="item" value=$section->getRootItem()}
{assign var="sequence" value=$item->getMainSequence()}
{foreach from=$sequence->getItems() item="item"}
		{assign var="variant" value=$item->getVariant('default')}
		{assign var="itemversion" value=$variant->getDraftVersion()}
		{if $itemversion && ($itemversion->getFieldValue('sent') != 'true')}
 			{ldelim}
					class: "draftmail",
					name: "{$itemversion->getFieldValue('name')}",
					id: "{$item->getId()}",
					published: false
			{rdelim},
		{/if}
{/foreach}
		]
	{rdelim},
	{ldelim}
		id: "pending",
		name: "Pending Mailings",
		class: "category",
		published: true,
		contains: "sentmail",
		subitems: [
{foreach from=$sequence->getItems() item="item"}
		{assign var="variant" value=$item->getVariant('default')}
		{assign var="itemversion" value=$variant->getDraftVersion()}
		{if $itemversion && ($itemversion->getFieldValue('sent') == 'true')}
 			{ldelim}
					class: "sentmail",
					name: "{$itemversion->getFieldValue('name')}",
					id: "{$item->getId()}",
					published: true
			{rdelim},
		{/if}
{/foreach}
		]
	{rdelim},
	{ldelim}
		id: "archive",
		name: "Past Mailings",
		class: "category",
		published: true,
		contains: "sentmail",
		subitems: [
{foreach from=$sequence->getItems() item="item"}
		{assign var="variant" value=$item->getVariant('default')}
		{assign var="itemversion" value=$variant->getCurrentVersion()}
		{if $itemversion}
 			{ldelim}
					class: "sentmail",
					name: "{$itemversion->getFieldValue('name')}",
					id: "{$item->getId()}",
					published: true
			{rdelim},
		{/if}
{/foreach}
		]
	{rdelim}
];

var SiteTree = new BlueprintIT.widget.SiteTree('{$request.query.section}', '', 'categorytree', maildata);
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<h2>Mailing Options</h2>
	</div>
	<div class="body">
		<div id="categorytree"></div>
	</div>
</div>

<div id="mainpane" class="pane">
{if $request.query.item}
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src="{encode method="admin" path="mailing/details.tpl" item=$request.query.item"}></iframe>
{else}
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
{/if}
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
