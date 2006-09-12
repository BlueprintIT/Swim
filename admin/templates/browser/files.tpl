{secure documents="read"}
{include file="includes/singletabbedheader.tpl" title="File Browser"}
{literal}<script>
function selectUrl()
{
	var table = document.getElementById("files");
	var node = table.firstChild;
	while (node)
	{
		if ((node.nodeType == 1) && (node.className == "selected"))
		{
			window.parent.setUrl(node.getAttribute("path"));
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
		if (btn.getAttribute("disabled"))
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
<div id="tabpanel">
  <table>
    <tr>
{if $request.query.type=='link'}
      <td class="spacer"></td>
      <td class="tab unselected"><div class="tableft"><div class="tabright"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/items.tpl"}">Items</a></div></div></td>
{/if}
      <td class="spacer"></td>
      <td class="tab unselected"><div class="tableft"><div class="tabright"><a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/attachments.tpl"}">Item Attachments</a></div></div></td>
      <td class="spacer"></td>
      <td class="tab selected" selected="true"><div class="tableft"><div class="tabright">Files</div></div></td>
      <td class="remainder"></td>
    </tr>
  </table>
</div>

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
		{if isset($request.query.message)}
			<p style="text-align: center">{$request.query.message}</p>
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
					{getfiles var="files"}
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
