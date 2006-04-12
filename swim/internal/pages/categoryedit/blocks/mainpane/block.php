<?

$container = getContainer($request->query['container']);
$category = $container->getCategory($request->query['category']);

$upload = new Request();
$upload->method = 'save';
$upload->resource = $container->id.'/categories/'.$category->id;

?>
<form method="POST" action="<?= $upload->encodePath() ?>">
<?= $upload->getFormVars() ?>
<input type="hidden" name="commit" value="<?= $request->nested->encode(); ?>">
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
