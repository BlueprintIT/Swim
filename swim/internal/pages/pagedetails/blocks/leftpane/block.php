<stylesheet src="/internal/file/yahoo/css/folders/tree.css"/>
<script src="/internal/file/yahoo/YAHOO.js"/>
<script src="/internal/file/yahoo/event.js"/>
<script src="/internal/file/yahoo/dom.js"/>
<script src="/internal/file/yahoo/dragdrop.js"/>
<script src="/internal/file/yahoo/treeview.js"/>
<script src="/internal/file/scripts/treeview.js"/>
<?

class LinkedCategoryTree extends YahooPageTree
{
  function LinkedCategoryTree($id, $root)
  {
    $this->YahooPageTree($id, $root);
  }
  
  function getItemLink($page)
  {
    if ($page instanceof Page)
    {
      $request = SwimEngine::getCurrentRequest();
      
      if ($request->resource!=$page->getPath())
      {
        $link = new Request();
        $link->resource = $page->getPath();
        $link->method='admin';
        return $link->encode();
      }
    }
    return parent::getItemLink($page);
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
<div id="categorytree">
<?
$tree = new LinkedCategoryTree("categorytree", $cont->getRootCategory());
$tree->display();
?>
</div>
</div>
