<?

$newrequest = new Request();
$newrequest->method='login';
$newrequest->nested=&$request->nested;

?>
<form action='<?= $newrequest->encodePath() ?>' method='POST'>
<?= $newrequest->getFormVars() ?>	<input type='hidden' name='swim_action' value='login'/>
<input type='text' name='swim_username' value=''/>
<input type='password' name='swim_password' value=''/>
<input type='submit'/>
</form>
