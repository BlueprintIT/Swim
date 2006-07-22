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
  
  public function compare($a, $b)
  {
    $a = $a->getCurrentVersion(Session::getCurrentVariant());
    $b = $b->getCurrentVersion(Session::getCurrentVariant());
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
  
  public static function sortItems($items, $field)
  {
    $sorter = new ItemSorter($field);
    usort($items, array($sorter, 'compare'));
  }
}

class Sequence extends ClassField
{
  protected $classes;  
  protected $sortfield;
  
  public function __construct($metadata)
  {
    parent::__construct($metadata);
  }
  
  protected function parseAttributes($element)
  {
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
  
  public function isSorted()
  {
    return isset($this->sortfield);
  }
  
  private function internalGetItems()
  {
    global $_STORAGE;
    
    $items = array();
    $results = $_STORAGE->query('SELECT position,item FROM Sequence WHERE parent='.$this->item->getId().' AND field="'.$_STORAGE->escape($this->id).'" ORDER BY position;');
    while ($results->valid())
    {
      $details = $results->fetch();
      $items[$details['position']] = Item::getItem($details['item']);
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