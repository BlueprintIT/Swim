{secure documents="read"}
{include file="includes/singletabbedheader.tpl" title="Item Browser"}
{stylesheet href="$CONTENT/yahoo/css/folders/tree.css"}
{script href="$CONTENT/yahoo/YAHOO.js"}
{script href="$CONTENT/scripts/BlueprintIT.js"}
{script method="admin" path="scripts/request.js"}
{script href="$CONTENT/yahoo/event.js"}
{script href="$CONTENT/yahoo/dom.js"}
{script href="$CONTENT/yahoo/dragdrop.js"}
{script href="$CONTENT/yahoo/connection.js"}
{script href="$CONTENT/yahoo/treeview.js"}
{script href="$CONTENT/scripts/treeview.js"}
{script href="$CONTENT/scripts/dom.js"}
{script href="$CONTENT/scripts/sitetree.js"}
<script>{literal}
function selectUrl(url)
{
	var request = new Request();
	request.setMethod("view");
	request.setPath(SiteTree.selected);
	window.parent.opener.SetUrl(request.encode());
	window.parent.close();
}

function cancel()
{
	window.parent.close();
}

function onTreeItemClick(id)
{
	var request = new Request();
	request.setMethod('view');
	request.setPath(id);
	document.getElementById('main').src = request.encode();
	SiteTree.selectItem(id);
	var btn = document.getElementById("okbtn");
	if (btn.hasAttribute("disabled"))
		btn.removeAttribute("disabled");
}
{/literal}
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml' root=1}', 'categorytree');
</script>
<table id="tabpanel">
  <tr>
    <td class="spacer"></td>
    <td class="tab selected" selected="true">Items</td>
    <td class="spacer"></td>
    <td class="tab unselected"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/attachments.tpl"}">Item Attachments</a></td>
    <td class="spacer"></td>
    <td class="tab unselected"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/files.tpl"}">Files</a></td>
    <td class="remainder"></td>
  </tr>
</table>

<div id="leftpane" class="pane">
	<div class="header">
		<button id="okbtn" onclick="selectUrl()" type="button" disabled="true">OK</button>
		<button onclick="cancel()" type="button">Cancel</button>
		<h2>Structure</h2>
	</div>
	<div class="body">
		<div id="categorytree">
			<p>Loading Site...</p>
		</div>
	</div>
</div>

<div id="mainpane" class="pane">
	<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
</div>

{include file="includes/singletabbedfooter.tpl"}
{/secure}
