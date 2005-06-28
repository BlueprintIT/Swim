<?

$resource = &Resource::decodeResource($request);
$block=&$resource->getBlock();

$commit = new Request();
$commit->method='commit';
$commit->resource=$request->resource;
$commit->query['version']=$block->version;
$commit->nested=&$request->nested;

$cancel = new Request();
$cancel->method='cancel';
$cancel->resource=$request->resource;
$cancel->query['version']=$block->version;
$cancel->nested=&$request->nested;

?>
<div align="center">
<applet class="com.blueprintit.webedit.WebEdit" width="90%" height="400"
 codebase="/internal/file/webedit" classpath="webedit.jar,log4j-1.2.9.jar,jdom.jar,swixml.jar,swim.jar">
 <param name="swim.base" value="<?= $prefs->getPref('url.pagegen') ?>">
 <param name="style" value="/global/template/base/layout/content.css">
 <param name="html" value="<?= $request->resource.'/block.html' ?>">
</applet>
</div>
<p>Once you have finished editing you must click save in the editor, then click commit below.</p>
<div style="float: right"><a href="<?= $commit->encode() ?>">Commit</a></div>
<div style="float: left"><a href="<?= $cancel->encode() ?>">Cancel</a></div>
