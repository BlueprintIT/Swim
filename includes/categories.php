<?

/*
 * Swim
 *
 * The category handling functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class CategoryManager
{
  var $log;
  var $namespace;
  var $root;
  var $cache;
  
  function CategoryManager($namespace)
  {
    global $_STORAGE;
    
    $this->cache=array();
    $this->namespace=$namespace;
    $set=$_STORAGE->query('SELECT id,Category.name FROM Namespace,Category WHERE id=rootcategory;');
    $details = $set->current();
    $this->root = new Category($this,$this,$details['id'],$details['Category.name']);
    $this->cache[$this->root->id]=&$this->root;
  }
  
  function &getRootCategory()
  {
    return $this->root;
  }
  
  function &getCategory($id)
  {
    global $_STORAGE;
    
    if (!isset($this->cache[$id]))
    {
      $set=$_STORAGE->query('SELECT id,parent,name FROM Category WHERE id='.$id.';');
      if ($set->valid())
      {
        $details = $set->current();
        $this->cache[$id] = new Category($this,$this->getCategory($details['parent']),$details['id'],$details['name']);
      }
      else
      {
        $this->cache[$id] = false;
      }
    }
    return $this->cache[$id];
  }
}

class Category
{
  var $parent;
  var $name;
  var $id;
  var $manager;
  
  function Category(&$manager,&$parent,$id,$name)
  {
    $this->parent=&$parent;
    $this->manager=&$manager;
    $this->id=$id;
    $this->name=$name;
  }
  
  function count()
  {
    global $_STORAGE;
    
    $count=$_STORAGE->singleQuery('SELECT COUNT() FROM Category WHERE parent='.$this->id.';');
    $count+=$_STORAGE->singleQuery('SELECT COUNT() FROM LinkCategory WHERE category='.$this->id.';');
    $count+=$_STORAGE->singleQuery('SELECT COUNT() FROM PageCategory WHERE category='.$this->id.';');
  }
  
  function &item($pos)
  {
    $id=$_STORAGE->singleQuery('SELECT COUNT() FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
    if ($id!==false)
    {
      return $this->manager->getCategory($id);
    }
    $id=$_STORAGE->singleQuery('SELECT page FROM PageCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    if ($id!==false)
    {
      return Resource::decodeResource($id);
    }
    $id=$_STORAGE->singleQuery('SELECT link FROM LinkCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    if ($id!==false)
    {
      return $id;
    }
  }
  
  function &items()
  {
    global $_STORAGE;
    
    $items=array();
    $set=$_STORAGE->query('SELECT id,name,sortkey FROM Category WHERE parent='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      if (isset($this->manager->cache[$details['id']]))
      {
        $this->manager->cache[$details['id']] = new Category($this->manager,$this,$details['id'],$details['name']);
      }
      $items[$details['sortkey']]=&$this->manager->cache[$details['id']];
    }
    $set=$_STORAGE->query('SELECT page,sortkey FROM PageCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $items[$details['sortkey']]=&Resource::decodeResource($details['page']);
    }
    $set=$_STORAGE->query('SELECT link,sortkey FROM LinkCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $items[$details['sortkey']]=$details['link'];
    }
  }
}

class CategoryTree
{
  var $root;
  var $padding = '  ';
  var $showRoot = true;
  
  function CategoryTree(&$root)
  {
    $this->root=$root;
  }
  
  function displayCategoryContent(&$category,$indent)
  {
      $items = $category->items();
      if (count($items)>0)
      {
        print("\n".$indent."<ul>\n");
        $ni=$indent.$this->padding;
        foreach (array_keys($items) as $i)
        {
          $this->displayItem($items[$i],$ni);
        }
        print($indent."</ul>\n");
      }
  }
  
  function displayCategoryLabel(&$category)
  {
    print($category->name);
  }
  
  function displayPageLabel(&$page)
  {
    print($page->prefs->getPref('page.variables.title'));
  }
  
  function displayLinkLabel($link)
  {
    print($link);
  }
  
  function displayItem(&$item,$indent)
  {
    if (is_a($item,'Category'))
    {
      print($indent.'<li class="category">');
      $this->displayCategoryLabel($item);
      $this->displayCategoryContent($item,$indent.$this->padding);
      print("</li>\n");
    }
    else if (is_a($item,'Page'))
    {
      print($indent.'<li class="page">');
      $this->displayPageLabel($item);
      print("</li>\n");
    }
    else
    {
      print($indent.'<li class="link">');
      $this->displayLinkLabel($item);
      print("</li>\n");
    }
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

function &getCategoryManager($space)
{
  global $_CATMAN;
  
  if (!isset($_CATMAN[$space]))
  {
    $_CATMAN[$space] = new CategoryManager($space);
  }
  return $_CATMAN[$space];
}

$_CATMAN = array();

?>
