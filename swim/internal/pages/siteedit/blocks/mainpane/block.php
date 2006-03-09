<?

$block = Resource::decodeResource($request);

$commit = $request->nested;

$cancel = $commit;

?>
<applet class="com.blueprintit.menuedit.MenuEdit" style="width: 100%; height: 100%"
 codebase="/internal/file/java" classpath="MenuEdit.jar,log4j-1.2.9.jar,jdom.jar,swixml.jar">
 <param name="swim.base" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $prefs->getPref('url.pagegen') ?>">
 <param name="resource" value="<?= $request->query['container']  ?>/categories">
 <param name="upload" value="<?= $request->query['container']  ?>/categories">
 <param name="commit" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $commit->encode() ?>">
 <param name="cancel" value="http://<?= $_SERVER['HTTP_HOST'] ?><?= $cancel->encode() ?>">
</applet>
