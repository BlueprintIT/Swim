<?

$create = new Request();
$create->method='users';
$create->resourcePath='create';
$create->nested=$request;

$user = new User(substr($request->resourcePath,5));

$edit = new Request();
$edit->method='users';
$edit->resourcePath='edit/'.$user->getUsername();
$edit->nested=$request;

$delete = new Request();
$delete->method='deleteuser';
$delete->resourcePath=$user->getUsername();
$delete->nested=$request;

?>
<div class="header">
<div class="toolbar">
<div class="toolbarbutton">
<a href="<?= $create->encode() ?>"><image src="/internal/file/icons/add-user-blue.gif"/> Create new User</a>
</div>
<?
if ($user->getUsername()!='blueprintit')
{
?>
<div class="toolbarbutton">
<a href="<?= $edit->encode() ?>"><image src="/internal/file/icons/edit-grey.gif"/> Edit this User</a>
</div>
<?
  if ($user->getUsername()!=$_USER->getUsername())
  {
?>
<div class="toolbarbutton">
<a href="<?= $delete->encode() ?>">Delete this User</a>
</div>
<?
  }
}
?>
</div>
<h2>User Details</h2>
</div>
<div class="body">
<div class="section first">
<div class="sectionheader">
<h3>User Details</h3>
</div>
<div class="sectionbody">
<table class="admin">
<tr>
<td class="label">Username:</td>
<td class="details"><?= $user->getUsername() ?></td>
</tr>
<tr>
<td class="label">Full Name:</td>
<td class="details"><?= $user->getName() ?></td>
</tr>
<?
$groups = $user->getGroups();
$pos=0;
foreach ($groups as $id)
{
  $group = new Group($id);
?>
<tr>
<?
if ($pos==0)
{
  $pos=1;
?><td class="label" rowspan="<?= count($groups) ?>">Groups:</td><?
}
?>
<td class="details"><?= $group->getName() ?></td>
</tr>
<?
}
?>
</table>
</div>
</div>
</div>
