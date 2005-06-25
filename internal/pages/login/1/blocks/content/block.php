<?

$newrequest = new Request();
$newrequest->method='login';
$newrequest->nested=&$request->nested;

?>
<form action='<?= $newrequest->encodePath() ?>' method='POST'>
<table>
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
