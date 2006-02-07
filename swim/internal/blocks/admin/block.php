<?

if ($_USER->isLoggedIn())
{
?>
Logged in as <?= $_USER->getName() ?> <anchor method="logout">Change Password</anchor> <anchor method="logout">Logout</anchor>
<?
}

?>
