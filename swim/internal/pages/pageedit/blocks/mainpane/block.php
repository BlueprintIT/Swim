<?

if (isset($request->query['version']))
{
	$page = Resource::decodeResource($request->query['page'], $request->query['version']);
}
else
{
	$page = Resource::decodeResource($request->query['page']);
}
$details = $page->getWorkingDetails();

if ((!$details->isMine())&&(isset($request->query['forcelock'])))
{
	if ($request->query['forcelock']=='continue')
	{
		$details->takeOver();
	}
	else if ($request->query['forcelock']=='discard')
	{
		$details->takeOverClean();
	}
}

if (!$details->isMine())
{
	displayLocked($request,$details,$resource);
}

$version=$page->version;
$page = $page->makeWorkingVersion();
$pageprefs = $page->prefs;
$layout=$page->getLayout();

$upload = new Request();
$upload->method = 'save';
$upload->resource = $page;

$commit = new Request();
$commit->method='commit';
$commit->resource=$page;
$commit->query['version']=$version;
$commit->nested = new Request($request->nested);
$commit->nested->query['reloadtree']=true;
if (isset($commit->nested->query['version']))
  unset($commit->nested->query['version']);

$cancel = new Request();
$cancel->method='cancel';
$cancel->resource=$page;
$versions = array_keys($page->getVersions());
if ((count($versions)==1) && ($versions[0]=='base'))
{
  $deletecheck = ' onclick="return confirm(\'This page has not been saved yet so this will delete this page, continue?\');"';
	$cancel->nested = new Request();
	$cancel->nested->method='view';
	$cancel->nested->resource='internal/page/categorydetails';
  $cancel->nested->query['container']=$page->container->id;
  $cancel->nested->query['reloadtree']='true';
	if (isset($request->nested->query['category']))
		$cancel->nested->query['category']=$request->nested->query['category'];
}
else
{
  $deletecheck='';
	$cancel->nested=$request->nested;
}

include 'html.php';
include 'image.php';

?>
<script>
function submitForm(form, type)
{
  if (type)
  {
    document.forms[form].elements[type].disabled=false;
  }
  document.forms[form].submit();
}

<?
if (isset($request->query['reloadtree']))
{
?>
  window.top.SiteTree.loadTree();
<?
}
?>
  window.top.SiteTree.selectPage("<?= $page->getPath(); ?>");
</script>
<form name="mainform" action="<?= $upload->encodePath() ?>" method="POST">
<?= $upload->getFormVars() ?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="default" value="<?= $request->encode(); ?>">
<input type="hidden" name="cancel" value="<?= $cancel->encode(); ?>">
<div class="header">
<input type="hidden" disabled="true" name="action:commit" value="Save &amp; Commit">
<input type="hidden" disabled="true" name="action:default" value="Save Working Version">
<input type="hidden" disabled="true" name="action:cancel" value="Cancel">
<div class="toolbar">
<div class="toolbarbutton">
<a href="javascript:submitForm('mainform','action:commit')">Save &amp; Commit</a>
</div>
<div class="toolbarbutton">
<a href="javascript:submitForm('mainform','action:default')">Save Working Version</a>
</div>
<div class="toolbarbutton">
<a <?= $deletecheck ?>href="javascript:submitForm('mainform','action:cancel')">Cancel</a>
</div>
</div>
<h2>Page Editor</h2>
</div>
<div class="body">
<div class="section first">
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
  <td class="label"><label for="layout">Layout:</label></td>
  <td class="details"><select id="layout" onchange="this.form.submit()" name="layout">
<?
	foreach($layouts as $id => $l)
	{
	  if ($l->hidden == false)
	  {
?>    <option value="<?= $id ?>"<?
	
	  if ($layout===$l)
	    print(' selected="true"');
	  print('>'.$l->getName()) 
?></option>
<?
	  }
	}
?>
</select></td>
  <td class="description">The layout determines what the page contains and how it is organised.</td>
</tr>
<?
}

foreach ($layout->variables as $pref => $variable)
{
?>
<tr>
	<td class="label"><label for="pref:<?= $pref ?>"><?= $variable->name ?>:</label></td>
	<td class="details"><?
if ($variable->type == 'text')
{
  ?><input style="width: 100%" type="input" id="pref:<?= $pref ?>" name="pref:<?= $pref ?>" value="<?= $pageprefs->getPref($pref) ?>"><?
}
else if ($variable->type == 'multiline')
{
  ?><textarea style="width: 100%; height: 50px;" id="pref:<?= $pref ?>" name="pref:<?= $pref ?>"><?= $pageprefs->getPref($pref) ?></textarea><?
}
?></td>
	<td class="description"><?= $variable->description ?></td>
</tr>
<?
}

foreach ($layout->blocks as $id => $blk)
{
  if ($id!='content')
  {
    $block = $page->getReferencedBlock($id);
    if (($block!==null)&&($_USER->canWrite($block)))
    {
?>
<tr>
  <td class="label"><?= $blk->getName() ?>:</td>
<?
      if ($blk->getType()=='image')
      {
        block_image($id,$page,$block,$blk);
      }
?>
</tr>
<?
    }
  }
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
<?
if ((isset($contentfile))&&($_USER->canWrite($content)))
{
?>
  <textarea id="editor" name="file:<?= $contentfile ?>" style="width: 100%; height: 400px"><?
@readfile($content->getDir().'/block.html');
?></textarea>
<?
}
?>
</div>
</div>
</div>
</form>
