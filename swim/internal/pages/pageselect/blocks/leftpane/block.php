<stylesheet src="/internal/file/yahoo/css/folders/tree.css"/>
<script src="/internal/file/yahoo/YAHOO.js"/>
<script src="/internal/file/yahoo/event.js"/>
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
    global $request;
    
    if ($page instanceof Page)
    {
      $request = SwimEngine::getCurrentRequest();
      
      if ($request->resource !== $page)
      {
        $link = new Request();
        $link->resource = $request->resource;
        $link->method=$request->method;
        $link->query['page']=$page->getPath();
        return $link->encode();
      }
    }
    return parent::getItemLink($page);
  }
}

?>
<div class="header">
<h2>Structure</h2>
</div>
<div class="body">
<div id="categorytree"></div>
<?
$cont = $_PREFS->getPref('container.default');
if (isset($request->query['container']))
  $cont = $request->query['container'];
$cm = getContainer($cont);
$tree = new LinkedCategoryTree("categorytree", $cm->getRootCategory());
$tree->display();
?>
</div>
