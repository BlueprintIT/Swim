<?
$index = new Request();
$index->method='view';
$index->resource='internal/page/site/block/mainpane/file/index.html';
?>
<iframe name="main" style="height: 100%; width: 100%" frameborder="0" src="<?= $index->encode() ?>"></iframe>
