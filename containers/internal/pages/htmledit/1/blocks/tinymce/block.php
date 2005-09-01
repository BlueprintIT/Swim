<?

$resource = &Resource::decodeResource($request);
if ($resource->prefs->isPrefSet('block.stylesheets'))
{
	$styles=explode(',',$resource->prefs->getPref('block.stylesheets'));
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

$working=&$resource->makeWorkingVersion();

$upload = new Request();
$upload->method="upload";
$upload->resource=$working->getPath().'/file/'.$request->data['file'];
$upload->query['version']=$working->version;

$commit = new Request();
$commit->method='commit';
$commit->resource=$request->resource;
$commit->query['version']=$resource->version;
$commit->nested=&$request->nested;

$cancel = new Request();
$cancel->method='cancel';
$cancel->resource=$request->resource;
$cancel->query['version']=$resource->version;
$cancel->nested=&$request->nested;

$browser = new Request();
$browser->method='fileselect';
$browser->resource=$working->getPath().'/file/attachments';
$browser->query['version']=$working->version;

$pagebrowser = new Request();
$pagebrowser->method='view';
$pagebrowser->resource='internal/page/pageselect';

$expurl = new Request();
$expurl->method='view';
$expurl->resource='version/'.$working->version.'/'.$working->getPath().'/file/';

?>

<script src="/internal/file/tinymce/jscripts/tiny_mce/tiny_mce_src.js"/>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	plugins : "swim",
	swim_browser : "<?= $browser->encode() ?>",
	swim_pagebrowser : "<?= $pagebrowser->encode() ?>",
	swim_attachments : "<?= $expurl->encode() ?>",
	swim_cancel : "<?= $cancel->encode() ?>",
	content_css : new Array(<?= $list ?>),
	remove_linebreaks : false,
	relative_urls : true,
	document_base_url : "<?= $expurl->encode() ?>",
	inline_styles : true,
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_blockformats : "h1,h2,p",
	theme_advanced_buttons1 : "'General',separator,cancel,save,commit,separator,cut,copy,paste,separator,undo,redo,separator,link,pagelink,unlink,image,separator,help",
	theme_advanced_buttons2 : "'Formatting',separator,formatselect,separator,numlist,bullist,separator,outdent,indent,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bold,italic,underline",
	theme_advanced_buttons3 : ""
});
</script>

<div align="center" style="width: 90%; height: 80%; margin-left: auto; margin-right: auto">
<div style="text-align: center; width: 100%; height: 10%; background: #F0F0EE; border-top: 1px solid #cccccc; border-left: 1px solid #cccccc; border-right: 1px solid #cccccc;">
<div style="float: left; color: blue; font-weight: bold; padding-top: 15px;">
<image style="vertical-align: middle" src="/global/file/images/edit.gif"/> Content Editor
</div>
<image style="vertical-align: middle" src="/global/file/images/swimlogo.gif"/>
by
<image style="vertical-align: middle" src="/global/file/images/bpitlogo.gif"/>
</div>
<form method="POST" action="<?= $upload->encodePath(); ?>">
<?= $upload->getFormVars(); ?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="continue" value="<?= $request->encode(); ?>">
<textarea id="editor" name="content" style="width: 100%; height: 90%;"><?

$file=$working->openFileRead($request->data['file']);
fpassthru($file);
$working->closeFile($file);

?></textarea>
</form>
</div>
