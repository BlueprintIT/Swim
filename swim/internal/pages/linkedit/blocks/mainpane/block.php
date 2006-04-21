<?

$container = getContainer($request->query['container']);
if (isset($request->query['link']))
	$link = $container->getLink($request->query['link']);
else
{
	$parent = $container->getCategory($request->query['parent']);
	$link = new Link($parent, null, "New Link", "http://");
}
	
$upload = new Request();
$upload->method = 'save';
$upload->resourcePath = $container->id.'/links';

if (isset($request->query['link']))
	$upload->resourcePath.='/'.$link->id;

$commit = new Request();
$commit->method = 'view';
$commit->resource = 'internal/page/linkdetails';
$commit->query['container'] = $container->id;
$commit->query['reloadtree'] = true;

?>
<form method="POST" action="<?= $upload->encodePath() ?>">
<?= $upload->getFormVars() ?>
<?
if (!isset($request->query['link']))
{
?>
<input type="hidden" name="parent" value="<?= $link->parent->id ?>">
<?
}
?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="cancel" value="<?= $request->nested->encode(); ?>">
<div class="header">
<input type="submit" name="action:commit" value="Save &amp; Commit">
<input type="submit" name="action:cancel" value="Cancel">
<h2>Edit Link</h2>
</div>
<div class="body">
<table>
<tr>
  <td style="vertical-align: top">Name:</td>
  <td style="vertical-align: top"><input type="text" name="name" value="<?= $link->name ?>"></td>
</tr>
<tr>
  <td style="vertical-align: top">Address:</td>
  <td style="vertical-align: top"><input type="text" name="address" value="<?= $link->address ?>"></td>
</tr>
</table>
</div>
</form>
