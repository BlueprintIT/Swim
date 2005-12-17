<div class="header">
<h2>Users</h2>
</div>
<div class="body">
<ul class="categorytree">
<?
$users = UserManager::getAllUsers();
foreach ($users as $username => $user)
{
?>
<li class="page"><anchor method="users" href="/view/<?= $username ?>"><?
if (strlen($user->getName())>0)
{
  print($user->getName());
}
else
{
  print($username);
}
?></anchor></li>
<?
}
?>
</ul>
</div>
