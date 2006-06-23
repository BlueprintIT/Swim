<?

/*
 * Swim
 *
 * The basic database item
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Item
{
  private $id;
  private $versions = array();
  private $current = array();
  
  private function __construct($id)
  {
    $this->id = $id;
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getCurrentVersion($variant)
  {
    global $_STORAGE;
    
    if ($this->current != null)
      return $this->current;
      
    $results = $_STORAGE->query('SELECT * FROM Item WHERE item='.$this->id.' AND variant="'.$_STORAGE->escape($variant).'" AND current=1;');
    if ($results->valid())
    {
      $version = new ItemVersion($results->fetch());
      $this->current[$variant] = $version;
      $this->versions[$variant][$version->getVersion()] = $version;
    }
    return $this->current;
  }
  
  public function getVersion($variant, $version)
  {
    global $_STORAGE;
    
    if ($this->versions[$version] != null)
      return $this->versions[$version];
      
    $results = $_STORAGE->query('SELECT * FROM Item WHERE item='.$this->id.' AND version='.$version.' AND variant="'.$_STORAGE->escape($variant).'";');
    if ($results->valid())
    {
      $version = new ItemVersion($results->fetch());
      $this->versions[$variant][$version] = $version;
      if ($version->isCurrent())
        $this->current[$variant] = $version;
    }
    return $this->versions[$version];
  }
  
  public static function getItem($id)
  {
    $result = ObjectCache::getItem('dbitem', $id);
    if ($result != null)
    {
      $result = new Item($id);
      ObjectCache::setItem('dbitem', $id);
    }
    return $result;
  }
}

class ItemVersion
{
  private $id;
  private $item;
  private $variant;
  private $version;
  private $itemclass;
  private $owner;
  private $modified;
  private $complete;
  private $current;
  private $fields = array();
  
  public function __construct($details)
  {
    $this->id = $details['id'];
    $this->item = Item::getItem($details['item']);
    $this->variant = $details['variant'];
    $this->version = $details['version'];
    $this->itemclass = ClassManager::getClass($details['class']);
    $this->modified = $details['modified'];
    $this->owner = $details['owner'];
    if ($details['complete']==1)
      $this->complete = true;
    else
      $this->complete = false;
    if ($details['current']==1)
      $this->current = true;
    else
      $this->current = false;
  }
  
  private function getId()
  {
    return $this->id;
  }
  
  public function getItem()
  {
    return $this->item;
  }
  
  public function getVariant()
  {
    return $this->variant;
  }
  
  public function getVersion()
  {
    return $this->version;
  }
  
  public function getModified()
  {
    return $this->modified;
  }
  
  public function getOwner()
  {
    return new User($this->owner);
  }
  
  public function setOwner($value)
  {
    if ($this->complete)
      return;
      
    if ($value->getUsername() == $this->owner)
      return;
      
    $this->owner = $value->getUsername();
    $this->modified = time();
    $_STORAGE->queryExec('UPDATE Item SET owner="'.$_STORAGE->escape($this->owner).'", modified='.$this->modified.' WHERE id='.$this->getId().';');
  }
  
  public function isComplete()
  {
    return $this->complete;
  }
  
  public function setComplete($value)
  {
    if ($this->complete == $value)
      return;
      
    $this->complete = $value;
    if ($value)
      $bit = '1';
    else
      $bit = 'NULL';

    $this->modified = time();
    $_STORAGE->queryExec('UPDATE Item SET complete='.$bit.', modified='.$this->modified.' WHERE id='.$this->getId().';');
  }
  
  public function isCurrent()
  {
    return $this->current;
  }
  
  public function makeCurrent()
  {
    if ($this->current)
      return;
      
    $_STORAGE->queryExec('UPDATE Item SET current=NULL WHERE current=1 AND item='.$this->item->getId().' AND variant="'.$this->variant.'";');
    $_STORAGE->queryExec('UPDATE Item SET current=1 WHERE id='.$this->getId().';');
  }
  
  public function getClass()
  {
    return $this->itemclass;
  }
  
  public function setClass($value)
  {
    if ($this->complete)
      return;
      
    if ($this->itemclass->getId() != $value->getId())

    $this->itemclass = $value;
    $this->modified = time();
    $_STORAGE->queryExec('UPDATE Item SET class="'.$_STORAGE->escape($value->getId()).'", modified='.$this->modified.' WHERE id='.$this->getId().';');
  }
  
  public function getField($name)
  {
    if ($this->itemclass == null)
      return null;
      
    return $this->itemclass->getField($this, $name);
  }
}

?>