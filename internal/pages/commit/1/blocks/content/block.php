<?

$newrequest = new Request();
$newrequest->method='docommit';
$newrequest->resource=$request->resource;
$newrequest->query['version']=$request->query['version'];
$newrequest->query['newversion']=$request->query['newversion'];
$newrequest->nested=&$request->nested;

?>
<form action="<?= $newrequest->encodePath() ?>" method="get">
<?= $newrequest->getFormVars() ?>
<?
$pages=&$request->query['pages'];
foreach (array_keys($pages) as $key)
{
	$page=&$pages[$key];
?>
<p>
<input type="hidden" name="container[<?= $key ?>]" value="<?= $page->container ?>">
<input type="hidden" name="id[<?= $key ?>]" value="<?= $page->id ?>">
<input type="hidden" name="ver[<?= $key ?>]" value="<?= $page->version ?>">
<input type="checkbox" name="commit[<?= $key ?>]" value="true"><?= $page->prefs->getPref('page.variables.title') ?></p>
<?
}
?>
<input type="submit">
</form>
