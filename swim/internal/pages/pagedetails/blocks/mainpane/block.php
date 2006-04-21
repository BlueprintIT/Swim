<script src="/internal/file/yahoo/YAHOO.js"/>
<script src="/internal/file/yahoo/event.js"/>
<script src="/internal/file/yahoo/connection.js"/>
<script>
<?

if (isset($request->query['version']))
	$page = Resource::decodeResource($request->query['page'], $request->query['version']);
else
	$page = Resource::decodeResource($request->query['page']);

$cont = $page->container;

if (isset($request->query['category']))
	$category = $cont->getCategory($request->query['category']);
else
	$category = $cont->getRootCategory();


$pageprefs = $page->prefs;
$layout=$page->getLayout();

$edit = new Request();
$edit->query['version']=$page->version;
$edit->query['page']=$page->getPath();
$edit->method='view';
$edit->resource='internal/page/pageedit';
$edit->nested=$request;

$create = new Request();
$create->method='create';
$create->query['category']=$category->id;
$create->resourcePath=$cont->id.'/page';

$delete = new Request();
$delete->resource=$page;
$delete->method='delete';
$delete->nested = new Request();
$delete->nested->method='view';
$delete->nested->query['reloadtree']=true;
$delete->nested->resource='internal/page/categorydetails';
$root = $cont->getRootCategory();
$delete->nested->query['category']=$root->id;
$delete->nested->query['container']=$cont->id;

$mutate = new Request();
$mutate->method='mutate';
$mutate->resourcePath=$cont->id.'/categories';
$mutate->query['page']=$page->getPath();

if (isset($request->query['reloadtree']))
{
?>
	window.top.SiteTree.loadTree();
<?
}

?>

function removeCompleted(req) {
	var row = document.getElementById("catrow-"+req.argument.category);
	row.style.display="none";
	window.top.SiteTree.loadTree();
	var option = document.getElementById("linktocat-"+req.argument.category);
	option.disabled=false;
}

function removeFromCategory(button) {
	var category = button.id.substring(10);
	if (category) {
		var callback = {
			success: removeCompleted,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				category: category
			}
		};
		var target = "<?= $mutate->encode() ?>";
		target=target+"&action=remove&category="+category;
		YAHOO.util.Connect.asyncRequest("GET", target, callback, null);
	}
}

function addCompleted(req) {
	window.top.SiteTree.loadTree();
	var option = document.getElementById("linktocat-"+req.argument.category);
	option.disabled=true;
	document.getElementById("linkcategory").selectedIndex=-1;
	var row = document.getElementById("catrow-"+req.argument.category);
	row.style.display=null;
}

function addToCategory(button) {
	var category = document.getElementById("linkcategory").value;
	if (category) {
		var callback = {
			success: addCompleted,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				category: category
			}
		};
		var target = "<?= $mutate->encode() ?>";
		target=target+"&action=add&category="+category;
		YAHOO.util.Connect.asyncRequest("GET", target, callback, null);
	}
}

function buttonClicked(event) {
	if (this.className=="remove")
		removeFromCategory(this);
	else if (this.className=="add")
		addToCategory(this);
}

function findButtons() {
	var buttons = document.getElementsByTagName("button");
	for (var i=0; i<buttons.length; i++) {
		YAHOO.util.Event.addListener(buttons[i], "click", buttonClicked);
	}
}

YAHOO.util.Event.addListener(window, "load", findButtons);

</script>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<!--<form method="GET" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="submit" value="Create new Page">
</form>-->
<?
}
?>
<?
if ($_USER->canWrite($page))
{
?>
<form action="<?= $edit->encodePath() ?>" method="GET">
<?= $edit->getFormVars() ?>
<input type="submit" value="Edit this Page">
</form>
<form onsubmit="return confirm('This will delete this page, continue?');" action="<?= $delete->encodePath() ?>" method="GET">
<?= $delete->getFormVars() ?>
<input type="submit" value="Delete this Page">
</form>
<?
}
?>
<h2>Page Details</h2>
</div>
<div class="body">
<table>
<tr>
    <td style="vertical-align: top"><label for="title">Version:</label></td>
    <td style="vertical-align: top"><?

$versions=$page->getVersions();
$verlist = array_keys($versions);
rsort($verlist);
$select = new Request();
$select->method=$request->method;
$select->resource=$request->resource;
$select->query['page']=$request->query['page'];

?>
<form style="display: inline" action="<?= $select->encodePath() ?>" method="GET">
<?= $select->getFormVars() ?>
        <select name="version" onchange="this.form.submit();">
<?
        foreach ($verlist as $version)
        {
            $pagev = $versions[$version];
            if ($version == $page->version)
            {
?>            <option value="<?= $version ?>" selected="true"><?
            }
            else
            {
?>            <option value="<?= $version ?>"><?
            }
            print($version.' created at '.formatdate($pagev->getModifiedDate()));
            if ($pagev->isCurrentVersion())
            {
              print(' (Current version)');
            }
?></option>
<?
        }
?>
        </select>
</form>
<?
if (($_USER->canWrite($page))&&($page->prefs->getPref("page.editable")!==false))
{
  $revert = new Request();
  $revert->query['version']=$page->version;
  $revert->method='revert';
  $revert->resource=$page;
  $revert->nested= new Request($request);
  $revert->nested->query['reloadtree']=true;
?>
<form style="display: inline" method="POST" action="<?= $revert->encodePath() ?>">
<?= $revert->getFormVars() ?>
<input type="submit" value="Make current version" <? 
if ($page->isCurrentVersion())
{
  print('disabled="true"');
}
?>>
</form>
<?
}
?>
</td>
</tr>
<tr>
	<td>Currently listed in:</td>
	<td>
		<table>
<?
function showCategoryRemove($page,$category,$indent)
{
	$style='';
	if ($category->indexOf($page)===false)
		$style='style="display: none" ';
?>
			<tr <?= $style?>id="catrow-<?= $category->id ?>">
				<td><?= $indent.$category->name ?></td>
				<td><button id="removeBtn-<?= $category->id ?>" class="remove" type="button">Remove...</button></td>
			</tr>
<?
	$items = $category->items();
	foreach ($items as $item)
	{
		if ($item instanceof Category)
			showCategoryRemove($page,$item, $indent.$category->name.' &gt; ');
	}
}

showCategoryRemove($page,$cont->getRootCategory(),'');
?>		</table>
	</td>
</tr>
<tr>
	<td>Link to another category:</td>
	<td>
		<form>
			<select id="linkcategory" name="category">
<?
function showCategoryOption($page,$category,$indent)
{
	print('        <option id="linktocat-'.$category->id.'" value="'.$category->id.'"');
	if ($category->indexOf($page)!==false)
		print(' disabled="disabled"');
	print('>'.$indent.' '.$category->name.'</option>'."\n");
	$items = $category->items();
	foreach ($items as $item)
	{
		if ($item instanceof Category)
			showCategoryOption($page,$item, '--'.$indent);
	}
}

showCategoryOption($page,$cont->getRootCategory(),'');
?>
			</select>
			<button class="add" type="button">Add...</button>
		</form>
	</td>
</tr>
<?
$layouts = $page->container->layouts->getPageLayouts();
$count = 0;
foreach($layouts as $id => $l)
{
  if ($l->hidden != false)
  	$count++;
  if ($count==2)
  	break;
}

if ($count>1)
{
?>
<tr>
  <td style="vertical-align: top">Layout:</td>
  <td style="vertical-align: top"><?= $layout->getName() ?></td>
</tr>
<?
}
foreach ($layout->variables as $pref => $variable)
{
?>
<tr>
  <td style="vertical-align: top"><?= $variable->name ?>:</td>
  <td style="vertical-align: top"><?= $pageprefs->getPref($pref) ?></td>
</tr>
<?
}
?>
<tr>
  <td style="vertical-align: top">Content:</td>
  <td style="vertical-align: top">
<?
$block=$page->getReferencedBlock('content');
?><block id="content" src="/version/<?= $page->version ?>/<?= $block->getPath() ?>"/>
  </td>
</tr>
</table>
</div>
