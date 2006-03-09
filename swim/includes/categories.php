<?

/*
 * Swim
 *
 * The category handling functions
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Link
{
  var $name;
  var $address;
  
  function Link($name,$address)
  {
    $this->name=$name;
    $this->address=$address;
  }
}

class Category
{
  var $parent;
  var $name;
  var $id;
  var $container;
  var $log;
  var $list;
  
  function Category($container,$parent,$id,$name)
  {
    $this->parent=$parent;
    $this->container=$container;
    $this->id=$id;
    $this->name=$name;
    $this->log=$container->log;
  }
  
  function remove($pos)
  {
    global $_STORAGE;
    $_STORAGE->queryExec('DELETE FROM LinkCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    $_STORAGE->queryExec('UPDATE LinkCategory SET sortkey=sortkey-1 WHERE category='.$this->id.' AND sortkey>'.$pos.';');
    $_STORAGE->queryExec('DELETE FROM PageCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    $_STORAGE->queryExec('UPDATE PageCategory SET sortkey=sortkey-1 WHERE category='.$this->id.' AND sortkey>'.$pos.';');
    $id = $_STORAGE->singleQuery('SELECT id FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
    if ($id!=false)
    {
      $cat=$this->container->getCategory($id);
      $cat->clean();
      $_STORAGE->queryExec('DELETE FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
      $_STORAGE->queryExec('UPDATE Category SET sortkey=sortkey-1 WHERE parent='.$this->id.' AND sortkey>'.$pos.';');
    }
    if (isset($this->list))
      unset($this->list);
  }
  
  function clean()
  {
    global $_STORAGE;
    $_STORAGE->queryExec('DELETE FROM LinkCategory WHERE category='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM PageCategory WHERE category='.$this->id.';');
    
    $result = $_STORAGE->query('SELECT id,parent,name FROM Category WHERE parent='.$this->id.';');
    while ($result->valid())
    {
      $id=$result->current();
      $cat=$this->container->getReadyCategory($id[0],$id[1],$id[2]);
      $cat->clean();
      $result->next();
    }
    $_STORAGE->queryExec('DELETE FROM Category WHERE parent='.$this->id.';');
  }
  
  function count()
  {
    if (isset($this->list))
    {
      $this->list=$this->items();
    }
    return count($this->list);
  }
  
  function item($pos)
  {
    if (isset($this->list))
    {
      $this->list=$this->items();
    }
    return $this->list[$pos];
  }
  
  function items()
  {
    global $_STORAGE;
    
    $list=array();
    $set=$_STORAGE->query('SELECT page,sortkey FROM PageCategory WHERE category='.$this->id.' ORDER BY sortkey;');
    while ($set->valid())
    {
      $details = $set->current();
      $page=Resource::decodeResource($details['page']);
      if ($page!==null)
      {
        $list[$details['sortkey']]=$page;
      }
      else
      {
        $this->log->warn("Removing missing page from category ".$details['page']);
        $this->remove($details['sortkey']);
      }
      $set->next();
    }
    $set=$_STORAGE->query('SELECT id,name,sortkey FROM Category WHERE parent='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $cat = ObjectCache::getItem('category', $details['id']);
      if ($cat===null)
      {
        $cat = new Category($this->container,$this,$details['id'],$details['name']);
        ObjectCache::setItem('category', $details['id'], $cat);
      }
      $list[$details['sortkey']]=$cat;
      $set->next();
    }
    $set=$_STORAGE->query('SELECT name,link,sortkey FROM LinkCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $list[$details['sortkey']] = new Link($details['name'],$details['link']);
      $set->next();
    }
    ksort($list);
    return $list;
  }
  
  function getDefaultItem()
  {
    $items = $this->items();
    foreach($items as $item)
    {
      if (($item instanceof Page)||($item instanceof Link))
      {
        return $item;
      }
    }
    foreach($items as $item)
    {
      if ($item instanceof Category)
      {
        $page=$item->getDefaultItem();
        if ($page!==null)
          return $page;
      }
    }
    return null;
  }
}

class CategoryTree
{
  var $root;
  var $padding = '  ';
  var $showRoot = true;
  
  function CategoryTree($root)
  {
    $this->root=$root;
  }
  
  function displayCategoryContentStartTag($category,$indent)
  {
    print("\n".$indent."<ul>\n");
  }
  
  function displayCategoryContentEndTag($category,$indent)
  {
    print($indent."</ul>\n");
  }
  
  function displayCategoryContent($category,$indent)
  {
    $items = $category->items();
    if (count($items)>0)
    {
      $this->displayCategoryContentStartTag($category,$indent);
      $ni=$indent.$this->padding;
      foreach ($items as $item)
      {
        $this->displayItem($item,$ni);
      }
      $this->displayCategoryContentEndTag($category,$indent);
    }
  }
  
  function displayCategoryLabel($category)
  {
    print(htmlspecialchars($category->name));
  }
  
  function displayPageLabel($page)
  {
    print(htmlspecialchars($page->prefs->getPref('page.variables.title')));
  }
  
  function displayLinkLabel($link)
  {
    print(htmlspecialchars($link->name));
  }
  
  function displayItemStartTag($item,$indent)
  {
    if ($item instanceof Category)
    {
      print($indent.'<li class="category">');
    }
    else if ($item instanceof Page)
    {
      print($indent.'<li class="page">');
    }
    else
    {
      print($indent.'<li class="link">');
    }
  }
  
  function displayItemEndTag($item)
  {
    print("</li>\n");
  }
  
  function displayItem($item,$indent)
  {
    $this->displayItemStartTag($item,$indent);
    if ($item instanceof Category)
    {
      $this->displayCategoryLabel($item);
      $this->displayCategoryContent($item,$indent.$this->padding);
    }
    else if ($item instanceof Page)
    {
      $this->displayPageLabel($item);
    }
    else if ($item instanceof Link)
    {
      $this->displayLinkLabel($item);
    }
    $this->displayItemEndTag($item,$indent);
  }
  
  function display($indent='')
  {
    if ($this->showRoot)
    {
      $this->displayItem($this->root,$indent);
    }
    else
    {
      $this->displayCategoryContent($this->root,$indent);
    }
  }
}

class PageTree extends CategoryTree
{
  var $pages;
  
  function PageTree($root)
  {
    $this->CategoryTree($root);
  }
  
  function displayItem($item,$indent)
  {
    if ($item instanceof Page)
    {
      unset($this->pages[$item->getPath()]);
    }
    parent::displayItem($item,$indent);
  }
  
  function display($indent='')
  {
    $container = $this->root->container;
    $list=$container->getResources('page');
    $this->pages=array();
    foreach ($list as &$page)
    {
      $this->pages[$page->getPath()]=$page->prefs->getPref('page.variables.title','');
    }
    asort($this->pages);
    parent::display($indent);
    if (count($this->pages)>0)
    {
      print($indent.'<li class="category">Uncategorised pages'."\n");
      print($indent.$this->padding.'<ul>');
      foreach (array_keys($this->pages) as $path)
      {
        $page=Resource::decodeResource($path);
        print($indent.$this->padding.$this->padding.'<li class="page">');
        $this->displayPageLabel($page);
        print("</li>\n");
      }
      print($indent.$this->padding."</ul>\n");
      print($indent."</li>\n");
    }
  }
}

class YahooPageTree extends CategoryTree
{
  var $id;
  var $categorys = array();
  var $pages;
  
  function YahooPageTree($id, $root)
  {
    $this->CategoryTree($root);
    $this->id = $id;
  }
  
  function getItemLabel($item)
  {
    if ($item instanceof Category)
    {
      return $item->name;
    }
    else if ($item instanceof Page)
    {
      return $item->prefs->getPref('page.variables.title');
    }
    else if ($item instanceof Link)
    {
      return $item->name;
    }
  }
  
  function getItemIconClass($item)
  {
    if ($item instanceof Category)
    {
      return "category";
    }
    else if ($item instanceof Page)
    {
      return "page";
    }
    else if ($item instanceof Link)
    {
      return "link";
    }
    return "";
  }
  
  function getItemLabelClass($item)
  {
    return "";
  }
  
  function getItemLink($item)
  {
    return false;
  }
  
  function displayCategoryContentStartTag($category,$indent)
  {
  }
  
  function displayCategoryContentEndTag($category,$indent)
  {
  }
  
  function displayItem($item,$indent)
  {
    if ($item instanceof Page)
    {
      unset($this->pages[$item->getPath()]);
    }

    $label = $this->getItemLabel($item);
    $icon = $this->getItemIconClass($item);
    $style = $this->getItemLabelClass($item);
    $link = $this->getItemLink($item);
    $data = "{ label: '".$label."'";
    if ($link !== false)
      $data.=", href: '".$link."'";
    if ($icon != false)
      $data.=", iconClass: '".$icon."'";
    if ($style != false)
      $data.=", labelClass: '".$style."'";
    $data.=" }";

    $node = $this->categorys[count($this->categorys)-1];
    if ($item instanceof Category)
    {
      $newnode = "cat".$item->id;
      print("  var ".$newnode." = new BlueprintIT.widget.StyledTextNode(".$data.", ".$node.", true);\n");
      array_push($this->categorys, $newnode);
      $this->displayCategoryContent($item,$indent.$this->padding);
      array_pop($this->categorys);
    }
    else
    {
      print("  new BlueprintIT.widget.StyledTextNode(".$data.", ".$node.", false);\n");
    }
  }
  
  function display($indent='')
  {
    $container = $this->root->container;
    $list=$container->getResources('page');
    $this->pages=array();
    foreach ($list as &$page)
    {
      $this->pages[$page->getPath()]=$page->prefs->getPref('page.variables.title','');
    }
    asort($this->pages);
?>
<script type="text/javascript">
function display_<?= $this->id ?>_tree(event) {
  var tree = new YAHOO.widget.TreeView("<?= $this->id ?>");
  var root = tree.getRoot();
<?
    array_push($this->categorys, "root");
    parent::display($indent);
    if (count($this->pages)>0)
    {
      print("  var unused = new BlueprintIT.widget.StyledTextNode('Uncategorised pages', root, true);\n");
      array_push($this->containers, "unused");
      foreach (array_keys($this->pages) as $path)
      {
        $page=Resource::decodeResource($path);
        print($indent.$this->padding.$this->padding.'<li class="page">');
        $this->displayItem($page);
      }
    }
?>
  tree.draw();
}

YAHOO.util.Event.addListener(window, "load", display_<?= $this->id ?>_tree);
</script>
<?
  }
}

?>
