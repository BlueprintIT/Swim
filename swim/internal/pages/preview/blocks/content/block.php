<?

$resource=$request->resource;
if ($resource->isPage())
{
	$resource=$resource->getReferencedBlock('content');
}
if ($resource->isBlock())
{
	$id='content';
	if ($resource->prefs->isPrefSet('block.format'))
	{
		$id=$resource->prefs->getPref('block.format');
	}
?>
<block id="<?= $id ?>" src="/<?= $resource->getPath() ?>"/>
<?
}

?>