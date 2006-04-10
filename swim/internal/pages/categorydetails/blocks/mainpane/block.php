<?

if (isset($request->query['reloadtree']))
{
?>
<script>
	window.top.SiteTree.loadTree();
</script>
<?
}

$container = getContainer($request->query['container']);
$category = $container->getCategory($request->query['category']);

$create = new Request();
$create->method='create';
$create->resource=$cont->id.'/page';

?>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form method="GET" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="submit" value="Create new Page">
</form>
<?
}
?>
<h2>Category Details</h2>
</div>
<div class="body">
</div>
