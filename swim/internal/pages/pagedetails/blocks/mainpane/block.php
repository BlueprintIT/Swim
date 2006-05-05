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
  window.top.SiteTree.selectPage("<?= $page->getPath(); ?>");

function removeCompleted(req) {
	var row = document.getElementById("catrow-"+req.argument.category);
	row.style.display="none";
	window.top.SiteTree.loadTree();
	var option = document.getElementById("linktocat-"+req.argument.category);
	option.disabled=false;
}

function removeFromCategory(category) {
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

function addToCategory() {
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
<div class="toolbar">
<div class="toolbarbutton">
<a href="<?= $edit->encode() ?>"><image src="/internal/file/icons/edit-grey.gif"/> Edit this Page</a>
</div>
<div class="toolbarbutton">
<a onclick="return confirm('This will delete this page, continue?');" href="<?= $delete->encode() ?>"><image src="/internal/file/icons/delete-page-blue.gif"/> Delete this Page</a>
</div>
</div>
<?
}
?>
<h2>Page Details</h2>
</div>
<div class="body">
<div class="section first">
<div class="sectionheader">
<h3>Version Control</h3>
</div>
<div class="sectionbody">
<table class="admin">
<tr>
    <td class="label"><label for="title">Version:</label></td>
    <td class="details"><?

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
<form name="versionform" style="display: inline" method="POST" action="<?= $revert->encodePath() ?>">
<?= $revert->getFormVars() ?>
<?
if (!$page->isCurrentVersion())
{
?>
<div class="toolbarbutton">
<a href="javascript:document.forms.versionform.submit();">
<image src="/internal/file/icons/check-blue.gif"/> Make current version
</a>
</div>
<?
}
else
{
?>
<div class="toolbarbutton disabled">
<image src="/internal/file/icons/check-grey.gif"/> Make current version
</div>
<?
}
?>
</div>
</form>
<?
}
?>
</td>
</tr>
</table>
</div>
</div>
<div class="section">
<div class="sectionheader">
<h3>Category Links</h3>
</div>
<div class="sectionbody">
<table class="admin">
<tr>
	<td class="label">Currently listed in:</td>
	<td class="details">
		<table>
<?
function showCategoryRemove($page,$category,$indent)
{
	$style='';
  $listed = false;
	if ($category->indexOf($page)===false)
		$style='style="display: none" ';
  else
    $listed = true;
?>
			<tr <?= $style?>id="catrow-<?= $category->id ?>">
				<td><?= $indent.$category->name ?></td>
				<td>
          <div class="toolbarbutton">
          <a href="javascript:removeFromCategory(<?= $category->id ?>)"><image src="/internal/file/icons/delete-folder-purple.gif"/> Remove</a>
          </div>
        </td>
			</tr>
<?
	$items = $category->items();
	foreach ($items as $item)
	{
		if ($item instanceof Category)
    {
			if (showCategoryRemove($page,$item, $indent.$category->name.' &gt; '))
        $listed = true;
    }
	}
  return $listed;
}

if (!showCategoryRemove($page,$cont->getRootCategory(),''))
{
?>
        <tr>
          <td>
            Page is Uncategorised
          </td>
        </tr>
<?
}
?>		</table>
	</td>
</tr>
<tr>
	<td class="label">Link to another category:</td>
	<td class="details">
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
      <div class="toolbarbutton">
      <a href="javascript:addToCategory()"><image src="/internal/file/icons/left-purple.gif"/> Link</a>
      </div>
		</form>
	</td>
</tr>
</table>
</div>
</div>
<div class="section">
<div class="sectionheader">
<h3>Page Options</h3>
</div>
<div class="sectionbody">
<table class="admin">
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
  <td class="label">Layout:</td>
  <td class="details"><?= $layout->getName() ?></td>
</tr>
<?
}
foreach ($layout->variables as $pref => $variable)
{
?>
<tr>
  <td class="label"><?= $variable->name ?>:</td>
  <td class="details"><?= $pageprefs->getPref($pref) ?></td>
</tr>
<?
}
?>
</table>
</div>
</div>
<div class="section">
<div class="sectionheader">
<h3>Page Content</h3>
</div>
<div class="sectionbody">
  <div id="contentblock">
<?
$block=$page->getReferencedBlock('content');
?><block id="content" src="/version/<?= $page->version ?>/<?= $block->getPath() ?>"/>
  </div>
</div>
</div>
</div>
