<?

/*
 * Swim
 *
 * The basic database sequence
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class ItemSorter
{
  private $field;
  private $ascending;
  
  public function __construct($field, $ascending = true)
  {
    $this->field = $field;
    $this->ascending = $ascending;
  }
  
  public function getItemVersion($i)
  {
    if ($i instanceof Item)
      return $i->getCurrentVersion(Session::getCurrentVariant());
    else if ($i instanceof ItemVersion)
      return $i;
    else if ($i instanceof ItemWrapper)
      return $i->item;
    return null;
  }
  
  public function compare($a, $b)
  {
    if ($a instanceof ItemWrapper)
      
    $a = $this->getItemVersion($a);
    $b = $this->getItemVersion($b);
    if ($a != null)
      $a = $a->getField($this->field);
    if ($b != null)
      $b = $b->getField($this->field);
    if (($b == null) && ($a == null))
      $result = 0;
    else if ($b == null)
      $result = -1;
    else if ($a == null)
      $result = 1;
    else
      $result = $a->compareTo($b);

    if (!$this->ascending)
      $result = -$result;
    return $result;
  }
  
  public static function sortItems($items, $field, $ascending = true)
  {
    $sorter = new ItemSorter($field, $ascending);
    usort($items, array($sorter, 'compare'));
    return $items;
  }
}

class Sequence extends ClassField
{
  protected $classes;  
  protected $sortfield;
  protected $relationship = 'aggregation';
  protected $allowposts = false;
  protected $postpublished = true;
  
  public function __construct($metadata)
  {
    parent::__construct($metadata);
  }
  
  public function onArchivedChanged($archived)
  {
    global $_STORAGE;
    
    if ($this->relationship == 'aggregation')
    {
      if ($archived)
        $_STORAGE->query('DELETE FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    }
    else if ($this->relationship == 'composition')
    {
      $items = $this->getItems();
      foreach ($items as $item)
        $item->setArchived($archived);
    }
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('relationship'))
    {
      switch ($element->getAttribute('relationship'))
      {
        case 'composition':
          $this->relationship = 'composition';
          break;
        default:
          $this->relationship = 'aggregation';
      }
    }
    if ($element->hasAttribute('allowpost'))
    {
    	switch ($element->getAttribute('allowpost'))
    	{
    		case 'true':
    			$this->allowposts = true;
    			break;
    		case 'moderated':
    			$this->allowposts = true;
    			$this->postpublished = false;
    			break;
    	}
    }
    parent::parseAttributes($element);
  }
  
  protected function parseElement($el)
  {
    if ($el->tagName == 'classes')
    {
      $items = explode(',', getDOMText($el));
      $this->classes = array();
      foreach ($items as $name)
      {
        $class = FieldSetManager::getClass($name);
        if ($class !== null)
          $this->classes[$name] = $class;
      }
    }
    else
      parent::parseElement($el);
  }
  
  public function getClassForMimetype($mimetype)
  {
    list($major,$minor) = explode('/', $mimetype);
    
    $classes = $this->getVisibleClasses();
    foreach ($classes as $class)
    {
      if ($class->getType() == 'file')
      {
        $types = $class->getMimetypes();
        foreach ($types as $type)
        {
          if ($type == '*')
            return $class;
            
          list($maj,$min) = explode('/', $type);
          if (($maj == $major) && ((!isset($min)) || ($min == $minor)))
            return $class;
        }
      }
    }
    return null;
  }
  
  public function getVisibleClasses()
  {
    if (!isset($this->classes))
      return $this->item->getSection()->getVisibleClasses();
      
    $main = $this->item->getMainSequence();
    if (($main !== null) && ($main->getId() === $this->getId()))
    {
      $sectlist = $this->item->getSection()->getVisibleClasses();
      return array_intersect($this->classes, $sectlist);
    }
    
    return $this->classes;
  }
  
  public function allowPosts()
  {
  	return $this->allowposts;
  }
  
  public function postPublished()
  {
  	return $this->postpublished;
  }
  
  public function isSorted()
  {
    return isset($this->sortfield);
  }
  
  private function internalGetItems()
  {
    global $_STORAGE;
    
    $items = array();
    $results = $_STORAGE->query('SELECT position,Item.* FROM Sequence JOIN Item ON Sequence.item=Item.id WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'" ORDER BY position;');
    while ($results->valid())
    {
      $details = $results->fetch();
      $items[$details['position']] = Item::getItem($details['id'], $details);
    }
    return $items;
  }
  
  public function getSortedItems($field)
  {
    $items = $this->internalGetItems();
    ItemSorter::sortItems($items, $field);
    return $items;
  }
  
  public function getItems()
  {
    if (isset($this->sortfield))
      return $this->getSortedItems($this->sortfield);
    return $this->internalGetItems();
  }
  
  public function getItem($index)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT item FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'" AND position='.$index.';');
    if ($results->valid())
      return Item::getItem($results->fetchSingle());
    return null;
  }
  
  public function indexOf($item)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT position FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'" AND item='.$item->getId().';');
    if ($results->valid())
      return $results->fetchSingle();
    else
      return -1;
  }
  
  public function count()
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT COUNT(position) FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    if ($results->valid())
      return $results->fetchSingle();
    else
      return 0;
  }
  
  public function appendItem($item)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT MAX(position+1) FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    if ($results->valid())
    {
      $pos = $results->fetchSingle();
      if ($pos == false)
        $pos = '0';
    }
    else
      $pos = '0';
    $_STORAGE->queryExec('INSERT INTO Sequence (parent,field,position,item) VALUES ('.$this->item->getId().',"'.$_STORAGE->escape($this->id).'",'.$pos.','.$item->getId().');');
    $this->items = null;
  }
  
  public function insertItem($pos, $item)
  {
    global $_STORAGE;
    
    if ($pos === null)
      $this->appendItem($item);
    else
    {
      $results = $_STORAGE->query('SELECT position FROM Sequence WHERE parent='.$this->item->getId().' AND position>='.$pos.' AND field="'.$_STORAGE->escape($this->id).'" ORDER BY position DESC;');
      while ($results->valid())
        $_STORAGE->queryExec('UPDATE Sequence SET position=position+1 WHERE parent='.$this->item->getId().' AND position='.$results->fetchSingle().' AND field="'.$_STORAGE->escape($this->id).'";');
      $_STORAGE->queryExec('INSERT INTO Sequence (parent,field,position,item) VALUES ('.$this->item->getId().',"'.$_STORAGE->escape($this->id).'",'.$pos.','.$item->getId().');');
      $this->items = null;
    }
  }
  
  public function removeItem($pos)
  {
    global $_STORAGE;
    
    $_STORAGE->queryExec('DELETE FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'" AND position='.$pos.';');
      $results = $_STORAGE->query('SELECT position FROM Sequence WHERE parent='.$this->item->getId().' AND position>'.$pos.' AND field="'.$_STORAGE->escape($this->id).'" ORDER BY position;');
      while ($results->valid())
        $_STORAGE->queryExec('UPDATE Sequence SET position=position-1 WHERE parent='.$this->item->getId().' AND position='.$results->fetchSingle().' AND field="'.$_STORAGE->escape($this->id).'";');
    $this->items = null;
  }
}

?>