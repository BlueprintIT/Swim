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
{script href="`$smarty.config.YUISOURCE`/logger/logger`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/connection/connection`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/treeview/treeview`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/sitetree`$smarty.config.YUI`.js"}
<script>
var section = '{$request.query.section}';
{if $smarty.config.inlinetree}
var sitedata = {php}
global $_PREFS;
$request = $this->get_template_vars('REQUEST');
include_once $_PREFS->getPref('storage.methods').'/tree.php';
displaySection(FieldSetManager::getSection($request->getQueryVar('section')), Session::getCurrentVariant());{/php};
{else}
var sitedata = null;
{/if}
{literal}

YAHOO.widget.Logger.enableBrowserConsole();
function onTreeItemClick(id)
{
	if (!SiteTree.dragging) {
		var request = new Request();
		request.setMethod('admin');
		if (id == 'uncat') {
			request.setPath('items/uncategorised.tpl');
			request.setQueryVar('section', section);
		}
		else {
			request.setPath('items/details.tpl');
			request.setQueryVar('item', id);
		}
		document.getElementById('main').src = request.encode();
	}
}

function updateDragMode(input)
{
	SiteTree.setDragMode(input.value);
}
{/literal}
{if isset($request.query.root)}
var SiteTree = new BlueprintIT.widget.SiteTree('null', '{encode method='tree' root=$request.query.root}', 'categorytree', sitedata);
{apiget var="root" type="item" id=$request.query.root}
{else}
var SiteTree = new BlueprintIT.widget.SiteTree('{$request.query.section}', '{encode method='tree' section=$request.query.section}', 'categorytree', sitedata);
{apiget var="section" type="section" id=$request.query.section}
{assign var="root" value=$section->getRootItem()}
{/if}
//SiteTree.setExpandAnim(YAHOO.widget.TVAnim.FADE_IN);
//SiteTree.setCollapseAnim(YAHOO.widget.TVAnim.FADE_OUT);
SiteTree.draggable = true;
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<div style="float: left; text-align: left; margin: 5px 0 0 5px">
			<p><img src="{$CONTENT}/icons/drag-move.gif" alt="Move" style="margin-right: 5px"><input type="radio" onchange="updateDragMode(this)" id="mode_move" name="mode" value="0" checked="checked"> <label for="mode_move">Drag to move</label></p>
			<p><img src="{$CONTENT}/icons/drag-copy.gif" alt="Copy" style="margin-right: 5px"><input type="radio" onchange="updateDragMode(this)" id="mode_copy" name="mode" value="1"> <label for="mode_copy">Drag to copy</label></p>
		</div>
		<h2>Menu</h2>
	</div>
	<div class="body">
		<div id="categorytree">
			<p>Loading Site...</p>
		</div>
	</div>
</div>

<div id="mainpane" class="pane">
{if $request.query.item}
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src="{encode method='admin' path='items/details.tpl' item=$request.query.item}"></iframe>
{else}
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src="{encode method='admin' path='items/details.tpl' item=$root->getId()}"></iframe>
{/if}
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
