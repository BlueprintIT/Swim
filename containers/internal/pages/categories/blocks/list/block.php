<?
class XMLTree extends CategoryTree
{
  function XMLTree(&$root)
  {
    $this->CategoryTree($root);
    $this->padding=' ';
  }
  
  function displayCategoryContentStartTag(&$category,$indent)
  {
  }
  
  function displayCategoryContentEndTag(&$category,$indent)
  {
  }
  
  function displayItemStartTag(&$item,$indent)
  {
    if (is_a($item,'Category'))
    {
      print($indent.'<category id="'.$item->id.'">');
    }
    else if (is_a($item,'Page'))
    {
      print($indent.'<page path="'.$item->getPath().'">');
    }
    else
    {
      print($indent.'<link path="'.$item.'">');
    }
  }
  
  function displayItemEndTag(&$item)
  {
    if (is_a($item,'Category'))
    {
      print("</category>\n");
    }
    else if (is_a($item,'Page'))
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
$cm = &getCategoryManager('website');
$tree = new XMLTree($cm->getRootCategory());
$tree->showRoot=false;
$tree->display('  ');
?>
  </tree>
  <pages>
<?
$container = &getContainer('global');
$pages = &$container->getResources('page');
foreach (array_keys($pages) as $i)
{
?>    <page path="<?= $pages[$i]->getPath() ?>"><?= htmlspecialchars($pages[$i]->prefs->getPref('page.variables.title')) ?></page>
<?
}
?>
  </pages>
