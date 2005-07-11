<?

function format($date)
{
	return date('g:ia d/m/Y',$date);
}

$commit = new Request();

if ($request->method=='edit')
{
	$page = &Resource::decodeResource($request);
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


?>
<form action="<?= $commit->encodePath() ?>" method="GET">
<?= $commit->getFormVars() ?>
<table>
<tr>
	<td style="vertical-align: top">Title:</td>
	<td style="vertical-align: top"><input type="input" name="page.variables.title" value="<?= $pageprefs->getPref('page.variables.title','New Page') ?>"></td>
	<td style="vertical-align: top">The page title is displayed in the browser title bar.</td>
</tr>
<tr>
	<td style="vertical-align: top">Description:</td>
	<td style="vertical-align: top"><textarea name="page.variables.description" cols="40" rows="5"><?= $pageprefs->getPref('page.variables.description','') ?></textarea></td>
	<td style="vertical-align: top">The page description is displayed by many search engines. If left blank search engines will normally display the first
	 few paragraphs of the page instead.</td> 
</tr>
<tr>
	<td style="vertical-align: top">Keywords:</td>
	<td style="vertical-align: top"><input type="input" name="page.variables.keywords" value="<?= $pageprefs->getPref('page.variables.keywords','') ?>"></td>
	<td style="vertical-align: top">Search engines may use these keywords when indexing this page. Many of the more popular search engines
	 generally don't place very much weight on this.</td>
</tr>
<tr>
	<td colspan="3"><input type="submit" value="Save"></td>
</tr>
</table>
</form>
<?
	if ($page!==false)
	{
		$versions=&$page->getVersions();
		if (count($versions)>1)
		{
			$revert = new Request();
			$revert->method='revert';
			$revert->resource=$request->resource;
			$revert->nested=&$request->nested;
?>
<hr>
<form action="<?= $revert->encodePath() ?>" method="GET">
<?= $revert->getFormVars() ?>
<table>
<tr>
	<td>Change to a different version:</td>
	<td>
		<select name="version">
<?
		foreach (array_keys($versions) as $version)
		{
			$pagev = &$versions[$version];
			if (!$pagev->isCurrentVersion())
			{
?>
			<option value="<?= $version ?>"><?= $version ?> created at <?= format($pagev->getModifiedDate()) ?></option>
<?
			}
		}
?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="Revert"></td>
</tr>
</table>
</form>
<?
		}
	}
?>