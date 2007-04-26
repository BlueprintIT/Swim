{secure contacts="read" login="true"}
{include file='includes/adminheader.tpl' title="Mailing Options"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$SHARED/treeview/iconnode.css"}
{stylesheet href="$CONTENT/styles/mailingtree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/treeview`$smarty.config.YUI`.js"}
{apiget var="section" type="section" id=$request.query.section}
<script type="text/javascript">
function displayTree(event)
{ldelim}
  var tree = new YAHOO.widget.TreeView("categorytree");
  var root = tree.getRoot();
  var details = {ldelim}
    label: "Contacts",
    href: "{encode method="admin" path="mailing/contacts.tpl" section=$section->getId()}",
    target: "main",
    type: "users"
  {rdelim};
  new BlueprintIT.widget.IconNode(details, root, true);
  details = {ldelim}
    label: "Mailings",
    type: "category"
  {rdelim};
  var mailings = new BlueprintIT.widget.IconNode(details, root, true);
{foreach from=$section->getMailings() item="mailing"}
  details = {ldelim}
    label: "{$mailing->getName()}",
    href: "{encode method="admin" path="mailing/maildetails.tpl" section=$section->getId() mailing=$mailing->getId()}",
    target: "main",
    type: "mailing"
  {rdelim};
  new BlueprintIT.widget.IconNode(details, mailings, false);
{/foreach}
  {assign var="item" value=$section->getRootItem()}
  details = {ldelim}
    label: "Past Mailings",
    type: "category"
  {rdelim};
  mailings = new BlueprintIT.widget.IconNode(details, root, true);
  tree.draw();
{rdelim}

YAHOO.util.Event.addListener(window, "load", displayTree);
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<h2>Mailing Options</h2>
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
