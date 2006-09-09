{secure documents="read" login="true"}
{include file='includes/adminheader.tpl' title="General Options"}
{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$SHARED/treeview/iconnode.css"}
{stylesheet href="$CONTENT/styles/optionstree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT.js"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/dialogs.js"}
{script href="$SHARED/scripts/treeview.js"}
{script href="$SHARED/scripts/dom.js"}
{script href="$SHARED/scripts/treeview.js"}
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
{php}
$users = UserManager::getAllUsers();
foreach ($users as $username => $user)
{
  if (strlen($user->getName())>0)
    $name = $user->getName();
  else
    $name = $username;
  $edit = new Request();
  $edit->setMethod('admin');
  $edit->setPath('users/details.tpl');
  $edit->setQueryVar('user',$username);
  print("  details = {\n");
  print("    type: \"user\",\n");
  print("    target: \"main\",\n");
  print("    label: \"".$name."\",\n");
  print("    href: \"".$edit->encode()."\"\n");
  print("  };\n");
  print("  new BlueprintIT.widget.IconNode(details, root, false);\n");
}
{/php}
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
