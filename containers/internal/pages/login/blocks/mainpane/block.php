<?

$newrequest = new Request();
$newrequest->protocol='https';
$newrequest->method='login';
if (isset($request->nested))
{
  $newrequest->nested=$request->nested;
}
else if (isset($request->query['goto']))
{
  $newrequest->query['goto']=$request->query['goto'];
}

?>
<div class="header">
<h2>Please log in</h2>
</div>
<div class="body">
<h3><? if (isset($request->query['message'])) echo $request->query['message']; ?></h3>
<?
	if ($_USER->isLoggedIn())
	{
?>
<p>The user you are currently logged in as does not have permission for this. You may log in
 as a different user.</p> 
<?
	}
	else
	{
?>
<p>If you were logged in then you may have been automatically logged out for being inactive.</p>
<?
	}
?>
<form action='<?= $newrequest->encodePath() ?>' method='POST'>
<table>
<input type="hidden" name="message" value="<?= $request->query['message'] ?>">
<?= $newrequest->getFormVars() ?>	<input type='hidden' name='swim_action' value='login'>
<tr>
<td>Username:</td><td><input type='text' name='swim_username' value=''></td>
</tr>
<tr>
<td>Password:</td><td><input type='password' name='swim_password' value=''></td>
</tr>
<tr>
<td colspan="2"><input type='submit' value="Login"></td>
</tr>
</table>
</form>
</div>

