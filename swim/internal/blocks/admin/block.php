<?

if ($_USER->isLoggedIn())
{
?>
<p style="margin: 0">Logged in as <?= $_USER->getName() ?></p>
<p style="margin: 0"><anchor method="logout">Change Password</anchor> <anchor method="logout">Logout</anchor></p>
<?
}

?>
