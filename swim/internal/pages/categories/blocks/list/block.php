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
    if ($item instanceof Category)
    {
      print($indent.'<category id="'.$item->id.'">');
    }
    else if ($item instanceof Page)
    {
      print($indent.'<page path="'.$item->getPath().'">');
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
?>    <page path="<?= $page->getPath() ?>"><?= htmlspecialchars($page->prefs->getPref('page.variables.title')) ?></page>
<?
}
?>
  </pages>
