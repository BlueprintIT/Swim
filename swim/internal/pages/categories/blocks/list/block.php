<?
class XMLTree extends CategoryTree
{
	var $container;
	
  function XMLTree($container)
  {
    $this->CategoryTree($container->getRootCategory());
    $this->padding=' ';
    $this->container=$container;
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
    	$info->resource='internal/page/categorydetails';
    	$info->query['category']=$item->id;
    	$info->query['container']=$this->container->id;
      print($indent.'<category infolink="'.htmlentities($info->encode()).'" id="'.$item->id.'">');
    }
    else if ($item instanceof Page)
    {
    	$info->resource='internal/page/pagedetails';
    	$info->query['page']=$item->getPath();
      print($indent.'<page infolink="'.htmlentities($info->encode()).'" path="'.htmlentities($item->getPath()).'">');
    }
    else if ($item instanceof Link)
    {
      print($indent.'<link path="'.htmlentities($item->address).'">');
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
$container = Resource::decodeResource(substr($request->resource,0,-11));
if (($container === null) || (!$container->isContainer()))
{
  $container = getContainer($_PREFS->getPref('container.default'));
}

?>
	<tree>
<?
$tree = new XMLTree($container);
$tree->showRoot=true;
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
