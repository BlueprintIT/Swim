<stylesheet src="/internal/file/yahoo/css/folders/tree.css"/>
<script type="text/javascript" src="/internal/file/yahoo/YAHOO.js"></script>
<script type="text/javascript" src="/internal/file/yahoo/dragdrop.js"></script>
<script type="text/javascript" src="/internal/file/yahoo/event.js"></script>
<script type="text/javascript" src="/internal/file/yahoo/treeview.js"></script>
<script type="text/javascript" src="/internal/file/scripts/BlueprintIT.js"></script>
<script type="text/javascript" src="/internal/file/scripts/treeview.js"></script>
<script type="text/javascript">
function displayTree(event)
{
  var tree = new YAHOO.widget.TreeView("categorytree");
  var details = {
    label: "Users",
    iconClass: "category"
  };
  var root = new BlueprintIT.widget.StyledTextNode(details, tree.getRoot(), true);
<?
$users = UserManager::getAllUsers();
foreach ($users as $username => $user)
{
  if (strlen($user->getName())>0)
    $name = $user->getName();
  else
    $name = $username;
  $edit = new Request();
  $edit->method='users';
  $edit->resourcePath='view/'.$username;
?>
  details = {
    label: "<?= $name ?>",
    iconClass: "user",
    href: "<?= $edit->encode() ?>"
  };
  new BlueprintIT.widget.StyledTextNode(details, root, false);
<?
}
?>
  tree.draw();
}

YAHOO.util.Event.addListener(window, "load", displayTree);
</script>
<div class="header">
<h2>Users</h2>
</div>
<div class="body">
<div id="categorytree">
</div>
</div>
