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
  private $log;
  private $id;
  private $section;
  private $variants = array();
  
  private function __construct($details)
  {
    $this->log = LoggerManager::getLogger('swim.itemversion');
    
    $this->id = $details['id'];
    $this->section = $details['section'];
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getSection()
  {
    return $this->section;
  }
  
  public function getCurrentVersion($variant)
  {
    $v = $this->getVariant($variant);
    if ($v != null)
    {
      $r = $v->getCurrentVersion();
      if ($r != null)
        return $r;
    }
    $v = $this->getVariant('default');
    if ($v != null)
      return $v->getCurrentVersion();
    return null;
  }
  
  public function getVariant($variant)
  {
    global $_STORAGE;
    
    if (isset($this->variants[$variant]))
      return $this->variants[$variant];
    
    $result = $_STORAGE->query('SELECT * FROM ItemVariant WHERE item='.$this->id.' AND variant="'.$_STORAGE->escape($variant).'";');
    if ($result->valid())
      $this->variants[$variant] = new ItemVariant($result->fetch());
    else
      $this->variants[$variant] = null;
    return $this->variants[$variant];
  }
  
  public static function getItem($id)
  {
    global $_STORAGE;
    
    $item = ObjectCache::getItem('dbitem', $id);
    if ($item === null)
    {
      $result = $_STORAGE->query('SELECT * FROM Item WHERE id='.$id.';');
      if ($result->valid())
        $item = new Item($result->fetch());
      else
        $item = null;
      ObjectCache::setItem('dbitem', $id, $item);
    }
    return $item;
  }
}

class ItemVariant
{
  private $id;
  private $variant;
  private $item;
  private $versions = array();
  
  public function __construct($details)
  {
    $this->item = $details['item'];
    $this->variant = $details['variant'];
    $this->id = $details['id'];
  }

  public function getItem()
  {
    return Item::getItem($this->item);
  }
  
  public function getVariant()
  {
    return $this->variant;
  }
  
  public function getCurrentVersion()
  {
    global $_STORAGE;
    
    if (isset($this->current))
      return $this->current;
      
    $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE itemvariant='.$this->id.' AND current=1;');
    if ($results->valid())
    {
      $version = new ItemVersion($results->fetch(), $this);
      $this->current = $version;
      $this->versions[$version->getVersion()] = $version;
    }
    return $this->current;
  }
  
  public function getVersion($version)
  {
    global $_STORAGE;
    
    if (isset($this->versions[$version]))
      return $this->versions[$version];
      
    $results = $_STORAGE->query('SELECT * FROM VersionVariant WHERE itemvariant='.$this->id.' AND version='.$version.';');
    if ($results->valid())
    {
      $version = new ItemVersion($results->fetch(), $this);
      $this->versions[$version] = $version;
      if ($version->isCurrent())
        $this->current = $version;
    }
    return $this->versions[$version];
  }
}

class ItemVersion
{
  private $log;
  private $id;
  private $version;
  private $variant;
  private $itemclass;
  private $owner;
  private $modified;
  private $complete;
  private $current;
  private $fields = array();
  
  public function __construct($details, $variant = null)
  {
    $this->log = LoggerManager::getLogger('swim.itemversion');
    
    $this->id = $details['id'];
    if ($variant != null)
      $this->variant = $variant;
    else
      $this->variant = $details['itemvariant'];
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
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getItem()
  {
    return $this->getVariant()->getItem();
  }
  
  public function getVariant()
  {
    if ($this->variant instanceof ItemVariant)
      return $this->variant;
    else
    {
      $this->variant = null;
      $results = $_STORAGE->query('SELECT item,variant FROM ItemVariant WHERE id='.$this->variant.';');
      if ($results->valid())
      {
        $details = $result->fetch();
        $item = Item::getItem($details['item']);
        if ($item != null)
          $this->variant = $item->getVariant($details['variant']);
      }
      return $this->variant;
    }
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
    $_STORAGE->queryExec('UPDATE VariantVersion SET owner="'.$_STORAGE->escape($this->owner).'", modified='.$this->modified.' WHERE id='.$this->getId().';');
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
    $_STORAGE->queryExec('UPDATE VariantVersion SET complete='.$bit.', modified='.$this->modified.' WHERE id='.$this->getId().';');
  }
  
  public function isCurrent()
  {
    return $this->current;
  }
  
  public function makeCurrent()
  {
    if ($this->current)
      return;
      
    $_STORAGE->queryExec('UPDATE VariantVersion SET current=0 WHERE current=1 AND itemvariant="'.$this->getVariant()->getId().'";');
    $_STORAGE->queryExec('UPDATE VariantVersion SET current=1 WHERE id='.$this->getId().';');
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
    $_STORAGE->queryExec('UPDATE VariantVersion SET class="'.$_STORAGE->escape($value->getId()).'", modified='.$this->modified.' WHERE id='.$this->getId().';');
  }
  
  public function getMainSequence()
  {
    if ($this->itemclass == null)
      return null;
      
    return $this->itemclass->getMainSequence($this);
  }
  
  public function getField($name)
  {
    if ($this->itemclass == null)
      return null;
      
    return $this->itemclass->getField($this, $name);
  }
}

?>