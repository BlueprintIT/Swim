<?

$save = new Request();
$save->method='saveuser';
$save->nested=$request->nested;

?>
<script>

function checkForm(form)
{
  if (form.elements['username'].value.length==0)
  {
    alert('You must enter a username.');
    return false;
  }
  if (form.elements['password'].value.length==0)
  {
    alert('You must enter a password.');
    return false;
  }
  return true;
}

</script>
<form onsubmit="return checkForm(this)" method="POST" action="<?= $save->encodePath() ?>">
<?= $save->getFormVars() ?>
<div class="header">
<input type="submit" value="Save">
<button type="button" onclick="document.location.href='<?= $request->nested->encode() ?>'">Cancel</button>
<h2>Create User</h2>
</div>
<div class="body">
<table>
<tr>
<td><label for="username">Username:</label></td>
<td><input type="input" name="username" id="username" value=""></td>
<td>The username is used to log in to the administration area.</td>
</tr>
<tr>
<td><label for="password">Password:</label></td>
<td><input type="text" name="password" id="password" value=""></td>
<td>Enter a password for the user.</td>
</tr>
<tr>
<td><label for="name">Full Name:</label></td>
<td><input type="text" name="name" id="name" value=""></td>
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
<td><input type="radio" name="group" id="group-<?= $id ?>" value="<?= $id ?>"><label for="group-<?= $id ?>"><?= $group->getName() ?></label></td>
<td><?= $group->getDescription() ?></td>
</tr>
<?
}
?>
</table>
</div>
</form>
