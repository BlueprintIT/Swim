<?

$page = Resource::decodeResource($request);
$version=$page->version;
$page = $page->makeWorkingVersion();
$pageprefs = $page->prefs;
$layout=$page->getLayout();

$upload = new Request();
$upload->method = 'save';
$upload->resource = $request->resource;

$commit = new Request();
$commit->method='commit';
$commit->resource=$request->resource;
$commit->query['version']=$version;
$commit->nested = new Request();
$commit->nested->method = $request->nested->method;
$commit->nested->resource = $request->nested->resource;

$cancel = new Request();
$cancel->method='cancel';
$cancel->resource=$request->resource;
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
<table style="width: 100%; table-layout: fixed; border-spacing: 5px;">
<tr>
	<td style="vertical-align: top"><label for="title">Title:</label></td>
	<td style="vertical-align: top; width: 45%"><input style="width: 100%" type="input" id="title" name="pref:page.variables.title" value="<?= $pageprefs->getPref('page.variables.title','New Page') ?>"></td>
	<td style="vertical-align: top; width: 45%">The page title is displayed in the browser title bar.</td>
</tr>
<tr>
	<td style="vertical-align: top"><label for="description">Description:</label></td>
	<td style="vertical-align: top"><textarea style="width: 100%; height: 50px;" id="description" name="pref:page.variables.description"><?= $pageprefs->getPref('page.variables.description','') ?></textarea></td>
	<td style="vertical-align: top">The page description is displayed by many search engines. If left blank search engines will normally display the first
	 paragraph of the page instead.</td> 
</tr>
<tr>
  <td style="vertical-align: top"><label for="keywords">Keywords:</label></td>
  <td style="vertical-align: top"><input style="width: 100%" type="input" id="keywords" name="pref:page.variables.keywords" value="<?= $pageprefs->getPref('page.variables.keywords','') ?>"></td>
  <td style="vertical-align: top">Search engines may use these keywords when indexing this page. Many of the more popular search engines
   generally don't place very much weight on this.</td>
</tr>
<tr>
  <td style="vertical-align: top"><label for="layout">Layout:</label></td>
  <td style="vertical-align: top"><select id="layout" onchange="this.form.submit()" name="layout">
<?
$layouts = LayoutManager::getPageLayouts();
foreach($layouts as $id => $l)
{
?>    <option value="<?= $id ?>"<?

  if ($layout===$l)
    print(' selected="true"');
  print('>'.$l->getName()) 
?></option>
<?
}
?>
</select></td>
  <td style="vertical-align: top"></td>
</tr>
<?
foreach ($layout->blocks as $id => $blk)
{
  if ($id!='content')
  {
    $block = $page->getReferencedBlock($id);
    if ($block!==null)
    {
?>
<tr>
  <td style="vertical-align: top"><?= $blk->getName() ?>:</td>
<?
      if ($blk->getType()=='image')
      {
        block_image($id,$block);
      }
?>
</tr>
<?
    }
  }
}
?>
<?
if (isset($contentfile))
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
