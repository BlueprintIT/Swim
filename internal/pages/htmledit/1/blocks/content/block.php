HTML Editor
<applet class="com.blueprintit.webedit.WebEdit" width="400" height="400"
 codebase="/files/global/webedit" classpath="webedit.jar,log4j-1.2.9.jar,jdom.jar,swixml.jar">
 <param name="swim.base" value="<?= $prefs->getPref('url.pagegen') ?>">
 <param name="html" value="<?= $request->resource.'/block.html' ?>">
</applet>
