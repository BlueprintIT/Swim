<?

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
<form action="<?= $commit->encodePath() ?>" method="POST">
<?= $commit->getFormVars() ?>
<table>
<tr>
	<td style="vertical-align: top"><label for="title">Title:</label></td>
	<td style="vertical-align: top"><input type="input" id="title" name="page.variables.title" value="<?= $pageprefs->getPref('page.variables.title','New Page') ?>"></td>
	<td style="vertical-align: top">The page title is displayed in the browser title bar.</td>
</tr>
<?
	if ($page===false)
	{
		$layouts=&getAllLayouts();
		if (count($layouts)>0)
		{
?>
<tr>
	<td style="vertical-align: top"><label for="layout">Layout:</label></td>
	<td style="vertical-align: top"><select name="layout" id="layout">
<?
			foreach ($layouts as $id => $layout)
			{
?>
	<option value="<?= $layout->id ?>"><?= $layout->name ?></option>
<?
			}
?>
	</select></td>
	<td style="vertical-align: top">The page layout.</td>
</tr>
<?
		}
	}
?>
<tr>
	<td style="vertical-align: top"><label for="description">Description:</label></td>
	<td style="vertical-align: top"><textarea id="description" name="page.variables.description" cols="40" rows="5"><?= $pageprefs->getPref('page.variables.description','') ?></textarea></td>
	<td style="vertical-align: top">The page description is displayed by many search engines. If left blank search engines will normally display the first
	 paragraph of the page instead.</td> 
</tr>
<tr>
	<td style="vertical-align: top"><label for="keywords">Keywords:</label></td>
	<td style="vertical-align: top"><input type="input" id="keywords" name="page.variables.keywords" value="<?= $pageprefs->getPref('page.variables.keywords','') ?>"></td>
	<td style="vertical-align: top">Search engines may use these keywords when indexing this page. Many of the more popular search engines
	 generally don't place very much weight on this.</td>
</tr>
<tr>
	<td colspan="2" style="vertical-align: top">
		<input type="checkbox" id="makedefault" name="makedefault" value="true"<?
if (($page!==false)&&($page->getPath()==$this->prefs->getPref('method.view.defaultresource')))
{
	print(' checked="checked" disabled="true"');
}
?>><label for="makedefault">This page is the default page.</label>
	</td>
	<td style="vertical-align: top">Makes this page the default home page for the website.</td>
</tr>
<tr>
	<td colspan="2" style="text-align: center"><input type="submit" value="Save"></td><td></td>
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
<form action="<?= $revert->encodePath() ?>" method="POST">
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
			<option value="<?= $version ?>"><?= $version ?> created at <?= formatdate($pagev->getModifiedDate()) ?></option>
<?
			}
		}
?>
		</select>
	</td>
	<td><input type="submit" value="Revert"></td>
</tr>
</table>
</form>
<hr>
<form action="<?= $request->nested->encodePath() ?>" method="GET">
<?= $request->nested->getFormVars() ?>
<div style="text-align: center;"><input type="submit" value="Cancel changes"></div>
</form>
<?
		}
	}
	else
	{
	}
?>