<?
$cont = getContainer($_PREFS->getPref('container.default'));
$resource = Resource::decodeResource($request);
if ($resource!==null)
{
  if ($resource->isContainer())
  {
    $cont = $resource;
  }
  else
  {
    $cont = $resource->container;
  }
}

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
<input type="submit" value="Create new Page">
</form>
<?
}
?>
<h2>Site Administration</h2>
</div>
<div class="body">
<p>Welcome to the SWIM administration interface.</p>
</div>
