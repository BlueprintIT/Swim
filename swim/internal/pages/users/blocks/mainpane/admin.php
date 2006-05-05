<?
$create = new Request();
$create->method='users';
$create->resourcePath='create';
$create->nested=$request;
?>
<div class="header">
<div class="toolbar">
<div class="toolbarbutton">
<a href="<?= $create->encode() ?>"><image src="/internal/file/icons/add-user-blue.gif"/> Create new User</a>
</div>
</div>
<h2>User Administration</h2>
</div>
<div class="body">
<p>Welcome to the SWIM administration interface.</p>
</div>
