<?

$resource = &Resource::decodeResource($request);
$page=&$resource->getPage();

$commit = new Request();
$commit->method='commit';
$commit->resource=$request->resource;
$commit->query['version']=$page->version;
$commit->nested=&$request->nested;

?>
<form action="<?= $commit->encodePath() ?>" method="GET">
<?= $commit->getFormVars() ?>
<table>
<tr>
	<td>Title:</td>
	<td><input type="input" name="page.variables.title" value="<?= $page->prefs->getPref('page.variables.title') ?>"></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="Save"></td>
</tr>
</table>
</form>
