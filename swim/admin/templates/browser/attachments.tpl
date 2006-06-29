{secure documents="read"}
{include file="includes/singletabbedheader.tpl" title="Attachment Browser"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="itemvariant" value=$item->getVariant($request.query.variant)}
{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{literal}<script>
function selectUrl()
{
	var table = document.getElementById("files");
	var node = table.firstChild;
	while (node)
	{
		if ((node.nodeType == 1) && (node.className == "selected"))
		{
			window.parent.opener.SetUrl(node.getAttribute("path"));
			window.parent.close();
			break;
		}
		node = node.nextSibling;
	}
}

function cancel()
{
	window.parent.close();
}

function selectRow(row)
{
	if (row.className != 'selected')
	{
		var table = row.parentNode;
		var node = table.firstChild;
		while (node)
		{
			if (node.nodeType == 1)
			{
				node.className = '';
			}
			node = node.nextSibling;
		}
		row.className = 'selected';
		document.getElementById("preview").src = row.getAttribute("path");
		var btn = document.getElementById("okbtn");
		if (btn.hasAttribute("disabled"))
			btn.removeAttribute("disabled");
	}
}
</script>
<style>
table#filelist, table#filelist tr, table#filelist td {
	border: 0 none;
	border-collapse: collapse;
	cursor: default;
}

table#filelist td {
	padding: 2px;
}

tr.selected {
	background-color: Highlight;
}

tr.selected td {
	color: HighlightText;
}

tr#header {
	background-color: ThreeDFace;
}

td.name {
	width: 20%;
	border-right: 1px solid black;
}

td.description {
	border-right: 1px solid black;
	border-left: 1px solid black;
}

td.type {
	width: 20%;
	border-right: 1px solid black;
	border-left: 1px solid black;
}

td.size {
	width: 10%;
	border-left: 1px solid black;
}

td.name a {
}

td.name img {
	vertical-align: middle;
	border: 0;
}
</style>{/literal}
<table id="tabpanel">
  <tr>
{if $request.query.type=='link'}
    <td class="spacer"></td>
    <td class="tab unselected"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/items.tpl"}">Items</a></td>
{/if}
    <td class="spacer"></td>
    <td class="tab selected" selected="true">Item Attachments</td>
    <td class="spacer"></td>
    <td class="tab unselected"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/files.tpl"}">Files</a></td>
    <td class="remainder"></td>
  </tr>
</table>

<div id="mainpane" class="pane">
	<div class="header">
		<button id="okbtn" disabled="true" onclick="selectUrl()" type="button">OK</button>
		<button onclick="cancel()" type="button">Cancel</button>
		{secure documents="write"}
			{html_form tag_enctype="multipart/form-data" method="upload" nestcurrent="true"}
				<input type="file" name="file">
				<input type="submit" value="Upload">
			{/html_form}
		{/secure}
		<h2>Global Files</h2>
	</div>
	<div class="body">
		{if $request.query.message}
			<p>$request.query.message</p>
		{/if}
		<div style="height: 40%; overflow: auto">
			<table id="filelist" style="width: 100%">
				<thead>
					<tr id="header">
						<td class="name">Filename</td>
						<td class="description">Description</td>
						<td class="type">File Type</td>
						<td class="size">File Size</td>
					</tr>
				</thead>
				<tbody id="files">
					{getfiles var="files" itemversion=$itemversion->getId()}
					{foreach from=$files item="file"}
						<tr path="{$file.path}" onclick="selectRow(this)">
							<td class="name"><img alt="" src="{$CONTENT}/icons/{$file.extension}.gif"> {$file.name}</td>
							<td class="description">{$file.description}</td>
							<td class="type">{$file.type}</td>
							<td class="size">{$file.size}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<iframe id="preview" name="preview" style="height: 40%; width: 50%" scrolling="no" frameborder="1" src=""></iframe>
</div>

{include file="includes/singletabbedfooter.tpl"}
{/secure}
