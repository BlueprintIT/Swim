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
	var $parent;
	var $id;
  var $name;
  var $address;
  var $newwindow = true;
  
  function Link($category,$id,$name,$address)
  {
  	$this->parent=$category;
  	$this->key=$key;
    $this->name=$name;
    $this->address=$address;
  }
  
  function save()
  {
  	global $_STORAGE;
  	
  	$_STORAGE->queryExec('UPDATE LinkCategory SET name=\''.$_STORAGE->escape($this->name).'\', link=\''.$_STORAGE->escape($this->address).'\', newwindow=\''.$this->newwindow.'\' WHERE id='.$this->id.';');
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
  var $icon;
  var $hovericon;
  
  function Category($container,$parent,$id,$name)
  {
    $this->parent=$parent;
    $this->container=$container;
    $this->id=$id;
    $this->name=$name;
    $this->log=$container->log;
  }
  
  function save()
  {
  	global $_STORAGE;
  	
  	if ($this->id !==null)
  	{
  		$_STORAGE->queryExec('UPDATE Category SET name=\''.$_STORAGE->escape($this->name).'\', parent='.$this->parent->id.', icon=\''.$_STORAGE->escape($this->icon).'\', hovericon=\''.$_STORAGE->escape($this->hovericon).'\' WHERE id='.$this->id.';');
  	}
  	else
  	{
  		$_STORAGE->queryExec('INSERT INTO Category (name, parent, icon, hovericon) VALUES (\''.$_STORAGE->escape($this->name).'\','.$this->parent->id.',\''.$_STORAGE->escape($this->icon).'\',\''.$_STORAGE->escape($this->hovericon).'\');');
  		$this->id = $_STORAGE->lastInsertRowid();
  		ObjectCache::setItem('category', $this->id, $cat);
  	}
  }
  
  function add($item)
  {
  	$this->insert($item, $this->count());
  }
  
  function shiftItems($cutoff, $amount)
  {
  	global $_STORAGE;
  	
  	$_STORAGE->beginTransaction();
  	
  	if ($amount>0)  // Shift up
  	{
	  	$order = 'DESC';
  		$chg = '+'.$amount;
  	}
  	else            // Shift down
  	{
	  	$order = 'ASC';
	  	$chg = $amount;
  	}
  		
  	$rows = $_STORAGE->arrayQuery('SELECT * FROM LinkCategory WHERE category='.$this->id.' AND sortkey>='.$cutoff.' ORDER BY sortkey '.$order.';');
  	foreach ($rows as $row)
  	{
  		$_STORAGE->queryExec('UPDATE LinkCategory SET sortkey=sortkey'.$chg.' WHERE sortkey='.$row['sortkey'].' AND category='.$this->id.';');
  	}

  	$rows = $_STORAGE->arrayQuery('SELECT * FROM PageCategory WHERE category='.$this->id.' AND sortkey>='.$cutoff.' ORDER BY sortkey '.$order.';');
  	foreach ($rows as $row)
  	{
	    $_STORAGE->queryExec('UPDATE PageCategory SET sortkey=sortkey'.$chg.' WHERE sortkey='.$row['sortkey'].' AND category='.$this->id.';');
		}
		
  	$rows = $_STORAGE->arrayQuery('SELECT * FROM Category WHERE parent='.$this->id.' AND sortkey>='.$cutoff.' ORDER BY sortkey '.$order.';');
  	foreach ($rows as $row)
  	{
	    $_STORAGE->queryExec('UPDATE Category SET sortkey=sortkey'.$chg.' WHERE sortkey='.$row['sortkey'].' AND parent='.$this->id.';');
	  }
	  
	  $_STORAGE->commitTransaction();
  }
  
  function insert($item, $npos)
  {
    global $_STORAGE;

		$_STORAGE->beginTransaction();

  	if (($item instanceof Category) || ($item instanceof Link))
  	{
  		if ($item->parent !==null)
  		{
	  		if ($item instanceof Category)
		  		$pos = $_STORAGE->singleQuery('SELECT sortkey FROM Category WHERE id='.$item->id.';');
		  	
		  	if ($item instanceof Link)
		  		$pos = $_STORAGE->singleQuery('SELECT sortkey FROM LinkCategory WHERE id='.$item->id.';');
	
	  		if ($item->parent !== $this)
	  		{
	  			$this->shiftItems($npos, 1);
	
		  		if ($item instanceof Category)
		  		{
			  		$_STORAGE->queryExec('UPDATE Category SET sortkey='.$npos.', parent='.$this->id.' WHERE id='.$item->id.';');
			  		$item->container=$this->container;
			  	}
			  	
			  	if ($item instanceof Link)
			  		$_STORAGE->queryExec('Update LinkCategory SET sortkey='.$npos.', category='.$this->id.' WHERE id='.$item->id.';');
			  		
			  	$item->parent->shiftItems($pos, -1);
			  	$item->parent=$this;
	  		}
	  		else
	  		{
	  			if ($pos<$npos)
	  				$npos++;
	  				
  				$this->shiftItems($npos, 1);
  				
		  		if ($item instanceof Category)
			  		$_STORAGE->queryExec('UPDATE Category SET sortkey='.$npos.' WHERE id='.$item->id.';');
			  	
			  	if ($item instanceof Link)
			  		$_STORAGE->queryExec('Update LinkCategory SET sortkey='.$npos.' WHERE id='.$item->id.';');

  				$this->shiftItems($pos+1, -1);
	  		}
	  	}
	  	else
	  	{
	  		$this->shiftItems($npos, 1);
		  	if ($item instanceof Category)
		  	{
		  		$item->parent=$this;
		  		$item->container=$this->container;
	        $_STORAGE->queryExec('INSERT INTO Category (name,parent,sortkey) VALUES ("'.$_STORAGE->escape($item->name).'",'.$this->id.','.$npos.');');
		  		$item->id = $_STORAGE->lastInsertRowid();
		  	}
		  	else if ($item instanceof Link)
		  	{
		  		$_STORAGE->queryExec('INSERT INTO LinkCategory (link,name,category,newwindow,sortkey) VALUES (\''.$_STORAGE->escape($item->address).'\',\''.$_STORAGE->escape($item->name).'\','.$this->id.','.$item->newwindow.','.$npos.');');
		  		$item->id = $_STORAGE->lastInsertRowid();
		  	}
	  	}
  	}
  	else if ($item instanceof Page)
  	{
  		$positions = $_STORAGE->arrayQuery('SELECT sortkey FROM PageCategory WHERE page=\''.$_STORAGE->escape($item->getPath()).'\' AND category='.$this->id.' ORDER BY sortkey DESC;');
  		foreach ($positions as $row)
  		{
		    $_STORAGE->queryExec('DELETE FROM PageCategory WHERE category='.$this->id.' AND sortkey='.$row['sortkey'].';');
		    $this->shiftItems($row['sortkey'], -1);
		    //if ($row['sortkey']<$npos)
		    //	$npos--;
  		}
  		$this->shiftItems($npos, 1);
  		$_STORAGE->queryExec('INSERT INTO PageCategory (page,category,sortkey) VALUES (\''.$_STORAGE->escape($item->getPath()).'\','.$this->id.','.$npos.');');
  	}
  	
  	$_STORAGE->commitTransaction();
  	
    if (isset($this->list))
      unset($this->list);
  }
  
  function delete()
  {
  	global $_STORAGE;
    $this->clean();
    $_STORAGE->queryExec('DELETE FROM Category WHERE id='.$this->id.';');
    // TODO clear from cache?
  }
  
  function remove($pos)
  {
    global $_STORAGE;
    
    $_STORAGE->beginTransaction();
    
    $_STORAGE->queryExec('DELETE FROM LinkCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    $_STORAGE->queryExec('DELETE FROM PageCategory WHERE category='.$this->id.' AND sortkey='.$pos.';');
    $id = $_STORAGE->singleQuery('SELECT id FROM Category WHERE parent='.$this->id.' AND sortkey='.$pos.';');
    if ($id!=false)
    {
      $cat=$this->container->getCategory($id);
      $cat->delete();
    }
    $this->shiftItems($pos,-1);

    $_STORAGE->commitTransaction();
    
    if (isset($this->list))
      unset($this->list);
  }
  
  function indexOf($item)
  {
  	global $_STORAGE;

  	if (($item instanceof Category)||($item instanceof Link))
  	{
  		if ($item->parent!==$this)
  			return false;
  	}
  	
  	if ($item instanceof Category)
  	{
  		$pos = $_STORAGE->singleQuery('SELECT sortkey from Category WHERE id='.$item->id.';');
  		if ($pos===null)
  			return false;
  		return $pos;
  	}
  	else if ($item instanceof Link)
  	{
  		$pos = $_STORAGE->singleQuery('SELECT sortkey from LinkCategory WHERE id='.$item->id.';');
  		if ($pos===null)
  			return false;
  		return $pos;
  	}
  	else if ($item instanceof Page)
  	{
  		$pos = $_STORAGE->singleQuery('SELECT sortkey from PageCategory WHERE page="'.$item->getPath().'" AND category='.$this->id.';');
  		if ($pos===null)
  			return false;
  		return $pos;
  	}
  	return false;
  }
  
  function clean()
  {
    global $_STORAGE;
    $_STORAGE->queryExec('DELETE FROM LinkCategory WHERE category='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM PageCategory WHERE category='.$this->id.';');
    
    $result = $_STORAGE->query('SELECT id,parent,name FROM Category WHERE parent='.$this->id.';');
    while ($result->valid())
    {
      $id=$result->fetch();
      $cat=$this->container->getReadyCategory($id[0],$id[1],$id[2]);
      $cat->clean();
    }
    $_STORAGE->queryExec('DELETE FROM Category WHERE parent='.$this->id.';');
  }
  
  function count()
  {
    if (!isset($this->list))
    {
      $this->items();
    }
    return count($this->list);
  }
  
  function item($pos)
  {
    if (!isset($this->list))
    {
      $this->items();
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
      $details = $set->fetch();
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
    }
    $set=$_STORAGE->query('SELECT id,name,sortkey,icon,hovericon FROM Category WHERE parent='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->fetch();
      $cat = ObjectCache::getItem('category', $details['id']);
      if ($cat===null)
      {
        $cat = new Category($this->container,$this,$details['id'],$details['name']);
        $cat->icon = $details['icon'];
        if ($details['hovericon']===null)
        {
	        $cat->hovericon = $details['icon'];
        }
        else
        {
	        $cat->hovericon = $details['hovericon'];
        }
        ObjectCache::setItem('category', $details['id'], $cat);
      }
      $list[$details['sortkey']]=$cat;
    }
    $set=$_STORAGE->query('SELECT id,name,link,newwindow,sortkey FROM LinkCategory WHERE category='.$this->id.';');
    while ($set->valid())
    {
      $details = $set->fetch();
      $link = ObjectCache::getItem('link', $details['id']);
      if ($line === null)
      {
	      $list[$details['sortkey']] = new Link($this, $details['id'], $details['name'],$details['link']);
	      $list[$details['sortkey']]->newwindow = $details['newwindow'];
	      ObjectCache::setItem('link', $list[$details['sortkey']]);
	    }
	    else
	    {
	    	$list[$details['sortkey']] = $link;
	    }
    }
    ksort($list);
    $this->list=$list;
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
  
  function getItemTarget($item)
  {
  	return false;
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
    $target = $this->getItemTarget($item);
    $data = "{ label: '".$label."'";
    if ($link !== false)
      $data.=", href: '".$link."'";
    if ($icon != false)
      $data.=", iconClass: '".$icon."'";
    if ($target != false)
      $data.=", target: '".$target."'";
    if ($style != false)
      $data.=", labelClass: '".$style."'";
    $data.=" }";

    $node = $this->categorys[count($this->categorys)-1];
    if ($item instanceof Category)
    {
      $newnode = "cat".$item->id;
      if ($item->count()>0)
      {
	      print("  var ".$newnode." = new BlueprintIT.widget.StyledTextNode(".$data.", ".$node.", true);\n");
	    }
	    else
	    {
	      print("  var ".$newnode." = new BlueprintIT.widget.StyledTextNode(".$data.", ".$node.", false);\n");
	    }
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
    array_pop($this->categorys);
    if (count($this->pages)>0)
    {
      print("  var unused = new BlueprintIT.widget.StyledTextNode('Uncategorised pages', root, true);\n");
      array_push($this->categorys, "unused");
      foreach (array_keys($this->pages) as $path)
      {
        $page=Resource::decodeResource($path);
        $this->displayItem($page,$indent);
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
