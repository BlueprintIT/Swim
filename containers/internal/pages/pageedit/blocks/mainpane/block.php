<?

$page = &Resource::decodeResource($request);
$version=$page->version;
$page = &$page->makeWorkingVersion();
$pageprefs = &$page->prefs;

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

$block=$page->getReferencedBlock('content');
if ($block->prefs->isPrefSet('block.stylesheets'))
{
    $styles=explode(',',$block->prefs->getPref('block.stylesheets'));
    $styles[]='global/file/styles/global.css';
    $list = '';
    foreach ($styles as $s)
    {
        $style = new Request();
        $style->method='view';
        $style->resource=$s;
        $list.='"'.$style->encode().'",';
    }
    $list=substr($list,0,-1);
}
$contentfile = $block->getDir().'/block.html';
$pagedir = $page->getDir();
if (substr($contentfile,0,strlen($pagedir))==$pagedir)
{
  $contentfile=substr($contentfile,strlen($pagedir));
}
else
{
  unset($contentfile);
}
?>
<script src="/internal/file/tinymce/tiny_mce_src.js"/>
<script language="javascript" type="text/javascript">
tinyMCE.init({
    mode : "exact",
    elements : "editor",
    theme : "advanced",
    plugins : "swim",
    content_css : new Array(<?= $list ?>),
    remove_linebreaks : false,
    relative_urls : true,
    document_host : "<?= $_SERVER['HTTP_HOST'] ?>",
    inline_styles : true,
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_blockformats : "h1,h2,p",
    theme_advanced_buttons1 : "cut,copy,paste,separator,undo,redo,separator,link,pagelink,unlink,image,separator,help",
    theme_advanced_buttons2 : "formatselect,separator,numlist,bullist,separator,outdent,indent,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bold,italic,underline",
    theme_advanced_buttons3 : ""
});
</script>
<form action="<?= $upload->encodePath() ?>" method="POST">
<?= $upload->getFormVars() ?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="continue" value="<?= $request->encode(); ?>">
<input type="hidden" name="cancel" value="<?= $cancel->encode(); ?>">
<div class="header">
<input type="submit" name="action:continue" value="Save Working Version">
<input type="submit" name="action:commit" value="Save &amp; Commit">
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
<?
if (isset($contentfile))
{
?>
<tr>
	<td style="vertical-align: top"><label for="editor">Content:</label</td>
  <td style="vertical-align: top" colspan="2"><textarea id="editor" name="file:<?= $contentfile ?>" style="width: 100%; height: 400px"><?
readfile($block->getDir().'/block.html');
?></textarea></td>
</tr>
<?
}
?>
</table>
</div>
</form>
