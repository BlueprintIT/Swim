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
    $this->log = LoggerManager::getLogger('swim.categories.'.$namespace);
    $set=$_STORAGE->query('SELECT id,Category.name FROM Namespace,Category WHERE id=rootcategory;');
    $details = $set->current();
    $this->root = new Category($this,null,$details['id'],$details['Category.name']);
    $this->cache[$this->root->id]=$this->root;
  }
  
  function getRootCategory()
  {
    return $this->root;
  }
  
  function getReadyCategory($id,$parent,$name)
  {
    if (!isset($this->cache[$id]))
    {
      $this->cache[$id] = new Category($this,$this->getCategory($parent),$id,$name);
    }
    return $this->cache[$id];
  }
  
  function getCategory($id)
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
  
  function getPageCategories($page)
  {
    global $_STORAGE;

    $path="'".storage_escape($page->getPath())."'";
    $set=$_STORAGE->query('SELECT id,parent,name FROM Category JOIN PageCategory ON Category.id=PageCategory.category WHERE PageCategory.page='.$path.';');
    $results = array();
    while ($set->valid())
    {
      $details=$set->current();
      $results[]=$this->getReadyCategory($details['id'],$details['parent'],$details['name']);
      $this->log->debug('Found page '.$page->getPath().' in category '.$details['id']);
      $set->next();
    }
    return $results;
  }
  
  function getTextContent($element)
  {
    $text='';
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_TEXT_NODE)
      {
        $text.=$el->nodeValue;
      }
      $el=$el->nextSibling;
    }
    return $text;
  }
  
  function loadCategory($element, $category)
  {
    global $_STORAGE;
    
    $this->log->debug('Loading category '.$category->id);
    $pos=0;
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='link')
        {
          $this->log->debug('Adding link');
          $name="'".storage_escape($this->getTextContent($el))."'";
          $path="'".storage_escape($el->getAttribute('path'))."'";
          $this->log->debug('path => '.$path.' name => '.$name.' sortkey => '.$pos);
          $_STORAGE->queryExec('INSERT INTO LinkCategory (link,name,category,sortkey) VALUES ('.$path.','.$name.','.$category->id.','.$pos.');');
          $this->log->debug('Insert complete - '.$_STORAGE->changes());
          $pos++;
        }
        else if ($el->tagName=='page')
        {
          $this->log->debug('Adding page');
          $path="'".storage_escape($el->getAttribute('path'))."'";
          $this->log->debug('path => '.$path.' sortkey => '.$pos);
          $_STORAGE->queryExec('INSERT INTO PageCategory (page,category,sortkey) VALUES ('.$path.','.$category->id.','.$pos.');');
          $this->log->debug('Insert complete - '.$_STORAGE->changes());
          $pos++;
        }
        else if ($el->tagName=='category')
        {
          $this->log->debug('Adding category');
          if ($el->hasAttribute('id'))
          {
            $id=$el->getAttribute('id');
          }
          else
          {
            $id='NULL';
          }
          $name=$this->getTextContent($el);
          $ename="'".storage_escape($name)."'";
          $this->log->debug('id => '.$id.' name => '.$ename.' sortkey => '.$pos);
          $this->log->debug('INSERT INTO Category (id,name,parent,sortkey) VALUES ('.$id.','.$ename.','.$category->id.','.$pos.');');
          $_STORAGE->queryExec('INSERT INTO Category (id,name,parent,sortkey) VALUES ('.$id.','.$ename.','.$category->id.','.$pos.');');
          $this->log->debug('Insert complete - '.$_STORAGE->changes());
          if ($id=='NULL')
            $id=$_STORAGE->lastInsertRowid();
          
          $this->cache[$id] = new Category($this,$category,$id,$name);
          $this->loadCategory($el,$this->cache[$id]);
          $pos++;
        }
      }
      $el=$el->nextSibling;
    }
    $this->log->debug('Completed category '.$category->id);    
  }
  
  function load($document)
  {
    global $_STORAGE;
    $_STORAGE->queryExec('BEGIN TRANSACTION;');
    $this->log->debug('Wiping categories');
    $items = $this->root->clean();
    $this->cache=array();
    $this->cache[$this->root->id]=$this->root;
    $this->loadCategory($document->documentElement,$this->root);
    $_STORAGE->queryExec('COMMIT TRANSACTION;');
    $this->log->debug('Transaction committed - '.$_STORAGE->lastError());
  }
}

class Category
{
  var $parent;
  var $name;
  var $id;
  var $manager;
  
  function Category($manager,$parent,$id,$name)
  {
    $this->parent=$parent;
    $this->manager=$manager;
    $this->id=$id;
    $this->name=$name;
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
      $cat=$this->manager->getCategory($id);
      $cat->clean();
      $_STORAGE->queryExec('DELETE FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
      $_STORAGE->queryExec('UPDATE Category SET sortkey=sortkey-1 WHERE parent='.$this->id.' AND sortkey>'.$pos.';');
    }
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
      $cat=$this->manager->getReadyCategory($id[0],$id[1],$id[2]);
      $cat->clean();
      $result->next();
    }
    $_STORAGE->queryExec('DELETE FROM Category WHERE parent='.$this->id.';');
  }
  
  function count()
  {
    global $_STORAGE;
    
    $count=$_STORAGE->singleQuery('SELECT COUNT() FROM Category WHERE parent='.$this->id.';');
    $count+=$_STORAGE->singleQuery('SELECT COUNT() FROM LinkCategory WHERE category='.$this->id.';');
    $count+=$_STORAGE->singleQuery('SELECT COUNT() FROM PageCategory WHERE category='.$this->id.';');
  }
  
  function item($pos)
  {
    $id=$_STORAGE->singleQuery('SELECT id FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
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
  
  function items()
  {
    global $_STORAGE;
    
    $list=array();
    $set=$_STORAGE->query('SELECT id,name,sortkey FROM Category WHERE parent='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      if (!isset($this->manager->cache[$details['id']]))
      {
        $this->manager->cache[$details['id']] = new Category($this->manager,$this,$details['id'],$details['name']);
      }
      $list[$details['sortkey']]=$this->manager->cache[$details['id']];
      $set->next();
    }
    $set=$_STORAGE->query('SELECT page,sortkey FROM PageCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $list[$details['sortkey']]=Resource::decodeResource($details['page']);
      $set->next();
    }
    $set=$_STORAGE->query('SELECT link,sortkey FROM LinkCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->current();
      $list[$details['sortkey']]=$details['link'];
      $set->next();
    }
    ksort($list);
    return $list;
  }
  
  function getDefaultPage()
  {
    $items = $this->items();
    foreach($items as $item)
    {
      if ($item instanceof Page)
      {
        return $item;
      }
    }
    foreach($items as $item)
    {
      if ($item instanceof Category)
      {
        $page=$item->getDefaultPage();
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
    print(htmlspecialchars($link));
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
    else
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
    $container = getContainer('global');
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

function getCategoryManager($space)
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
