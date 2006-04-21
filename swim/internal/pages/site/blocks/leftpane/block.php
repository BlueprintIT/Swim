<stylesheet src="/internal/file/yahoo/css/folders/tree.css"/>
<script src="/internal/file/yahoo/YAHOO.js"/>
<script src="/internal/file/yahoo/event.js"/>
<script src="/internal/file/yahoo/dom.js"/>
<script src="/internal/file/yahoo/dragdrop.js"/>
<script src="/internal/file/yahoo/connection.js"/>
<script src="/internal/file/yahoo/treeview.js"/>
<script src="/internal/file/scripts/treeview.js"/>
<script src="/internal/page/site/block/leftpane/file/sitetree.js"/>
<script>
<?

if (isset($request->query['container']))
	$cont = getContainer($request->query['container']);
else
	$cont = getContainer($_PREFS->getPref('container.default'));

$index = new Request();
$index->method='view';
$index->resource='internal/page/sitedetails';
$index->query['container']=$cont->id;

$containers = new Request();
$containers->method='view';
$containers->resourcePath=$cont->id.'/categories';
?>
var SiteTree = new BlueprintIT.widget.SiteTree('<?= $index->encode() ?>', '<?= $containers->encode() ?>', 'categorytree');
</script>
<?
$edit = new Request();
$edit->method='view';
$edit->resource='internal/page/siteedit';
$edit->query['container']=$cont->id;
$edit->nested=$request;
?>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<!--<form method="GET" action="<?= $edit->encodePath() ?>">
<?= $edit->getFormVars(); ?>
<input type="submit" value="Edit">
</form>-->
<?
}
?>
<h2>Structure</h2>
</div>
<div class="body">
<div id="categorytree">
	<p>Loading Site...</p>
</div>
</div>
