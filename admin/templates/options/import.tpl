{include file='includes/frameheader.tpl' title="Import Data"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{stylesheet href="$SITECONTENT/sitetree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/connection/connection`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs.js"}
{script href="$SHARED/scripts/treeview.js"}
{script href="$SHARED/scripts/dom.js"}
{script href="$SHARED/scripts/sitetree.js"}
<script>
{literal}
function submitForm(form)
{
	document.forms[form].submit();
}

function onTreeItemClick(id)
{
  var input = document.getElementById("parentitem");
  input.value = id;
  SiteTree.selectItem(id);
}
{/literal}
var SiteTree = new BlueprintIT.widget.SiteTree('{encode method='admin' path='items/tree.xml'}', 'categorytree');
SiteTree.draggable = false;
</script>
<div id="mainpane">
	{html_form tag_name="mainform" tag_enctype="multipart/form-data" method="import" targetvariant=$session.variant}
		<input type="hidden" id="parentitem" name="parentitem" value="">
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:submitForm('mainform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Import</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Import Data</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div style="float: left; width: 230px;">
				<p style="padding-bottom: 1em">Select root item:</p>
				<div id="categorytree"></div>
			</div>
			<div style="margin-left: 230px">
				<table>
					<tr>
						<td style="padding-bottom: 1em"><label for="file">Import from local file:</label></td>
						<td style="padding-bottom: 1em"><input type="file" name="file" id="file"></td>
					</tr>
					<tr>
						<td style="padding-bottom: 1em"><label for="local">Import from uploaded file:</label>
						<td style="padding-bottom: 1em"><input type="text" name="local" id="local"> (server path)</td>
					</tr>
				</table>
			</div>
		</div>
	{/html_form}
</div>
{include file='includes/framefooter.tpl'}
