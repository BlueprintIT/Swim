<?

$viewurl = new Request();
$viewurl->method='view';
$viewurl->resource='';

$pageselect = new Request();
$pageselect->method='view';
$pageselect->resource='internal/page/pageselect';

$expurl = new Request();
$expurl->method='view';
$expurl->resource=$page->getPath().'/file/';

$browser = new Request();
$browser->method='fileselect';
$browser->resource = $page->getPath();
$browser->query['version']=$page->version;

$content=$page->getReferencedBlock('content');
if ($content->prefs->isPrefSet('block.stylesheets'))
{
    $styles=explode(',',$content->prefs->getPref('block.stylesheets'));
    $styles[]='global/file/styles/global.css';
    $styles[]='internal/file/styles/editor.css';
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
$contentfile = $content->getDir().'/block.html';
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
    apply_source_formatting : true,
    relative_urls : true,
    document_host : "<?= $_SERVER['HTTP_HOST'] ?>",
    document_base_url : "<?= $expurl->encode() ?>",
    swim_view : "<?= $viewurl->encode() ?>",
    swim_pagebrowser : "<?= $pageselect->encode() ?>",
    swim_browser : "<?= $browser->encode() ?>",
    inline_styles : true,
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_blockformats : "h1,h2,p",
    theme_advanced_buttons1 : "cut,copy,paste,separator,undo,redo,separator,link,pagelink,unlink,image,separator,help",
    theme_advanced_buttons2 : "formatselect,separator,numlist,bullist,separator,outdent,indent,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bold,italic",
    theme_advanced_buttons3 : ""
});
</script>
