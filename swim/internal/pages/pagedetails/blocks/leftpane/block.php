<?

class LinkedCategoryTree extends PageTree
{
  function LinkedCategoryTree($root)
  {
    $this->PageTree($root);
  }
  
  function displayPageLabel($page)
  {
    global $pages;
    
    $request = SwimEngine::getCurrentRequest();
    
    if ($request->resource==$page->getPath())
    {
      print('<span class="selected">');
    }
    else
    {
      $link = new Request();
      $link->resource = $page->getPath();
      $link->method='admin';
      print('<a href="'.$link->encode().'">');
    }
    parent::displayPageLabel($page);
    if ($request->resource==$page->getPath())
    {
      print('</span>');
    }
    else
    {
      print('</a>');
    }
  }
}

$edit = new Request();
$edit->method='view';
$edit->resource='internal/page/siteedit';
$edit->nested=$request;
?>
<div class="header">
<?
if ($_USER->hasPermission('documents',PERMISSION_WRITE))
{
?>
<form method="GET" action="<?= $edit->encodePath() ?>">
<?= $edit->getFormVars(); ?>
<input type="submit" value="Edit">
</form>
<?
}
?>
<h2>Structure</h2>
</div>
<div class="body">
<ul class="categorytree">
<?
$cont = $_PREFS->getPref('container.default');
if (isset($request->query['container']))
  $cont = $request->query['container'];
$cm = getContainer($cont);
$tree = new LinkedCategoryTree($cm->getRootCategory());
$tree->display();
?>
</ul>
</div>
