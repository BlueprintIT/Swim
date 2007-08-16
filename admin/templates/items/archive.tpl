{secure documents="read" login="true"}
{include file='includes/adminheader.tpl' title="Content management"}
{stylesheet href="`$smarty.config.YUISOURCE`/treeview/assets/treeview.css"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{stylesheet href="$SITECONTENT/sitetree.css"}
{script href="$SHARED/json/json`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="`$smarty.config.YUISOURCE`/event/event`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dom/dom`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/animation/animation`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/connection/connection`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/treeview/treeview`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/sitetree`$smarty.config.YUI`.js"}
<script>
{if $smarty.config.inlinetree}
var sitedata = {php}
global $_PREFS;
$request = $this->get_template_vars('REQUEST');
include_once $_PREFS->getPref('storage.methods').'/tree.php';
displayArchive(Session::getCurrentVariant());{/php};
{else}
var sitedata = null;
{/if}
{literal}
function onTreeItemClick(id)
{
	var request = new Request();
	request.setMethod('admin');
	request.setPath('items/details.tpl');
	request.setQueryVar('item', id);
	document.getElementById('main').src = request.encode();
}
{/literal}
var SiteTree = new BlueprintIT.widget.SiteTree('archive', '{encode method='tree' archive='true'}', 'categorytree', sitedata);
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
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
