<?

$page = Resource::decodeResource($request);
$pageprefs = $page->prefs;

$edit = new Request();
$edit->query['version']=$page->version;
$edit->method='edit';
$edit->nested=$request;

$edit->resource=$request->resource;

$create = new Request();
$create->method='create';
$create->resource='global/page';

$delete = new Request();
$delete->resource=$page->getPath();
$delete->method='delete';
$delete->nested = new Request();
$delete->nested->resource='admin';

?>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form method="GET" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="submit" value="Create new Page">
</form>
<?
}
?>
<?
if (($_USER->canWrite($page))&&($page->prefs->getPref("page.editable")!==false))
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
</tr>
<tr>
    <td style="vertical-align: top">Description:</td>
    <td style="vertical-align: top"><?= $pageprefs->getPref('page.variables.description','') ?></td>
</tr>
<tr>
    <td style="vertical-align: top">Keywords:</td>
    <td style="vertical-align: top"><?= $pageprefs->getPref('page.variables.keywords','') ?></td>
</tr>
<tr>
    <td style="vertical-align: top">Layout:</td>
    <td style="vertical-align: top"><?= $page->getLayout()->getName() ?></td>
</tr>
<tr>
    <td style="vertical-align: top">Content:</td>
    <td style="vertical-align: top">
<?
$block=$page->getReferencedBlock('content');
?><block id="content" src="version/<?= $page->version ?>/<?= $block->getPath() ?>"/>
    </td>
</tr>
</table>
</div>
