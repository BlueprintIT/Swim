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
<form method="GET" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="submit" value="Create new User">
</form>
<?
if ($user->getUsername()!='blueprintit')
{
?>
<form method="GET" action="<?= $edit->encodePath() ?>">
<input type="submit" value="Edit this User">
<?= $edit->getFormVars() ?>
</form>
<?
  if ($user->getUsername()!=$_USER->getUsername())
  {
?>
<form method="GET" action="<?= $delete->encodePath() ?>">
<?= $delete->getFormVars() ?>
<input type="submit" value="Delete this User">
</form>
<?
  }
}
?>
<h2>User Details</h2>
</div>
<div class="body">
<table>
<tr>
<td>Username:</td>
<td><?= $user->getUsername() ?></td>
</tr>
<tr>
<td>Full Name:</td>
<td><?= $user->getName() ?></td>
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
?><td rowspan="<?= count($groups) ?>">Groups:</td><?
}
?>
<td><?= $group->getName() ?></td>
</tr>
<?
}
?>
</table>
</div>
