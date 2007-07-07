{secure documents="read" login="true"}
{include file='includes/adminheader.tpl' title="General Options"}
{stylesheet href="`$smarty.config.YUISOURCE`/treeview/assets/tree.css"}
{stylesheet href="$SHARED/treeview/iconnode.css"}
{stylesheet href="$CONTENT/styles/optionstree.css"}
{script href="`$smarty.config.YUISOURCE`/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="`$smarty.config.YUISOURCE`/event/event`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/dom/dom`$smarty.config.YUI`.js"}
{script href="`$smarty.config.YUISOURCE`/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
<script type="text/javascript">
function displayTree(event)
{ldelim}
  var tree = new YAHOO.widget.TreeView("categorytree");
  var details = {ldelim}
    label: "Users",
    href: "{encode method="admin" path="users/index.tpl"}",
    target: "main",
    type: "users"
  {rdelim};
  var root = new BlueprintIT.widget.IconNode(details, tree.getRoot(), true);
{apiget var="users" type="user"}
{foreach from=$users item="user"}
  details = {ldelim}
    type: "user",
    target: "main",
    label: "{$user->getName()|default:$user->getUsername()}",
    href: "{encode method="admin" path="users/details.tpl" user=$user->getUsername()}"
  {rdelim};
  new BlueprintIT.widget.IconNode(details, root, false);
{/foreach}
{apiget var="optionsets" type="optionset"}
{if count($optionsets)>0}
  details = {ldelim}
    label: "Option Sets",
    type: "optionsets"
  {rdelim};
  root = new BlueprintIT.widget.IconNode(details, tree.getRoot(), true);
{foreach from=$optionsets item="optionset"}
  details = {ldelim}
    type: "optionset",
    target: "main",
    label: "{$optionset->getName()}",
    href: "{encode method="admin" path="options/optionsetdetails.tpl" optionset=$optionset->getId()}"
  {rdelim};
  new BlueprintIT.widget.IconNode(details, root, false);
{/foreach}
{/if}
{*
  details = {ldelim}
    label: "Advanced",
    type: "advanced"
  {rdelim};
  root = new BlueprintIT.widget.IconNode(details, tree.getRoot(), true);
  details = {ldelim}
    label: "Import",
    target: "main",
    type: "adv-import",
    href: "{encode method="admin" path="options/import.tpl"}"
  {rdelim};
  new BlueprintIT.widget.IconNode(details, root, true);
*}
  tree.draw();
{rdelim}

YAHOO.util.Event.addListener(window, "load", displayTree);
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<h2>Options</h2>
	</div>
	<div class="body">
		<div id="categorytree"></div>
	</div>
</div>

<div id="mainpane" class="pane">
<iframe id="main" name="main" style="height: 100%; width: 100%" scrolling="no" frameborder="0" src=""></iframe>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
