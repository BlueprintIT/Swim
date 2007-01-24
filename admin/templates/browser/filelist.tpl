{secure documents="read" login="true"}
{include file="includes/singletabbedheader.tpl" title=$title}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{literal}<script>
function selectItem()
{
	var table = document.getElementById("files");
	var node = table.firstChild;
	while (node)
	{
		if ((node.nodeType == 1) && (node.className == "selected"))
		{
			window.parent.setItem(node.getAttribute("path"), node.getAttribute("path"), node.getAttribute("name"));
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
		/*var request = new Request();
		request.setMethod("admin");
		request.setPath("browser/preview.tpl");
		request.setQueryVar("path", row.getAttribute("path"));
		document.getElementById("preview").src = request.encode();*/
		document.getElementById("preview").src = row.getAttribute("path");
		var btn = document.getElementById("okbtn");
		btn.style.display = 'block';
		btn = document.getElementById("okbtn-disabled");
		btn.style.display = 'none';
	}
}
</script>
<style>
table#filelist {
	border: 0 none;
	border-collapse: collapse;
	cursor: default;
}

table#filelist, table#filelist tr, table#filelist td {
	border: 0 none;
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

table#filelist td.name {
	width: 20%;
	border-right: 1px solid #808080;
}

table#filelist td.description {
	border-right: 1px solid #808080;
	border-left: 1px solid #808080;
}

table#filelist td.type {
	width: 20%;
	border-right: 1px solid #808080;
	border-left: 1px solid #808080;
}

table#filelist td.size {
	width: 10%;
	border-right: 1px solid #808080;
	border-left: 1px solid #808080;
}

table#filelist td.options {
	width: 5%;
	border-left: 1px solid #808080;
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
      <td class="tab unselected" onmouseover="this.className='tab hover'" onmouseout="this.className='tab unselected'">
        <div class="tableft"><div class="tabright">
          <a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/items.tpl"}">Items</a>
        </div></div>
      </td>
{/if}
      <td class="spacer"></td>
{if $scope=="version"}
      <td class="tab selected" selected="true"><div class="tableft"><div class="tabright">Item Attachments</div></div></td>
{else}
      <td class="tab unselected" onmouseover="this.className='tab hover'" onmouseout="this.className='tab unselected'">
        <div class="tableft"><div class="tabright">
          <a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/attachments.tpl"}">Item Attachments</a>
        </div></div>
      </td>
{/if}
      <td class="spacer"></td>
{if $scope=="global"}
      <td class="tab selected" selected="true"><div class="tableft"><div class="tabright">Files</div></div></td>
{else}
      <td class="tab unselected" onmouseover="this.className='tab hover'" onmouseout="this.className='tab unselected'">
        <div class="tableft"><div class="tabright">
          <a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/files.tpl"}">Files</a>
        </div></div>
      </td>
{/if}
      <td class="remainder"></td>
    </tr>
  </table>
</div>

<div id="mainpane" class="pane">
	<div class="header">
		<table class="toolbar">
			<tr>
				<td>
					<div id="okbtn-disabled" class="toolbarbutton disabledtoolbarbutton">
						<img src="{$CONTENT}/icons/check-grey.gif"/> Save
					</div>
					<div id="okbtn" class="toolbarbutton" style="display: none">
						<a href="javascript:selectItem()"><img src="{$CONTENT}/icons/check-grey.gif"/> Save</a>
					</div>
				</td>
				<td>
					<div class="toolbarbutton">
						<a href="javascript:cancel()"><img src="{$CONTENT}/icons/delete-page-blue.gif"/> Cancel</a>
					</div>
				</td>
				{secure documents="write"}
{if $scope=="version"}
					{html_form tag_name="uploadform" tag_enctype="multipart/form-data" itemversion=$itemversion->getId() method="uploadfile" nestcurrent="true"}
					<td class="separator"></td>
					<td style="padding-left: 20px;">
						<input type="file" name="file">
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('uploadform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Upload</a>
						</div>
					</td>
					{/html_form}
{elseif $scope=="global"}
					{html_form tag_name="uploadform" tag_enctype="multipart/form-data" method="uploadfile" nestcurrent="true"}
					<td class="separator"></td>
					<td style="padding-left: 20px;">
						<input type="file" name="file">
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('uploadform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Upload</a>
						</div>
					</td>
					{/html_form}
{/if}
				{/secure}
			</tr>
		</table>
		<h2>{$title}</h2>
	</div>
	<div class="body">
		{if isset($request.query.message)}
			<p style="text-align: center">{$request.query.message}</p>
		{/if}
		<div style="height: 50%; overflow: auto">
			<table id="filelist" style="width: 100%">
				<thead>
					<tr id="header">
						<td class="name">Filename</td>
						<td class="description">Description</td>
						<td class="type">File Type</td>
						<td class="size">File Size</td>
						<td class="options"></td>
					</tr>
				</thead>
				<tbody id="files">
					{foreach from=$files item="file"}
						<tr path="{$file.path}" name="{$file.name}" description="{$file.description}" onclick="selectRow(this)">
							<td class="name">{$file.name}</td>
							<td class="description">{$file.description}</td>
							<td class="type">{$file.type}</td>
							<td class="size">{$file.readablesize}</td>
							<td class="options">
{if $scope=="version"}
								<a onclick="return confirm('Are you sure you want to delete this file?')" href="{encode method="deletefile" path=$file.name itemversion=$itemversion->getId() nestcurrent="true"}"><img src="{$CONTENT}/icons/delete-page-red.gif" title="Delete"></a>
{elseif $scope=="global"}
								<a onclick="return confirm('Are you sure you want to delete this file?')" href="{encode method="deletefile" path=$file.name nestcurrent="true"}"><img src="{$CONTENT}/icons/delete-page-red.gif" title="Delete"></a>
{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<iframe id="preview" name="preview" style="height: 40%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
	</div>
</div>

{include file="includes/singletabbedfooter.tpl"}
{/secure}
