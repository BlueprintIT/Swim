{secure documents="read"}
{include file="includes/singletabbedheader.tpl" title="Item Browser"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{stylesheet href="$SITECONTENT/sitetree.css"}
{script href="$SHARED/json/json`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/connection/connection`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/sitetree`$smarty.config.YUI`.js"}
<script>
{if $smarty.config.inlinetree}
var sitedata = {php}
global $_PREFS;
$request = $this->get_template_vars('REQUEST');
include_once $_PREFS->getPref('storage.methods').'/tree.php';
displayAllSections(Session::getCurrentVariant());{/php};
{else}
var sitedata = null;
{/if}
{literal}
function selectItem()
{
	var request = new Request();
	request.setMethod("view");
	request.setPath(SiteTree.selected);
	window.parent.setItem(request.encode(), SiteTree.selected, SiteTree.items[SiteTree.selected][0].data.label);
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
	btn.style.display = null;
	btn = document.getElementById("okbtn-disabled");
	btn.style.display = 'none';
}
{/literal}
var SiteTree = new BlueprintIT.widget.SiteTree('all', '{encode method='tree'}', 'categorytree', sitedata);
</script>
<div id="tabpanel">
  <table>
    <tr>
    <td class="spacer"></td>
    <td class="tab selected" selected="true"><div class="tableft"><div class="tabright">Items</div></div></td>
{if $request.query.type!='item'}
    <td class="spacer"></td>
    <td class="tab unselected" onmouseover="this.className='tab hover'" onmouseout="this.className='tab unselected'">
      <div class="tableft"><div class="tabright">
        <a href="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/attachments.tpl"}">Item Attachments</a>
      </div></div>
    </td>
    <td class="spacer"></td>
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

<div id="leftpane" class="pane">
	<div class="header">
		<table class="toolbar">
			<tr>
				<td>
					<div id="okbtn-disabled" class="toolbarbutton disabledtoolbarbutton" style="width: auto">
						<img src="{$CONTENT}/icons/check-grey.gif"/> Save
					</div>
					<div id="okbtn" class="toolbarbutton" style="width: auto; display: none">
						<a href="javascript:selectItem()"><img src="{$CONTENT}/icons/check-grey.gif"/> Save</a>
					</div>
				</td>
				<td>
					<div class="toolbarbutton" style="width: auto">
						<a href="javascript:cancel()"><img src="{$CONTENT}/icons/delete-page-blue.gif"/> Cancel</a>
					</div>
				</td>
			</tr>
		</table>
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
