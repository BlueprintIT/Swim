<?
$create = new Request();
$create->method='create';
$create->resource='global/page';
?>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form method="GET" action="<?= $create->encodePath() ?>">
<input type="submit" value="Create new Page">
</form>
<?
}
?>
<h2>Site Administration</h2>
</div>
<div class="body">
<p>Welcome to the SWIM administration interface.</p>
</div>
