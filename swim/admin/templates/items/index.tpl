{secure documents="read"}
{include file='includes/adminheader.tpl' title="User management"}
{stylesheet href="$CONTENT/yahoo/css/folders/tree.css"}
{script href="$CONTENT/yahoo/YAHOO.js"}
{script href="$CONTENT/scripts/BlueprintIT.js"}
{script href="$CONTENT/yahoo/event.js"}
{script href="$CONTENT/yahoo/dom.js"}
{script href="$CONTENT/yahoo/dragdrop.js"}
{script href="$CONTENT/yahoo/connection.js"}
{script href="$CONTENT/yahoo/treeview.js"}
{script href="$CONTENT/scripts/treeview.js"}
{script href="$CONTENT/scripts/dom.js"/>
{script href="$CONTENT/scripts/sitetree.js"}
<script>
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml' root=$request.query.root}', 'categorytree');
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
<iframe name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
