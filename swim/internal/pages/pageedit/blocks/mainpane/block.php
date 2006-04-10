<?

if (isset($request->query['version']))
{
	$page = Resource::decodeResource($request->query['page'], $request->query['version']);
}
else
{
	$page = Resource::decodeResource($request->query['page']);
}
$version=$page->version;
$page = $page->makeWorkingVersion();
$pageprefs = $page->prefs;
$layout=$page->getLayout();

$upload = new Request();
$upload->method = 'save';
$upload->resource = $page->getPath();

$commit = new Request();
$commit->method='commit';
$commit->resource=$page->getPath();
$commit->query['version']=$version;
$commit->nested = new Request($request->nested);
$commit->nested->query['reloadtree']=true;

$cancel = new Request();
$cancel->method='cancel';
$cancel->resource=$page->getPath();
$cancel->nested=$request->nested;

include 'html.php';
include 'image.php';

?>
<form action="<?= $upload->encodePath() ?>" method="POST">
<?= $upload->getFormVars() ?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="default" value="<?= $request->encode(); ?>">
<input type="hidden" name="cancel" value="<?= $cancel->encode(); ?>">
<div class="header">
<input type="submit" name="action:commit" value="Save &amp; Commit">
<input type="submit" name="action:default" value="Save Working Version">
<input type="submit" name="action:cancel" value="Cancel">
<h2>Page Editor</h2>
</div>
<div class="body">
<table style="table-layout: fixed; border-spacing: 5px;">
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
  <td style="vertical-align: top"><label for="layout">Layout:</label></td>
  <td style="vertical-align: top"><select id="layout" onchange="this.form.submit()" name="layout">
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
  <td style="vertical-align: top">The layout determines what the page contains and how it is organised.</td>
</tr>
<?
}

foreach ($layout->variables as $pref => $variable)
{
?>
<tr>
	<td style="vertical-align: top"><label for="pref:<?= $pref ?>"><?= $variable->name ?>:</label></td>
	<td style="vertical-align: top; width: 45%"><?
if ($variable->type == 'text')
{
  ?><input style="width: 100%" type="input" id="pref:<?= $pref ?>" name="pref:<?= $pref ?>" value="<?= $pageprefs->getPref($pref) ?>"><?
}
else if ($variable->type == 'multiline')
{
  ?><textarea style="width: 100%; height: 50px;" id="pref:<?= $pref ?>" name="pref:<?= $pref ?>"><?= $pageprefs->getPref($pref) ?></textarea><?
}
?></td>
	<td style="vertical-align: top; width: 45%"><?= $variable->description ?></td>
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
  <td style="vertical-align: top"><?= $blk->getName() ?>:</td>
<?
      if ($blk->getType()=='image')
      {
        block_image($id,$block,$blk);
      }
?>
</tr>
<?
    }
  }
}
?>
<?
if ((isset($contentfile))&&($_USER->canWrite($content)))
{
?>
<tr>
	<td style="vertical-align: top"><label for="editor">Content:</label></td>
  <td style="vertical-align: top" colspan="2"><textarea id="editor" name="file:<?= $contentfile ?>" style="width: 100%; height: 400px"><?
readfile($content->getDir().'/block.html');
?></textarea></td>
</tr>
<?
}
?>
</table>
</div>
</form>
