<?
if (isset($request->query['container']))
	$cont = $request->query['container'];
else
	$cont = $_PREFS->getPref('container.default');

$index = new Request();
$index->method='view';
$index->resource='internal/page/sitedetails';
$index->query['container']=$cont;
?>
<iframe name="main" style="height: 100%; width: 100%" frameborder="0" src="<?= $index->encode() ?>"></iframe>
