{include file='includes/adminheader.tpl' title=$request.query.title}
<div id="mainpane" class="pane">
	<iframe style="height: 100%; width: 100%" frameborder="0" src="{$request.query.url}"></iframe>
</div>
{include file='includes/adminfooter.tpl'}
