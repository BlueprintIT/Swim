<?

$block = &Resource::decodeResource($request);

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
 <param name="swim.base" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $prefs->getPref('url.pagegen') ?>">
 <param name="menu" value="<?= $request->resource.'/block.xml' ?>">
 <param name="commit" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $commit->encode() ?>">
 <param name="cancel" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $cancel->encode() ?>">
</applet>
</div>
