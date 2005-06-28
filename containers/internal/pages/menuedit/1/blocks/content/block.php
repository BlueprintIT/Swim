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
<applet class="com.blueprintit.menuedit.MenuEdit" width="90%" height="400"
 codebase="/internal/file/webedit" classpath="menuedit.jar,log4j-1.2.9.jar,jdom.jar,swixml.jar,swim.jar">
 <param name="swim.base" value="<?= $prefs->getPref('url.pagegen') ?>">
 <param name="menu" value="<?= $request->resource.'/block.xml' ?>">
 <param name="commit" value="<?= $commit->encode() ?>">
 <param name="cancel" value="<?= $cancel->encode() ?>">
</applet>
</div>
