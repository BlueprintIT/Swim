<?

$user = new User(substr($request->resource,5));

$save = new Request();
$save->method='saveuser';
$save->nested=$request->nested;

?>
<form method="POST" action="<?= $save->encodePath() ?>">
<?= $save->getFormVars() ?>
<div class="header">
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
<h2>Edit User</h2>
</div>
<div class="body">
<input type="hidden" name="username" value="<?= $user->getUsername() ?>">
<table>
<tr>
<td>Username:</td>
<td><?= $user->getUsername() ?></td>
<td>The username is used to log in to the administration area. It cannot be changed for an existing user.</td>
</tr>
<tr>
<td><label for="password">Password:</label></td>
<td><input type="text" name="password" id="password" value=""></td>
<td>Enter a new password for the user. Leave blank if you do not wish to change this user's password.</td>
</tr>
<tr>
<td><label for="name">Full Name:</label></td>
<td><input type="text" name="name" id="name" value="<?= $user->getName() ?>"></td>
<td>The full name is used to display the user's name in the administrative area.</td>
</tr>
<?
$groups = UserManager::getGroups();
$pos=0;
foreach ($groups as $id => $group)
{
?>
<tr>
<?
if ($pos==0)
{
  $pos=1;
?><td rowspan="<?= count($groups) ?>">Groups:</td><?
}
?>
<td><input type="radio" name="group" id="group-<?= $id ?>" value="<?= $id ?>"<?
if ($user->inGroup($id))
{
  print(' checked="true"');
}
?>><label for="group-<?= $id ?>"><?= $group->getName() ?></label></td>
<td><?= $group->getDescription() ?></td>
</tr>
<?
}
?>
</table>
</div>
</form>
