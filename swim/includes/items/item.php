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
    $this->log = LoggerManager::getLogger('swim.item');
    
    $this->id = $details['id'];
    $this->section = $details['section'];
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getSection()
  {
    return Section::getSection($this->section);
  }
  
  protected function getValidVariants($variant)
  {
    $valid = array();
    array_push($valid, $variant);
    if ($variant != 'default')
      array_push($valid, 'default');
    return $valid;
  }
  
  public function getVersions($variant)
  {
    $valid = $this->getValidVariants($variant);
    $v = $this->getVariant($valid[0]);
    return $v->getVersions();
  }
  
  public function getCurrentVersion($variant)
  {
    $valid = $this->getValidVariants($variant);
    foreach ($valid as $var)
    {
      $v = $this->getVariant($var);
      if ($v != null)
      {
        $r = $v->getCurrentVersion();
        if ($r != null)
          return $r;
      }
    }
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
  
  public function createVariant($variant)
  {
    global $_STORAGE;
    
    if (!isset($this->variants[$variant]))
      $this->getVariant($variant);
    
    if ($this->variants[$variant] !== null)
      return $this->variants[$variant];
    
    if ($_STORAGE->queryExec('INSERT INTO ItemVariant (item,variant) VALUES ('.$this->id.',"'.$_STORAGE->escape($variant).'");'))
    {
      $details = array();
      $details['id'] = $_STORAGE->lastInsertRowid();
      $details['item'] = $this->id;
      $details['variant'] = $variant;
      $v = new ItemVariant($details);
      $this->variants[$variant] = $v;
      return $v;
    }
    return null;
  }
  
  public static function createItem($section)
  {
    global $_STORAGE;
    
    if ($_STORAGE->queryExec('INSERT INTO Item (section) VALUES ("'.$section->getId().'");'))
    {
      $id = $_STORAGE->lastInsertRowid();
      $details = array('id' => $id, 'section' => $section->getId());
      $item = new Item($details);
      ObjectCache::setItem('dbitem', $id, $item);
      return $item;
    }
    return null;
  }
  
  public static function getItemVersion($id, $variant = null, $version = null)
  {
    global $_STORAGE;

    if (($variant == null) && ($version == null))
    {
      $results = $_STORAGE->query('SELECT item,variant,version FROM ItemVariant JOIN VariantVersion ON ItemVariant.id=VariantVersion.itemvariant WHERE VariantVersion.id='.$id.';');
      if (!$results->valid())
        return null;
      $details = $results->fetch();
      $id = $details['item'];
      $variant = $details['variant'];
      $version = $details['version'];
    }
    $item = Item::getItem($id);
    if ($item == null)
      return null;
    $v = $item->getVariant($variant);
    if ($v == null)
      return null;
    return $v->getVersion($version);
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
  private $log;
  private $id;
  private $variant;
  private $item;
  private $versions = array();
  private $complete = false;
  
  public function __construct($details)
  {
    $this->log = LoggerManager::getLogger('swim.itemvariant');
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
  
  public function getVersions()
  {
    global $_STORAGE;
    
    if ($this->complete)
      return $this->versions;
      
    $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE itemvariant='.$this->id.';');
    while ($results->valid())
    {
      $details = $results->fetch();
      if (!isset($this->versions[$details['version']]))
      {
        $version = new ItemVersion($details, $this);
        $this->versions[$details['version']] = $version;
        if ($version->isCurrent())
          $this->current = $version;
      }
    }
    $this->complete = true;
    return $this->versions;
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
      
    $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE itemvariant='.$this->id.' AND version='.$version.';');
    if ($results->valid())
    {
      $v = new ItemVersion($results->fetch(), $this);
      $this->versions[$version] = $v;
      if ($v->isCurrent())
        $this->current = $v;
    }
    return $this->versions[$version];
  }
  
  public function createNewVersion($clone = null)
  {
    global $_STORAGE,$_USER;

    $class = '';
    if ($clone != null)
      $class = $clone->getClass()->getId();
    $time = time();    
    if ($_STORAGE->queryExec('INSERT INTO VariantVersion (itemvariant,version,class,modified,owner,current,complete) ' .
      'SELECT itemvariant,MAX(version)+1,"'.$_STORAGE->escape($class).'",'.$time.',"'.$_USER->getUsername().'",0,0 FROM VariantVersion WHERE itemvariant='.$this->id.' GROUP BY itemvariant;'))
    {
      $id = $_STORAGE->lastInsertRowid();
      $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE id='.$id.';');
      $details = $results->fetch();
      $iv = new ItemVersion($details, $this);
      $this->versions[$iv->getVersion()] = $iv;
      
      if ($clone != null)
      {
        $_STORAGE->queryExec('INSERT INTO Field (itemversion,field,textValue,intValue,dateValue) ' .
          'SELECT '.$id.',field,textValue,intValue,dateValue FROM Field WHERE itemversion='.$clone->getId().';');
      }
      
      $fields = $iv->getFields();
      foreach ($fields as $name => $field)
      {
        if ($clone == null)
          $field->initialise();
        else
          $field->copyFrom($clone);
      }
      return $iv;
    }
    else
    {
      $this->log->error('Creation of new version in database failed.');
    }
    return null;
  }
}

class ItemVersion
{
  private $log;
  private $id;
  private $version;
  private $variant;
  private $variantid;
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
    $this->variantid = $details['itemvariant'];
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
    global $_STORAGE;
    
    if ($this->variant != null)
      return $this->variant;
    $results = $_STORAGE->query('SELECT item,variant FROM ItemVariant WHERE id='.$this->variantid.';');
    if ($results->valid())
    {
      $details = $result->fetch();
      $item = Item::getItem($details['item']);
      if ($item != null)
        $this->variant = $item->getVariant($details['variant']);
    }
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
    global $_STORAGE;
    
    if ($this->complete)
      return;
      
    if ($value->getUsername() == $this->owner)
      return;
      
    $newtime = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET owner="'.$_STORAGE->escape($value->getUsername()).'", modified='.$newtime.' WHERE id='.$this->getId().';'))
    {
      $this->owner = $value->getUsername();
      $this->modified = $newtime;
    }
  }
  
  public function isComplete()
  {
    return $this->complete;
  }
  
  public function setComplete($value)
  {
    global $_STORAGE;
    
    if ($this->complete == $value)
      return;
      
    if (!$value && $this->isCurrent())
    {
      if (!$_STORAGE->queryExec('UPDATE VariantVersion SET current=0 WHERE id='.$this->getId().';'))
        return;
      $this->current = false;
    }

    if ($value)
      $bit = '1';
    else
      $bit = 'NULL';

    $newtime = time();
    $this->modified = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET complete='.$bit.', modified='.$newtime.' WHERE id='.$this->getId().';'))
    {
      $this->modified = $newtime;
      $this->complete = $value;
    }
  }
  
  public function isCurrent()
  {
    return $this->current;
  }
  
  public function makeCurrent()
  {
    global $_STORAGE;
    
    if ($this->current)
      return;
      
    if (($_STORAGE->queryExec('UPDATE VariantVersion SET current=0 WHERE current=1 AND itemvariant="'.$this->variantid.'";'))
      && ($_STORAGE->queryExec('UPDATE VariantVersion SET current=1 WHERE id='.$this->getId().';')))
      $this->current = true;
  }
  
  public function getClass()
  {
    return $this->itemclass;
  }
  
  public function setClass($value)
  {
    global $_STORAGE;
    
    if ($this->complete)
      return;
      
    if ($this->itemclass->getId() == $value->getId())
      return;
      
    $newtime = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET class="'.$_STORAGE->escape($value->getId()).'", modified='.$newtime.' WHERE id='.$this->getId().';'))
    {
      $this->itemclass = $value;
      $this->modified = $newtime;
    }
  }
  
  public function getMainSequence()
  {
    if ($this->itemclass == null)
      return null;
      
    return $this->itemclass->getMainSequence($this);
  }
  
  public function getFields()
  {
    if ($this->itemclass == null)
      return null;
    
    return $this->itemclass->getFields($this);
  }
  
  public function getField($name)
  {
    if ($this->itemclass == null)
      return null;
      
    return $this->itemclass->getField($this, $name);
  }
  
  public function updateModified()
  {
    global $_STORAGE;
    
    if ($this->complete)
      return;
      
    $newtime = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET modified='.$newtime.' WHERE id='.$this->getId().';'))
      $this->modified = $newtime;
  }
}

?>