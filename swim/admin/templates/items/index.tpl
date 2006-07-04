{secure documents="read" login="true"}
{include file='includes/adminheader.tpl' title="Content management"}
{stylesheet href="$CONTENT/yahoo/css/folders/tree.css"}
{script href="$CONTENT/yui/yahoo/yahoo-min.js"}
{script href="$CONTENT/scripts/BlueprintIT.js"}
{script method="admin" path="scripts/request.js"}
{script href="$CONTENT/yui/event/event-min.js"}
{script href="$CONTENT/yui/dom/dom-min.js"}
{script href="$CONTENT/yui/connection/connection-min.js"}
{script href="$CONTENT/yui/treeview/treeview-min.js"}
{script href="$CONTENT/scripts/treeview.js"}
{script href="$CONTENT/scripts/dom.js"}
{script href="$CONTENT/scripts/sitetree.js"}
<script>{literal}
function onTreeItemClick(id)
{
	var request = new Request();
	request.setMethod('admin');
	request.setPath('items/details.tpl');
	request.setQueryVar('item', id);
	document.getElementById('main').src = request.encode();
}
{/literal}
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml' root=$request.query.root section=$request.query.section}', 'categorytree');
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
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src="{encode method='admin' path='items/details.tpl' item=$request.query.root}"></iframe>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
