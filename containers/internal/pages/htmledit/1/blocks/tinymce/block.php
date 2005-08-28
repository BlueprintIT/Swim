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
	swim_attachments : "<?= $expurl->encode() ?>",
	content_css : new Array(<?= $list ?>),
	relative_urls : true,
	document_base_url : "<?= $expurl->encode() ?>",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect",
	theme_advanced_buttons2 : "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,pagelink,unlink,image,separator,help",
	theme_advanced_buttons3 : ""
});
</script>

<div align="center">
<form method="POST" action="<?= $upload->encodePath(); ?>">
<?= $upload->getFormVars(); ?>
<input type="hidden" name="commit" value="<?= $commit->encode(); ?>">
<input type="hidden" name="continue" value="<?= $request->encode(); ?>">
<textarea id="editor" name="content" style="width: 90%; height: 80%;"><?

$file=$working->openFileRead($request->data['file']);
fpassthru($file);
$working->closeFile($file);

?></textarea>
<input type="submit" name="action_commit" value="Save and make available"> <input type="Submit" name="action_continue" value="Save and continue working">
</form>
<form method="POST" action="<?= $cancel->encodePath() ?>">
<?= $cancel->getFormVars() ?>
<input type="submit" name="action_cancel" value="Cancel Editing">
</form>
</div>
