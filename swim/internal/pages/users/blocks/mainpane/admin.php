<?
$create = new Request();
$create->method='users';
$create->resourcePath='create';
$create->nested=$request;
?>
<div class="header">
<form method="GET" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="submit" value="Create new User">
</form>
<h2>User Administration</h2>
</div>
<div class="body">
<p>Welcome to the SWIM administration interface.</p>
</div>
