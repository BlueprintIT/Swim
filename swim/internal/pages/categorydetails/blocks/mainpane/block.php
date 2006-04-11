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
$createp->resource=$container->id.'/page';

$createl = new Request();
$createl->method='create';
$createl->query['category']=$category->id;
$createl->resource=$container->id.'/link';

$createc = new Request();
$createc->method='create';
$createc->query['category']=$category->id;
$createc->resource=$container->id.'/category';

$edit = new Request();
$edit->method='view';
$edit->query['category']=$category->id;
$edit->resource='internal/page/categoryedit';

$mutate = new Request();
$mutate->method='mutate';
$mutate->resource=$container->id.'/categories';
$mutate->query['category']=$category->id;

?>
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
	var list = document.getElementById("contentList");

	var button = document.getElementById("moveUpBtn");
	button.disabled=(list.selectedIndex<=0);
	
	button = document.getElementById("moveDownBtn");
	button.disabled=((list.selectedIndex<0)||(list.selectedIndex==(list.length-1)));
}

function init(event) {
	var button = document.getElementById("moveUpBtn");
	YAHOO.util.Event.addListener(button, "click", moveUp);
	button = document.getElementById("moveDownBtn");
	YAHOO.util.Event.addListener(button, "click", moveDown);
	var list = document.getElementById("contentList");
	YAHOO.util.Event.addListener(list, "change", updateButtons);
	updateButtons();
}

YAHOO.util.Event.addListener(window, "load", init);

</script>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form method="GET" action="<?= $createp->encodePath() ?>">
<?= $createp->getFormVars() ?>
<input type="submit" value="Add a new Page">
</form>
<form method="GET" action="<?= $createl->encodePath() ?>">
<?= $createl->getFormVars() ?>
<input type="submit" value="Add a new Link">
</form>
<form method="GET" action="<?= $createc->encodePath() ?>">
<?= $createc->getFormVars() ?>
<input type="submit" value="Add a new Category">
</form>
<form action="<?= $edit->encodePath() ?>" method="GET">
<?= $edit->getFormVars() ?>
<input type="submit" value="Edit Category">
</form>
<?
}
?>
<h2>Category Details</h2>
</div>
<div class="body">
<table>
<tr>
  <td style="vertical-align: top">Name:</td>
  <td style="vertical-align: top"><?= $category->name ?></td>
</tr>
<tr>
  <td style="vertical-align: top">Contents:</td>
  <td style="vertical-align: top">
  	<table>
  		<tr>
  			<td rowspan="2">
  				<select id="contentList" size="7">
<?
$items= $category->items();
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
?>						<option value="<?= $pos ?>"><?= $name ?> (<?= $type ?>)</option>
<?
	$pos++;
}
?>
  				</select>
  			</td>
  			<td>
  				<button id="moveUpBtn" type="button">Move Up</button>
  			</td>
  		</tr>
  		<tr>
  			<td>
  				<button id="moveDownBtn" type="button">Move Down</button>
  			</td>
  		</tr>
  	</table>
  </td>
</tr>
</table>
</div>
