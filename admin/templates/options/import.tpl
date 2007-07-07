{include file='includes/frameheader.tpl' title="Import Data"}
{stylesheet href="`$smarty.config.YUISOURCE`/treeview/assets/tree.css"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{stylesheet href="$SITECONTENT/sitetree.css"}
{script href="`$smarty.config.YUISOURCE`/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="`$smarty.config.YUISOURCE`/event/event`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dom/dom`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/connection/connection`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/treeview/treeview`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/sitetree`$smarty.config.YUI`.js"}
{assign var="variant" value="default"}
<script>
{literal}
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
	{html_form tag_name="mainform" tag_enctype="multipart/form-data" method="import" targetvariant=$variant}
		<input type="hidden" id="parentitem" name="parentitem" value="">
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Import</a>
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
