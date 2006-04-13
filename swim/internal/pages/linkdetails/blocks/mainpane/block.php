<script src="/internal/file/yahoo/YAHOO.js"/>
<script src="/internal/file/yahoo/event.js"/>
<script src="/internal/file/yahoo/connection.js"/>
<script>
<?

if (isset($request->query['reloadtree']))
{
?>
	window.top.SiteTree.loadTree();
<?
}

$container = getContainer($request->query['container']);
$link = $container->getLink($request->query['link']);

$delete = new Request();
$delete->method='delete';
$delete->resource=$container->id.'/links/'.$link->id;
$delete->nested = new Request();
$delete->nested->method='view';
$delete->nested->resource = 'internal/page/categorydetails';
$delete->nested->query['container'] = $container->id;
$delete->nested->query['category'] = $link->parent->id;
$delete->nested->query['reloadtree'] = true;

$edit = new Request();
$edit->method='view';
$edit->query['container']=$container->id;
$edit->query['link']=$link->id;
$edit->resource='internal/page/linkedit';
$edit->nested=$request;

$move = new Request();
$move->method='mutate';
$move->resource=$container->id.'/categories';
$move->query['link']=$link->id;

?>
function moveCompleted(req) {
	window.top.SiteTree.loadTree();
}

function moveToCategory() {
	var category = document.getElementById("targetlist").value;
	if (category) {
		var callback = {
			success: moveCompleted,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				category: category
			}
		};
		var target = "<?= $move->encode() ?>";
		target=target+"&action=add&category="+category;
		YAHOO.util.Connect.asyncRequest("GET", target, callback, null);
	}
}

function init(event) {
	var button = document.getElementById("moveBtn");
	YAHOO.util.Event.addListener(button, "click", moveToCategory);
}

YAHOO.util.Event.addListener(window, "load", init);

</script>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form action="<?= $edit->encodePath() ?>" method="GET">
<?= $edit->getFormVars() ?>
<input type="submit" value="Edit Link">
</form>
<form onsubmit="return confirm('This will delete this link, continue?');" action="<?= $delete->encodePath() ?>" method="GET">
<?= $delete->getFormVars() ?>
<input type="submit" value="Delete this Link">
</form>
<?
}
?>
<h2>Link Details</h2>
</div>
<div class="body">
<table>
<tr>
  <td style="vertical-align: top">Name:</td>
  <td style="vertical-align: top"><?= $link->name ?></td>
</tr>
<tr>
  <td style="vertical-align: top">Address:</td>
  <td style="vertical-align: top"><?= $link->address ?></td>
</tr>
<tr>
	<td>Move to another category:</td>
	<td>
		<form>
			<select id="targetlist" name="category">
<?
function showCategoryOption($current,$category,$indent)
{
	print('        <option value="'.$category->id.'">'.$indent.' '.$category->name.'</option>'."\n");
	$items = $category->items();
	foreach ($items as $item)
	{
		if ($item instanceof Category)
			showCategoryOption($current,$item, '--'.$indent);
	}
}

showCategoryOption($link,$container->getRootCategory(),'');
?>
			</select>
			<button id="moveBtn" type="button">Move...</button>
		</form>
	</td>
</tr>
</table>
</div>
