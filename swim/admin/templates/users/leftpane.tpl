{stylesheet href="$SHARED/yui/treeview/assets/tree.css"}
{stylesheet href="$SHARED/treeview/treeview.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dragdrop/dragdrop`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/treeview/treeview`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/BlueprintIT.js"}
{script href="$SHARED/scripts/treeview.js"}
<script type="text/javascript">
function displayTree(event)
{ldelim}
  var tree = new YAHOO.widget.TreeView("categorytree");
  var details = {ldelim}
    label: "Users",
    iconClass: "category"
  {rdelim};
  var root = new BlueprintIT.widget.StyledTextNode(details, tree.getRoot(), true);
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
  print("    label: \"".$name."\",\n");
  print("    iconClass: \"user\",\n");
  print("    href: \"".$edit->encode()."\"\n");
  print("  };\n");
  print("  new BlueprintIT.widget.StyledTextNode(details, root, false);\n");
}
{/php}
  tree.draw();
{rdelim}

YAHOO.util.Event.addListener(window, "load", displayTree);
</script>
<div id="leftpane" class="pane">
	<div class="header">
		<h2>Users</h2>
	</div>
	<div class="body">
		<div id="categorytree">
		</div>
	</div>
</div>
