<?

$edit = new Request();

$page = Resource::decodeResource($request);
$pageprefs = $page->prefs;
$edit->query['version']=$page->version;
$edit->method='edit';
$edit->nested=$request;

$edit->resource=$request->resource;


?>
<div class="header">
<form action="<?= $edit->encodePath() ?>" method="GET">
<?= $edit->getFormVars() ?>
<?
if (($_USER->canWrite($page))&&($page->prefs->getPref("page.editable")!==false))
{
  ?><input type="submit" value="Edit"><?
}
else
{
  ?><input type="submit" value="Edit" disabled="true"><?
}?>
</form>
<h2>Page Details</h2>
</div>
<div class="body">
<table>
<tr>
    <td style="vertical-align: top"><label for="title">Version:</label></td>
    <td style="vertical-align: top" colspan="2"><?

$versions=$page->getVersions();
$verlist = array_keys($versions);
rsort($verlist);
$select = new Request();
$select->method=$request->method;
$select->resource=$request->resource;

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
$revert = new Request();
$revert->query['version']=$page->version;
$revert->method='revert';
$revert->resource=$request->resource;
$revert->nested=$request;
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
</td>
</tr>
<tr>
    <td style="vertical-align: top">Title:</td>
    <td style="vertical-align: top"><?= $pageprefs->getPref('page.variables.title','New Page') ?></td>
    <td style="vertical-align: top">The page title is displayed in the browser title bar.</td>
</tr>
<tr>
    <td style="vertical-align: top">Description:</td>
    <td style="vertical-align: top"><?= $pageprefs->getPref('page.variables.description','') ?></td>
    <td style="vertical-align: top">The page description is displayed by many search engines. If left blank search engines will normally display the first
     paragraph of the page instead.</td> 
</tr>
<tr>
    <td style="vertical-align: top">Keywords:</td>
    <td style="vertical-align: top"><?= $pageprefs->getPref('page.variables.keywords','') ?></td>
    <td style="vertical-align: top">Search engines may use these keywords when indexing this page. Many of the more popular search engines
     generally don't place very much weight on this.</td>
</tr>
<tr>
    <td style="vertical-align: top">Content:</td>
    <td style="vertical-align: top" colspan="2">
<?
$block=$page->getReferencedBlock('content');
?><block id="content" src="version/<?= $page->version ?>/<?= $block->getPath() ?>"/>
    </td>
</tr>
</table>
</div>
