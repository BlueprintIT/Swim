<div class="header">
<h2>Structure</h2>
</div>
<div class="body">
<?
$cm = getCategoryManager('website');
$tree = new CategoryTree($cm->getRootCategory());
$tree->display();
?>
</div>
