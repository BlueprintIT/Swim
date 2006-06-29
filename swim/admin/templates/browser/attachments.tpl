{secure documents="read"}
{include file="includes/singletabbedheader.tpl" title="Attachment Browser"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="itemvariant" value=$item->getVariant($request.query.variant)}
{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
<script>{literal}
function setUrl(url)
{
	window.parent.opener.SetUrl(url);
	window.parent.close();
}
{/literal}</script>
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
{getfiles var="files" itemversion=$itemversion->getId()}
{foreach from=$files item="file"}
	<p>{$file.name}</p>
{/foreach}
</div>

{include file="includes/singletabbedfooter.tpl"}
{/secure}
