<?
if (isset($request->query['container']))
	$cont = getContainer($request->query['container']);
else
	$cont = getContainer($_PREFS->getPref('container.default'));

$index = new Request();
$index->method='view';
$index->resource='internal/page/categorydetails';
$root = $cont->getRootCategory();
$index->query['category']=$root->id;
$index->query['container']=$cont->id;
?>
<iframe name="main" style="height: 100%; width: 100%" frameborder="0" src="<?= $index->encode() ?>"></iframe>
