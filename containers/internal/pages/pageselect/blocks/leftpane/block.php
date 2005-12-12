<?

class LinkedCategoryTree extends PageTree
{
  function LinkedCategoryTree($root)
  {
    $this->PageTree($root);
  }
  
  function displayPageLabel($page)
  {
    global $request,$pages;
    
    print('<span>');
    $link = new Request();
    $link->resource = $request->resource;
    $link->method=$request->method;
    $link->query['page']=$page->getPath();
    print('<a href="'.$link->encode().'">');
    parent::displayPageLabel($page);
    print('</a>');
    print('</span>');
  }
}

?>
<div class="header">
<h2>Structure</h2>
</div>
<div class="body">
<ul class="categorytree">
<?
$cm = getCategoryManager('website');
$tree = new LinkedCategoryTree($cm->getRootCategory());
$tree->display();
?>
</ul>
</div>
