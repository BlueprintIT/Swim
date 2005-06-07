<?

if (($_SERVER['REQUEST_METHOD']=='POST')&&(isset($_POST['swim_action'])))
{
	if ($_USER->login($_POST['swim_username'],$_POST['swim_password']))
	{
		$newpage = $request->nested->getPage();
		if ($_USER->canAccess($request->nested,$newpage))
		{
			redirect($request->nested);
		}
		else
		{
			print('<p>Cant access</p>');
		}
	}
	else
	{
		print('<p>Wrong details</p>');
	}
}

$newrequest = new Request();
$newrequest->page=$request->page;
$newrequest->nested=&$request->nested;

?>
<form action='<?= $newrequest->encodePath() ?>' method='POST'>
<?= $newrequest->getFormVars() ?>	<input type='hidden' name='swim_action' value='login'/>
<input type='text' name='swim_username' value=''/>
<input type='password' name='swim_password' value=''/>
<input type='submit'/>
</form>
