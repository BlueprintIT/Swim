<?

if (($_SERVER['REQUEST_METHOD']=="POST")&&(isset($_POST['swim_action'])))
{
	if ($_USER->login($_POST['swim_username'],$_POST['swim_password']))
	{
		$newpage = new Page($page->request->nested);
		if ($_USER->canAccess($newpage))
		{
			redirect($page->request->nested);
		}
		else
		{
			print("<p>Cant access</p>");
		}
	}
	else
	{
		print("<p>Wrong details</p>");
	}
}

$request = new Request();
$request->page=$page->request->page;
$request->nested=&$page->request->nested;

?>
<form action="<?= $request->encodePath() ?>" method="POST">
<?= $request->getFormVars() ?>	<input type="hidden" name="swim_action" value="login"/>
<input type="text" name="swim_username" value=""/>
<input type="password" name="swim_password" value=""/>
<input type="submit"/>
</form>
