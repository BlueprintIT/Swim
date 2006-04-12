<?

$container = getContainer($request->query['container']);
if (isset($request->query['category']))
	$category = $container->getCategory($request->query['category']);
else
{
	$parent = $container->getCategory($request->query['parent']);
	$category = new Category($container, $parent, null, "New Category");
}
	
$upload = new Request();
$upload->method = 'save';
$upload->resource = $container->id.'/categories';

if (isset($request->query['category']))
	$upload->resource.='/'.$category->id;

$commit = new Request();
$commit->method = 'view';
$commit->resource = 'internal/page/categorydetails';
$commit->query['container'] = $container->id;
$commit->query['reloadtree'] = true;

?>
<form method="POST" action="<?= $upload->encodePath() ?>">
<?= $upload->getFormVars() ?>
<?
if (!isset($request->query['category']))
{
?>
<input type="hidden" name="parent" value="<?= $category->parent->id ?>">
<?
}
?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="cancel" value="<?= $request->nested->encode(); ?>">
<div class="header">
<input type="submit" name="action:commit" value="Save &amp; Commit">
<input type="submit" name="action:cancel" value="Cancel">
<h2>Edit Category</h2>
</div>
<div class="body">
<table>
<tr>
  <td style="vertical-align: top">Name:</td>
  <td style="vertical-align: top"><input type="text" name="name" value="<?= $category->name ?>"></td>
</tr>
</table>
</div>
</form>
