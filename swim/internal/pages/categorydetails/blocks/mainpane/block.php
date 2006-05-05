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
$category = $container->getCategory($request->query['category']);

$createp = new Request();
$createp->method='create';
$createp->query['category']=$category->id;
$createp->resourcePath=$container->id.'/page';

$createl = new Request();
$createl->method='view';
$createl->query['parent']=$category->id;
$createl->query['container']=$container->id;
$createl->resource='internal/page/linkedit';
$createl->nested=$request;

$createc = new Request();
$createc->method='view';
$createc->query['parent']=$category->id;
$createc->query['container']=$container->id;
$createc->resource='internal/page/categoryedit';
$createc->nested=$request;

if ($category->parent!==null)
{
	$delete = new Request();
	$delete->method='delete';
	$delete->resourcePath=$container->id.'/categories/'.$category->id;
	$delete->nested = new Request();
	$delete->nested->method='view';
	$delete->nested->resource = 'internal/page/categorydetails';
	$delete->nested->query['container'] = $container->id;
	$delete->nested->query['category'] = $category->parent->id;
	$delete->nested->query['reloadtree'] = true;
}

$edit = new Request();
$edit->method='view';
$edit->query['container']=$container->id;
$edit->query['category']=$category->id;
$edit->resource='internal/page/categoryedit';
$edit->nested=$request;

$mutate = new Request();
$mutate->method='mutate';
$mutate->resourcePath=$container->id.'/categories';
$mutate->query['category']=$category->id;

$move = new Request();
$move->method='mutate';
$move->resourcePath=$container->id.'/categories';
$move->query['subcategory']=$category->id;

?>
  window.top.SiteTree.selectCategory("<?= $category->id; ?>");
  
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

function moveUpComplete(req) {
	var list = document.getElementById("contentList");
	var top = list.options[req.argument.index-1];
	var bottom = list.options[req.argument.index];
	bottom.parentNode.insertBefore(bottom, top);
	updateButtons();
	window.top.SiteTree.loadTree();
}

function moveUp() {
	var list = document.getElementById("contentList");

	if (list.selectedIndex>0) {
		var callback = {
			success: moveUpComplete,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				index: list.selectedIndex
			}
		};
		var target = "<?= $mutate->encode() ?>";
		target=target+"&action=moveup&item="+list.selectedIndex;
		YAHOO.util.Connect.asyncRequest("GET", target, callback, null);
	}
}

function moveDownComplete(req) {
	var list = document.getElementById("contentList");
	var top = list.options[req.argument.index];
	var bottom = list.options[req.argument.index+1];
	bottom.parentNode.insertBefore(bottom, top);
	updateButtons();
	window.top.SiteTree.loadTree();
}

function moveDown() {
	var list = document.getElementById("contentList");

	if (list.selectedIndex<(list.length-1)) {
		var callback = {
			success: moveDownComplete,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				index: list.selectedIndex
			}
		};
		var target = "<?= $mutate->encode() ?>";
		target=target+"&action=movedown&item="+list.selectedIndex;
		YAHOO.util.Connect.asyncRequest("GET", target, callback, null);
	}
}

function updateButtons() {
	/*var list = document.getElementById("contentList");
	if (list) {
		var button = document.getElementById("moveUpBtn");
		button.disabled=(list.selectedIndex<=0);
	
		button = document.getElementById("moveDownBtn");
		button.disabled=((list.selectedIndex<0)||(list.selectedIndex==(list.length-1)));
	}*/
}

function init(event) {
	/*var list = document.getElementById("contentList");
	if (list)
		YAHOO.util.Event.addListener(list, "change", updateButtons);
	updateButtons();*/
}

YAHOO.util.Event.addListener(window, "load", init);

</script>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<?
if ($category !== $container->getRootCategory())
{
?>
<div class="toolbar">
<div class="toolbarbutton">
<a href="<?= $edit->encode() ?>"><image src="/internal/file/icons/edit-grey.gif"/> Edit this Category</a>
</div>
<div class="toolbarbutton">
<a onclick="return confirm('This will delete this category, continue?');" href="<?= $delete->encode() ?>"><image src="/internal/file/icons/delete-folder-blue.gif"/> Delete this Category</a>
</div>
</div>
<?
}
}
?>
<h2>Category Details</h2>
</div>
<div class="body">
<div class="section first">
<div class="sectionheader">
<h3>Category Details</h3>
</div>
<div class="sectionbody">
<table class="admin">
<tr>
  <td class="label">Name:</td>
  <td class="details"><?= $category->name ?></td>
</tr>
<?
if ($category!==$container->getRootCategory())
{
?>
<tr>
	<td class="label">Move to another category:</td>
	<td class="details">
		<form>
			<select id="targetlist" name="category">
<?
function showCategoryOption($current,$category,$indent)
{
	if ($category===$current)
		return;
	
	print('        <option value="'.$category->id.'">'.$indent.' '.$category->name.'</option>'."\n");
	$items = $category->items();
	foreach ($items as $item)
	{
		if ($item instanceof Category)
			showCategoryOption($current,$item, '--'.$indent);
	}
}

showCategoryOption($category,$container->getRootCategory(),'');
?>
			</select>
      <div class="toolbarbutton"><a href="javascript:moveToCategory()""><image src="/internal/file/icons/right-purple.gif"/> Move</a></div>
		</form>
	</td>
</tr>
</table>
</div>
</div>
<div class="section">
<div class="sectionheader">
<h3>Category Contents</h3>
</div>
<div class="sectionbody">
<?
}
$items= $category->items();
if (count($items)>1)
{
?>
<table>
	<tr>
		<td rowspan="2">
			<select id="contentList" size="7">
<?
$pos=0;
foreach ($items as $item)
{
	$type=get_class($item);
	if ($item instanceof Page)
	{
		$name = $item->prefs->getPref('page.variables.title');
	}
	else
	{
		$name = $item->name;
	}
?>						<option value="<?= $pos ?>"><image src="/internal/file/images/<?= strtolower($type) ?>.gif"/> <?= $name ?> (<?= $type ?>)</option>
<?
	$pos++;
}
?>
			</select>
		</td>
		<td style="text-align: center">
      <div class="toolbarbutton"><a href="javascript:moveUp()"><image src="/internal/file/icons/up-purple.gif"/> Move Up</a></div>
		</td>
	</tr>
	<tr>
		<td style="text-align: center">
      <div class="toolbarbutton"><a href="javascript:moveDown()""><image src="/internal/file/icons/down-purple.gif"/> Move Down</a></div>
		</td>
	</tr>
</table>
<p>
<div class="toolbarbutton"><a href="<?= $createp->encode() ?>"><image src="/internal/file/icons/add-page-blue.gif"/> Add a new Page</a></div>
<div class="toolbarbutton"><a href="<?= $createl->encode() ?>"><image src="/internal/file/icons/add-page-blue.gif"/> Add a new Link</a></div>
<div class="toolbarbutton"><a href="<?= $createc->encode() ?>"><image src="/internal/file/icons/add-folder-blue.gif"/> Add a new Category</a></div>
</p>
<?
}
?>
</div>
</div>
</div>
