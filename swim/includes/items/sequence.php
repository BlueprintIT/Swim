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

class Sequence extends Field
{
  public function __construct($metadata, $item, $name)
  {
    parent::__construct($metadata, $item, $name);
    $this->exists = true;
  }
  
  public function getItems()
  {
    global $_STORAGE;
    
    $items = array();
    $results = $_STORAGE->query('SELECT position,item FROM Sequence WHERE parent='.$this->itemversion->getItem()->getId().' AND field="'.$_STORAGE->escape($this->id).'" ORDER BY position;');
    while ($results->valid())
    {
      $details = $results->fetch();
      $items[$details['position']] = Item::getItem($details['item']);
    }
    return $items;
  }
  
  public function indexOf($item)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT position FROM Sequence WHERE parent='.$this->itemversion->getItem()->getId().' AND field="'.$_STORAGE->escape($this->id).'" AND item='.$item->getId().';');
    if ($results->valid())
      return $results->fetchSingle();
    else
      return -1;
  }
  
  public function count()
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT COUNT(position) FROM Sequence WHERE parent='.$this->itemversion->getItem()->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    if ($results->valid())
      return $results->fetchSingle();
    else
      return 0;
  }
  
  public function appendItem($item)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT MAX(position) FROM Sequence WHERE parent='.$this->itemversion->getItem()->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    $pos = $results->fetchSingle()+1;
    $_STORAGE->queryExec('INSERT INTO Sequence (parent,field,position,item) VALUES ('.$this->itemversion->getItem()->getId().',"'.$_STORAGE->escape($this->id).'",'.$pos.','.$item->getId().');');
    $this->items = null;
  }
  
  public function insertItem($pos, $item)
  {
    global $_STORAGE;
    
    if ($pos == null)
      $this->appendItem($item);
    else
    {
      $_STORAGE->queryExec('UPDATE Sequence SET position=position+1 WHERE parent='.$this->itemversion->getItem()->getId().' AND position>='.$pos.' AND field="'.$_STORAGE->escape($this->id).'";');
      $_STORAGE->queryExec('INSERT INTO Sequence (parent,field,position,item) VALUES ('.$this->itemversion->getItem()->getId().',"'.$_STORAGE->escape($this->id).'",'.$pos.','.$item->getId().');');
      $this->items = null;
    }
  }
  
  public function removeItem($pos)
  {
    global $_STORAGE;
    
    $_STORAGE->queryExec('DELETE FROM Sequence WHERE parent='.$this->itemversion->getItem()->getId().' AND field="'.$_STORAGE->escape($this->id).'" AND position='.$pos.';');
    $_STORAGE->queryExec('UPDATE Sequence SET position=position+1 WHERE parent='.$this->itemversion->getItem()->getId().' AND position>'.$pos.' AND field="'.$_STORAGE->escape($this->id).'";');
    $this->items = null;
  }
}

?>