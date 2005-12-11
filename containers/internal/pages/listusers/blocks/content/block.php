<?

$id=$parser->data['blockid'];

if (isset($request->query[$id.':create']))
{
	$user=createUser($request->query[$id.':username']);
	if ($user!==false)
	{
		if ($request->query[$id.':pass1']==$request->query[$id.':pass2'])
		{
			$user->setName($request->query[$id.':fullname']);
			$user->setPassword($request->query[$id.':pass1']);
			$groups=$request->query[$id.':group'];
			foreach ($groups as $group)
			{
				if (strlen($group)>0)
				{
					$user->addGroup($group);
				}
			}
			$user->store();
?><p class="info">User <?= $user->user ?> was created.</p><?
		}
		else
		{
?><p class="warning">User was not created because the passwords did not match.</p><?
		}
	}
	else
	{
?><p class="warning">User was not created because that username is already in use.</p><?
	}
}
else if (isset($request->query[$id.':delete']))
{
	if ($request->query[$id.':username']!='blueprintit')
	{
		$user = new User($request->query[$id.':username']);
		deleteUser($user);
?><p class="info">User <?= $request->query[$id.':username'] ?> was deleted.</p><?
	}
	else
	{
?><p class="warning">The Blueprint IT administrative account cannot be deleted.</p><?
	}
}

$users=getAllUsers();
?>
<table style="width: 100%">
<tr>
<th style="text-align: left; width: 10%">Username</th>
<th style="text-align: left; width: 60%">Full name</th>
<th style="text-align: left; width: 15%">Type</th>
<th style="text-align: left; width: 15%">Options</th>
</tr>
<?
foreach (array_keys($users) as $uid)
{
	$user=$users[$uid];
	if ($user->inGroup('admin'))
	{
		$type='Administrator';
	}
	else
	{
		$type='User';
	}
	$delete = new Request();
	$delete->resource=$request->resource;
	$delete->method=$request->method;
	$delete->query[$id.':delete']='true';
	$delete->query[$id.':username']=$user->getUsername();
?>
<tr>
<td><?= $user->getUsername() ?></td>
<td><?= $user->getName() ?></td>
<td><?= $type ?></td>
<td>
<? if ($user->getUsername()!='blueprintit')
{
?>
<a href="<?= $delete->encode() ?>">Delete</a>
<?
}
?>
<anchor query:user="<?= $user->getUsername() ?>" method="view" href="/global/page/filestore">View Files</anchor>
</td>
</tr>
<?
}

$add = new Request();
$add->resource=$request->resource;
$add->method=$request->method;

?>
</table>
<form action="<?= $add->encodePath() ?>" method="POST">
<?= $add->getFormVars() ?>
<hr>
<p>Create a new user:</p>
<table>
<tr>
<td><label for="username">Username:</label></td>
<td><input type="input" id="username" name="<?= $id ?>:username"<?
if (isset($request->query[$id.':username']))
{
	print(' value="'.$request->query[$id.':username'].'"');
}
?>></td>
</tr>
<tr>
<td><label for="fullname">Full name:</label></td>
<td><input type="input" id="fullname" name="<?= $id ?>:fullname"<?
if (isset($request->query[$id.':fullname']))
{
	print(' value="'.$request->query[$id.':fullname'].'"');
}
?>></td>
</tr>
<tr>
<td><label for="pass1">Password:</label></td>
<td><input type="password" id="pass1" name="<?= $id ?>:pass1"></td>
</tr>
<tr>
<td><label for="pass2">Repeat password:</label></td>
<td><input type="password" id="pass2" name="<?= $id ?>:pass2"></td>
</tr>
<tr>
<td><label for="type">User type:</label></td>
<td><select id="type" name="<?= $id ?>:group[]">
<option>User</option>
<option value="admin">Administrator</option>
</select>
</td>
</tr>
<tr>
<td colspan="2" style="text-align: center"><input type="submit" name="<?= $id ?>:create" value="Create"></td>
</tr>
</table>
</form>
