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

$cont = getContainer($_PREFS->getPref('container.default'));
$resource = Resource::decodeResource($request);
if ($resource!==null)
{
  if ($resource->isContainer())
  {
    $cont = $resource;
  }
  else
  {
    $cont = $resource->container;
  }
}

$edit = new Request();
$edit->method='view';
$edit->resource='internal/page/siteedit';
$edit->query['container']=$cont->id;
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
$tree = new LinkedCategoryTree($cont->getRootCategory());
$tree->display();
?>
</ul>
</div>
