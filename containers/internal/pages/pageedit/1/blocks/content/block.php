<?

$commit = new Request();

if ($request->method=='edit')
{
	$resource = &Resource::decodeResource($request);
	$page=&$resource->getPage();
	$pageprefs = &$page->prefs;
	$commit->query['version']=$page->version;
	$commit->method='commit';
}
else
{
	$commit->method='docreate';
	$page=false;
	$pageprefs = new Preferences();
	$pageprefs->setParent($_PREFS);
}

$commit->resource=$request->resource;
$commit->nested=&$request->nested;

if ($page!==false)

?>
<form action="<?= $commit->encodePath() ?>" method="GET">
<?= $commit->getFormVars() ?>
<table>
<tr>
	<td>Title:</td>
	<td><input type="input" name="page.variables.title" value="<?= $pageprefs->getPref('page.variables.title','New Page') ?>"></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="Save"></td>
</tr>
</table>
</form>
