<?
class XMLTree extends CategoryTree
{
  function XMLTree($root)
  {
    $this->CategoryTree($root);
    $this->padding=' ';
  }
  
  function displayCategoryContentStartTag($category,$indent)
  {
  }
  
  function displayCategoryContentEndTag($category,$indent)
  {
  }
  
  function displayItemStartTag($item,$indent)
  {
  	$info = new Request();
  	$info->method='view';
    if ($item instanceof Category)
    {
    	$info->resource='internal/page/containerdetails';
    	$info->query['container']=$item->id;
      print($indent.'<category infolink="'.$info->encode().'" id="'.$item->id.'">');
    }
    else if ($item instanceof Page)
    {
    	$info->resource='internal/page/pagedetails';
    	$info->query['page']=$item->getPath();
      print($indent.'<page infolink="'.$info->encode().'" path="'.$item->getPath().'">');
    }
    else if ($item instanceof Link)
    {
      print($indent.'<link path="'.$item->address.'">');
    }
  }
  
  function displayItemEndTag($item)
  {
    if ($item instanceof Category)
    {
      print("</category>\n");
    }
    else if ($item instanceof Page)
    {
      print("</page>\n");
    }
    else
    {
      print("</link>\n");
    }
  }
}
?>

  <tree>
<?
$container = Resource::decodeResource(substr($request->resource,0,-11));
if (($container === null) || (!$container->isContainer()))
{
  $container = getContainer($_PREFS->getPref('container.default'));
}
$tree = new XMLTree($container->getRootCategory());
$tree->showRoot=false;
$tree->display('  ');
?>
  </tree>
  <pages>
<?
$pages = $container->getResources('page');
foreach ($pages as $page)
{
	$tree->displayItem($page, '    ');
}
?>
  </pages>
