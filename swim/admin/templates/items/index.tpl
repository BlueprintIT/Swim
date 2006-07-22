{secure documents="read" login="true"}
{include file='includes/adminheader.tpl' title="Content management"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$SHARED/treeview/sitetree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/animation/animation`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/connection/connection`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview.js"}
{script href="$SHARED/scripts/dom.js"}
{script href="$SHARED/scripts/sitetree.js"}
<script>
var section = '{$request.query.section}';
{literal}
function onTreeItemClick(id)
{
	if (!SiteTree.dragging) {
		var request = new Request();
		request.setMethod('admin');
		if (id == 'uncat') {
			request.setPath('items/uncategorised.tpl');
			request.setQueryVar('section', section);
		}
		else {
			request.setPath('items/details.tpl');
			request.setQueryVar('item', id);
		}
		document.getElementById('main').src = request.encode();
	}
}
{/literal}
{if isset($request.query.root)}
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml' root=$request.query.root}', 'categorytree');
{apiget var="root" type="item" id=$request.query.root}
{else}
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml' section=$request.query.section}', 'categorytree');
{apiget var="section" type="section" id=$request.query.section}
{assign var="root" value=$section->getRootItem()}
{/if}
//SiteTree.setExpandAnim(YAHOO.widget.TVAnim.FADE_IN);
//SiteTree.setCollapseAnim(YAHOO.widget.TVAnim.FADE_OUT);
SiteTree.draggable = true;
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<h2>Structure</h2>
	</div>
	<div class="body">
		<div id="categorytree">
			<p>Loading Site...</p>
		</div>
	</div>
</div>

<div id="mainpane" class="pane">
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src="{encode method='admin' path='items/details.tpl' item=$root->getId()}"></iframe>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
