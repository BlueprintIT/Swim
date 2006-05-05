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
$upload->resourcePath = $container->id.'/categories';

if (isset($request->query['category']))
	$upload->resourcePath.='/'.$category->id;

$commit = new Request();
$commit->method = 'view';
$commit->resource = 'internal/page/categorydetails';
$commit->query['container'] = $container->id;
$commit->query['reloadtree'] = true;

?>
<script>
function submitForm(form, type)
{
  if (type)
  {
    document.forms[form].elements[type].disabled=false;
  }
  document.forms[form].submit();
}

</script>
<form name="mainform" method="POST" action="<?= $upload->encodePath() ?>">
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
<input type="hidden" disabled="true" name="action:commit" value="Save &amp; Commit">
<input type="hidden" disabled="true" name="action:cancel" value="Cancel">
<div class="toolbar">
<div class="toolbarbutton">
<a href="javascript:submitForm('mainform','action:commit')">Save &amp; Commit</a>
</div>
<div class="toolbarbutton">
<a href="javascript:submitForm('mainform','action:cancel')">Cancel</a>
</div>
</div>
<h2>Edit Category</h2>
</div>
<div class="body">
<table>
<tr>
  <td style="vertical-align: top">Name:</td>
  <td style="vertical-align: top"><input type="text" name="name" value="<?= $category->name ?>"></td>
</tr>
<tr>
  <td style="vertical-align: top">Icon:</td>
  <td style="vertical-align: top"><filebrowser name="icon" container="<?= $container->id ?>" value="<?= $category->icon ?>"/></td>
</tr>
<tr>
  <td style="vertical-align: top">Hover Icon:</td>
  <td style="vertical-align: top"><filebrowser name="hovericon" container="<?= $container->id ?>" value="<?= $category->hovericon ?>"/></td>
</tr>
</table>
</div>
</form>
