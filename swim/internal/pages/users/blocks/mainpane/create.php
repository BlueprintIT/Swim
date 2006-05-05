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

function submitForm(form)
{
  document.forms[form].submit();
}

</script>
<form name="mainform" onsubmit="return checkForm(this)" method="POST" action="<?= $save->encodePath() ?>">
<?= $save->getFormVars() ?>
<div class="header">
<div class="toolbar">
<div class="toolbarbutton">
<a href="javascript:submitForm('mainform')">Save</a>
</div>
<div class="toolbarbutton">
<a href="<?= $request->nested->encode() ?>">Cancel</a>
</div>
</div>
<h2>Create User</h2>
</div>
<div class="body">
<div class="section first">
<div class="sectionheader">
<h3>User Details</h3>
</div>
<div class="sectionbody">
<table class="admin">
<tr>
<td class="label"><label for="username">Username:</label></td>
<td class="details"><input type="input" name="username" id="username" value=""></td>
<td class="description">The username is used to log in to the administration area.</td>
</tr>
<tr>
<td class="label"><label for="password">Password:</label></td>
<td class="details"><input type="text" name="password" id="password" value=""></td>
<td class="description">Enter a password for the user.</td>
</tr>
<tr>
<td class="label"><label for="name">Full Name:</label></td>
<td class="details"><input type="text" name="name" id="name" value=""></td>
<td class="description">The full name is used to display the user's name in the administrative area.</td>
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
?><td class="label" rowspan="<?= count($groups) ?>">Groups:</td><?
}
?>
<td class="details"><input type="radio" name="group" id="group-<?= $id ?>" value="<?= $id ?>"><label for="group-<?= $id ?>"><?= $group->getName() ?></label></td>
<td class="description"><?= $group->getDescription() ?></td>
</tr>
<?
}
?>
</table>
</div>
</div>
</div>
</form>
