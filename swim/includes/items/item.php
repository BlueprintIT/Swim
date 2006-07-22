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
  private $itemclass;
  private $variants = array();
  private $fields = array();
  
  private function __construct($details)
  {
    $this->log = LoggerManager::getLogger('swim.item');
    
    $this->id = $details['id'];
    $this->section = $details['section'];
    $this->itemclass = FieldSetManager::getClass($details['class']);
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getSection()
  {
    return SectionManager::getSection($this->section);
  }
  
  public function getClass()
  {
    return $this->itemclass;
  }
  
  public function getParents()
  {
    global $_STORAGE;
    
    $parents = array();
    $results = $_STORAGE->query('SELECT parent,field FROM Sequence WHERE item='.$this->id.';');
    while ($results->valid())
    {
      $details = $results->fetch();
      $item = Item::getItem($details['parent']);
      array_push($parents, array('item' => $item, 'field' => $details['field']));
    }
    return $parents;
  }
  
  public function getMainParents()
  {
    global $_STORAGE;
    
    $parents = array();
    $results = $_STORAGE->query('SELECT parent,field FROM Sequence WHERE item='.$this->id.';');
    while ($results->valid())
    {
      $details = $results->fetch();
      $item = Item::getItem($details['parent']);
      $item = $item->getCurrentVersion(Session::getCurrentVariant());
      if (($item !== null) && ($item->getMainSequence()->getId() == $details['field']))
        array_push($parents, $item);
    }
    return $parents;
  }
  
  public function getField($name, $item = null)
  {
    if (isset($this->fields[$name]))
      return $this->fields[$name];
    if ($item === null)
      $item = $this;
    $field = $this->itemclass->getField($item, $name);
    if (($field !== null) && ($field instanceof ClassField))
      $this->fields[$name] = $field;
    return $field;
  }
  
  public function getSequence($name)
  {
    if ($this->itemclass->getFieldType($name) == 'sequence')
      return $this->getField($name);
    return null;
  }
  
  public function getMainSequence()
  {
    $name = $this->itemclass->getMainSequenceName();
    if ($name !== null)
      return $this->getField($name);
    return null;
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
      if ($v !== null)
      {
        $r = $v->getCurrentVersion();
        if ($r !== null)
          return $r;
      }
    }
    return null;
  }
  
  public function getNewestVersion($variant)
  {
    $var = $this->getVariant($variant);
    if ($var !== null)
      return $var->getNewestVersion();
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
  
  public static function createItem($section, $class)
  {
    global $_STORAGE;
    
    if ($_STORAGE->queryExec('INSERT INTO Item (section,class) VALUES ("'.$_STORAGE->escape($section->getId()).'","'.$_STORAGE->escape($class->getId()).'");'))
    {
      $id = $_STORAGE->lastInsertRowid();
      $details = array('id' => $id, 'section' => $section->getId(), 'class' => $class->getId());
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
      if (($result !== false) && ($result->valid()))
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
  private $current;
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
    krsort($this->versions);
    $this->complete = true;
    return $this->versions;
  }
  
  public function getNewestVersion()
  {
    global $_STORAGE;
    
    if ($this->complete)
    {
      reset($this->versions);
      list($key, $val) = each($this->versions);
      return $val;
    }
    else
    {
      $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE itemvariant='.$this->id.' ORDER BY version DESC;');
      if ($results->valid())
      {
        $details = $results->fetch();
        if (isset($this->versions[$details['version']]))
          return $this->versions[$details['version']];
        $version = new ItemVersion($details, $this);
        $this->versions[$details['version']] = $version;
        if ($version->isCurrent())
          $this->current = $version;
        return $this->versions[$details['version']];
      }
      return null;
    }
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
    global $_PREFS,$_STORAGE,$_USER;

    $class = $this->getItem()->getClass();
    if ($clone !== null)
    {
      $view = $clone->getView();
      if (!$class->isValidView($view))
        $view = $class->getDefaultView();
    }
    else
      $view = $class->getDefaultView();
    
    if ($view !== null)
      $viewid = '"'.$_STORAGE->escape($view->getId()).'"';
    else
      $viewid = 'NULL';
    $time = time();
    $results = $_STORAGE->query('SELECT MAX(version)+1 FROM VariantVersion WHERE itemvariant='.$this->id.' GROUP BY itemvariant;');
    if ($results->valid())
      $version = $results->fetchSingle();
    else
      $version = 1;
    if ($_STORAGE->queryExec('INSERT INTO VariantVersion (itemvariant,version,view,modified,owner,current,complete) ' .
      'VALUES ('.$this->id.','.$version.','.$viewid.','.$time.',"'.$_USER->getUsername().'",0,0);'))
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
        $sourcefiles = $clone->getStoragePath();
        if (is_dir($sourcefiles))
        {
          $_STORAGE->queryExec('INSERT INTO File (itemversion,file,description) ' .
            'SELECT '.$id.',file,description FROM File WHERE itemversion='.$clone->getId().';');
          $targetfiles = $iv->getStoragePath();
          recursiveMkDir($targetfiles);
          recursiveCopy($sourcefiles, $targetfiles);
        }
      }
      
      $fields = $iv->getFields();
      foreach ($fields as $name => $field)
      {
        if ($clone == null)
          $field->initialise();
        else
        {
          if ($field instanceof ClassField)
            $field->copyFrom($clone->getItem());
          else
            $field->copyFrom($clone);
        }
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
  private $itemview;
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
    $this->itemview = FieldSetManager::getView($details['view']);
    if ($this->itemview === null)
      $this->itemview = $this->getClass()->getDefaultView();
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
  
  public function getStoragePath()
  {
    global $_PREFS;
    return $_PREFS->getPref('storage.site.attachments').'/'.$this->getItem()->getId().'/'.$this->getVariant()->getVariant().'/'.$this->version;
  }
  
  public function getStorageUrl()
  {
    global $_PREFS;
    return $_PREFS->getPref('url.site.attachments').'/'.$this->getItem()->getId().'/'.$this->getVariant()->getVariant().'/'.$this->version;
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
  
  public function getView()
  {
    return $this->itemview;
  }
  
  public function setView($value)
  {
    global $_STORAGE;
    
    if ($this->complete)
      return;
      
    if ($this->itemview->getId() == $value->getId())
      return;
      
    if (!$this->getClass()->isValidView($value))
      return;
      
    $newtime = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET view="'.$_STORAGE->escape($value->getId()).'", modified='.$newtime.' WHERE id='.$this->getId().';'))
    {
      $this->itemview = $value;
      $this->modified = $newtime;
    }
  }
  
  public function getClass()
  {
    return $this->getItem()->getClass();
  }
  
  public function getLinkTarget()
  {
    if ($this->getClass() === null)
      return $this;
    
    if ($this->getClass()->allowsLink())
      return $this;
      
    $sequence = $this->getMainSequence();
    $items = $sequence->getItems();
    foreach ($items as $item)
    {
      $iv = $item->getCurrentVersion(Session::getCurrentVariant());
      if ($iv !== null)
      {
        $link = $iv->getLinkTarget();
        if ($link !== null)
          return $link;
      }
    }
    return null;
  }
  
  public function getMainSequence()
  {
    return $this->getItem()->getMainSequence();
  }
  
  public function getFields()
  {
    return array_merge($this->getClassFields(), $this->getViewFields());
  }
  
  public function getClassFields()
  {
    if ($this->getClass() == null)
      return array();
    
    $fields = array();
    $names = $this->getClass()->getFields();
    foreach ($names as $name)
      $fields[$name] = $this->getField($name);
    return $fields;
  }
  
  public function getViewFields()
  {
    if ($this->itemview == null)
      return array();
    
    $fields = array();
    $names = $this->itemview->getFields();
    foreach ($names as $name)
      $fields[$name] = $this->getField($name);
    return $fields;
  }
  
  public function hasField($name)
  {
    if (($this->getClass() !== null) && ($this->getClass()->hasField($name)))
      return true;
    if (($this->itemview !== null) && ($this->itemview->hasField($name)))
      return true;
    return false;
  }
  
  public function getField($name)
  {
    if (isset($this->fields[$name]))
      return $this->fields[$name];
      
    $field = $this->getItem()->getField($name, $this);

    if (($field === null) && ($this->itemview !== null))
      $field = $this->itemview->getField($this, $name);
    
    if ($field !== null)
      $this->fields[$name] = $field;
      
    return $field;
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