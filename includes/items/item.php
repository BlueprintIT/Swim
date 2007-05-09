<?

/*
 * Swim
 *
 * The basic database item
 *
 * Copyright Blueprint IT Ltd. 2007
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
  private $path;
  private $archived;
  private $complete;
  private $variants = array();
  private $fields = array();
  
  private function __construct($details)
  {
    $this->log = LoggerManager::getLogger('swim.item');
    
    $this->id = $details['id'];
    $this->section = $details['section'];
    $this->itemclass = FieldSetManager::getClass($details['class']);
    if ($this->itemclass === null)
    	$this->log->warn('Item '.$this->id.' has an invalid class - '.$details['class']);
    if (($details['path']===false)||($details['path']==''))
    	$this->path = null;
    else
    	$this->path = $details['path'];
    if (isset($details['archived']) && $details['archived']==1)
      $this->archived = true;
    else
      $this->archived = false;
  }
  
  public function delete()
  {
    global $_STORAGE;
    
    $variants = $this->getVariants();
    foreach ($variants as $variant)
    {
      $variant->delete();
    }

    $results = $_STORAGE->query('SELECT parent,field,position FROM Sequence WHERE item='.$this->getId().' ORDER BY position DESC;');
    while ($results->valid())
    {
      $details = $results->fetch();
      $item = Item::getItem($details['parent']);
      $sequence = $item->getSequence($details['field']);
      $sequence->removeItem($details['position']);
    }
    
    $fields = $this->itemclass->getFields();
    foreach ($fields as $name => $field)
    {
      if ($field->getType() == 'sequence')
      {
        $sequence = $this->getSequence($name);
        $sequence->onDeleted();
      }
    }
    
    $_STORAGE->queryExec('DELETE FROM Sequence WHERE parent='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM Keyword WHERE item='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM Item WHERE id='.$this->id.';');
    ObjectCache::removeItem('dbitem', $this->id);
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function isArchived()
  {
    return $this->archived;
  }
  
  public function setArchived($value)
  {
    global $_STORAGE;
    
    if ($value == $this->archived)
      return;
      
    if ($value)
      $value = 1;
    else
      $value = 0;

    if ($_STORAGE->queryExec('UPDATE Item SET archived='.$value.' WHERE id='.$this->getId().';'))
      $this->archived = $value;
      
    if ($value)
    {
      $results = $_STORAGE->query('SELECT parent,field,position FROM Sequence WHERE item='.$this->getId().' ORDER BY position DESC;');
      while ($results->valid())
      {
        $details = $results->fetch();
        $item = Item::getItem($details['parent']);
        $sequence = $item->getSequence($details['field']);
        $sequence->removeItem($details['position']);
      }
    }

    $fields = $this->itemclass->getFields();
    foreach ($fields as $name => $field)
    {
      if ($field->getType() == 'sequence')
      {
        $sequence = $this->getSequence($name);
        $sequence->onArchivedChanged($value);
      }
    }
  }
  
  public function getSection()
  {
    return FieldSetManager::getSection($this->section);
  }
  
  public function getClass()
  {
    return $this->itemclass;
  }
  
  public function getPath()
  {
  	return $this->path;
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
  
  private function findParentPaths(&$seen)
  {
    $paths = array();
    $seen[$this->id] = true;
    $parents = $this->getParents();
    if (count($parents)>0)
    {
      foreach ($parents as $parentd)
      {
        $parent = $parentd['item'];
        if ($parent->getMainSequence()->getId()==$parentd['field'])
        {
          if (!isset($seen[$parent->getId()]))
          {
            $newpaths = $parent->findParentPaths($seen);
            foreach ($newpaths as $path)
            {
              array_push($path, $this);
              array_push($paths, $path);
            }
          }
        }
      }
    }
    else
    {
      array_push($paths, array($this));
    }
    return $paths;
  }
  
  public static function itemPathSort($a, $b)
  {
    if (count($a)<count($b))
      return -1;
    else if (count($a)>count($b))
      return 1;
    else
    {
      $pos = 0;
      while ($pos<count($a) && $a[$pos]===$b[$pos])
      {
        $pos++;
      }
      if ($pos<count($a))
      {
        if ($pos>0)
        {
          $seq = $a[$pos-1]->getMainSequence();
          $pos1 = $seq->indexOf($a[$pos]);
          $pos2 = $seq->indexOf($b[$pos]);
          return $pos1-$pos2;
        }
        if ($a[0]->getSection()->getRootItem()===$a[0])
          return -1;
        if ($b[0]->getSection()->getRootItem()===$b[0])
          return 1;
      }
      return 0;
    }
  }
  
  public function getParentPaths()
  {
    $seen = array();
    $paths = $this->findParentPaths($seen);
    usort($paths, array("Item", "itemPathSort"));
    return $paths;
  }
  
  public function getParentPath()
  {
    $paths = $this->getParentPaths();
    if (count($paths)>0)
      return $paths[0];
    else
      return null;
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
  
  public function getVariants()
  {
    global $_STORAGE;
    
    if ($this->complete)
      return $this->variants;
      
    $results = $_STORAGE->query('SELECT * FROM ItemVariant WHERE item='.$this->id.';');
    while ($results->valid())
    {
      $details = $results->fetch();
      if (!isset($this->variants[$details['variant']]))
      {
        $variant = new ItemVariant($details);
        $this->variants[$details['variant']] = $variant;
      }
    }
    krsort($this->variants);
    $this->complete = true;
    return $this->variants;
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
  
  private function internalGetSubitems($depth, $types, &$items)
  {
    $sequence = $this->getMainSequence();
    if ($sequence !== null)
    {
      foreach ($sequence->getItems() as $subitem)
      {
        if (!isset($items[$subitem->getId()]))
        {
          if (($types === null) || (in_array($subitem->getClass()->getId(), $types)))
            $items[$subitem->getId()] = $subitem;
          if ($depth!=0)
            $subitem->internalGetSubitems($depth-1, $types, $items);
        }
      }
    }
  }
  
  public function getSubitems($types = null, $depth = -1)
  {
    $results = array();
    $this->internalGetSubitems($depth, $types, $results);
    return $results;
  }
  
  public static function createItem($section, $class)
  {
    global $_STORAGE;
    
    if ($_STORAGE->queryExec('INSERT INTO Item (section,class,created) VALUES ("'.$_STORAGE->escape($section->getId()).'","'.$_STORAGE->escape($class->getId()).'",'.time().');'))
    {
      $id = $_STORAGE->lastInsertRowid();
      $details = array('id' => $id, 'section' => $section->getId(), 'class' => $class->getId(), 'path' => '');
      $item = new Item($details);
      ObjectCache::setItem('dbitem', $id, $item);
      return $item;
    }
    return null;
  }
  
  public static function getItemVersion($id, $variant = null, $version = null)
  {
    global $_STORAGE;

    if (($variant === null) && ($version === null))
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
    if ($item === null)
      return null;
    $v = $item->getVariant($variant);
    if ($v === null)
      return null;
    return $v->getVersion($version);
  }
  
  public static function getItem($id, $details = null)
  {
    global $_STORAGE;
    
    $item = ObjectCache::getItem('dbitem', $id);
    if ($item === null)
    {
    	if ($details === null)
    	{
	      $result = $_STORAGE->query('SELECT * FROM Item WHERE id='.$id.';');
	      if (($result !== false) && ($result->valid()))
	        $item = new Item($result->fetch());
	      else
	        $item = null;
    	}
    	else
    		$item = new Item($details);
      ObjectCache::setItem('dbitem', $id, $item);
    }
    return $item;
  }
  
  public static function findItems($section = null, $class = null, $variant = null, $fieldname = null, $fieldvalue = null, $fieldtype = 'text', $complete = true, $current = true, $archived = false)
  {
    global $_STORAGE;
    
    if (is_array($class))
    {
      if (count($class)==0)
        return array();
      else if (count($class)==1)
        $class = $class[0];
    }
    
    if (is_array($section))
    {
      if (count($section)==0)
        return array();
      else if (count($section)==1)
        $section = $section[0];
    }
    
    $tables = '((Item JOIN ItemVariant ON Item.id=ItemVariant.item) JOIN VariantVersion ON ItemVariant.id = VariantVersion.itemvariant)';
    if ($fieldname !== null)
      $tables .= ' JOIN Field ON Field.itemversion=VariantVersion.id';
    $query = 'SELECT ItemVariant.item,ItemVariant.variant,VariantVersion.version FROM '.$tables.' WHERE';
    $params = '';

    if ($current !== null)
    {
      if ($current)
        $params .= ' AND VariantVersion.current=1';
      else
        $params .= ' AND VariantVersion.current<>1';
    }

    if ($complete !== null)
    {
      if ($complete)
        $params .= ' AND VariantVersion.complete=1';
      else
        $params .= ' AND VariantVersion.complete<>1';
    }

    if ($archived !== null)
    {
      if ($archived)
        $params .= ' AND Item.archived=1';
      else
        $params .= ' AND Item.archived<>1';
    }

    if ($fieldname !== null)
    {
      $pos = strpos($fieldname, '.');
      if ($pos !== false)
        $params.=' AND Field.basefield="'.substr($fieldname, 0, $pos).'" AND Field.field="'.substr($fieldname, $pos+1).'"';
      else
        $params.=' AND Field.basefield="base" AND Field.field="'.$fieldname.'"';
      $params.=' AND Field.';
      switch ($fieldtype)
      {
        case 'date':
          $params .= 'dateValue='.$fieldvalue;
          break;
        case 'boolean':
          if (($fieldvalue === true) || ($fieldvalue === 'true') || ($fieldvalue === 1))
            $fieldvalue = 1;
          else
            $fieldvalue = 0;
        case 'int':
          $params .= 'intValue='.$fieldvalue;
          break;
        default:
          $params .= 'textValue="'.$_STORAGE->escape($fieldvalue).'"';
          break;
      }
    }
    
    if ($class !== null)
    {
      $params.=' AND ';
      if (is_array($class))
      {
        $params .= '(';
        foreach ($class as $c)
          $params .= 'Item.class="'.$c->getId().'" OR ';
        $params = substr($query, 0, -4).')';
      }
      else
        $params .= 'Item.class="'.$class->getId().'"';
    }
    
    if ($section !== null)
    {
      $params.=' AND ';
      if (is_array($section))
      {
        $params .= '(';
        foreach ($section as $c)
          $params .= 'Item.section="'.$c->getId().'" OR ';
        $params = substr($query, 0, -4).')';
      }
      else
        $params .= 'Item.section="'.$section->getId().'"';
    }

    if ($variant !== null)
      $params.=' AND ItemVariant.variant="'.$variant.'"';

    if (strlen($params) > 0)
      $query .= substr($params, 4);
    
    $items = array();
    $results = $_STORAGE->query($query);
    while ($results->valid())
    {
      $details = $results->fetch();
      array_push($items, Item::getItemVersion($details['item'], $details['variant'], $details['version']));
    }
    return $items;
  }

  public function getStoragePath()
  {
    global $_PREFS;
    return $_PREFS->getPref('storage.site.attachments').'/'.$this->getId();
  }
  
  public function getStorageUrl()
  {
    global $_PREFS;
    return $_PREFS->getPref('url.site.attachments').'/'.$this->getId();
  }
}

class ItemVariant
{
  private $log;
  private $id;
  private $variant;
  private $item;
  private $current;
  private $draft;
  private $versions = array();
  private $complete = false;
  
  public function __construct($details)
  {
    $this->log = LoggerManager::getLogger('swim.itemvariant');
    $this->item = $details['item'];
    $this->variant = $details['variant'];
    $this->id = $details['id'];
  }

  public function delete()
  {
    global $_STORAGE;
    
    foreach ($this->getVersions() as $version)
    {
      $version->delete();
    }
    
    $_STORAGE->queryExec('DELETE FROM ItemVariant WHERE id='.$this->id.';');
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
      
    $this->current = null;
    $this->draft = null;
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
        if (!$version->isComplete())
          $this->draft = $version;
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
  
  public function getDraftVersion()
  {
    global $_STORAGE;
    
    if (isset($this->draft) || $this->complete)
      return $this->draft;
      
    $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE itemvariant='.$this->id.' AND complete=0;');
    if ($results->valid())
    {
      $version = new ItemVersion($results->fetch(), $this);
      $this->draft = $version;
      $this->versions[$version->getVersion()] = $version;
    }
    return $this->draft;
  }
  
  public function getCurrentVersion()
  {
    global $_STORAGE;
    
    if (isset($this->current) || $this->complete)
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
    global $_PREFS,$_STORAGE;

    $user = Session::getUser();
    
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
    $results = $_STORAGE->query('SELECT MAX(version)+1 FROM VariantVersion WHERE itemvariant='.$this->id.';');
    if ($results->valid())
    {
      $version = $results->fetchSingle();
      if ($version === null)
        $version = 1;
    }
    else
      $version = 1;
    if ($_STORAGE->queryExec('INSERT INTO VariantVersion (itemvariant,version,view,created,modified,owner,current,complete) ' .
      'VALUES ('.$this->id.','.$version.','.$viewid.','.$time.','.$time.',"'.$user->getUsername().'",0,0);'))
    {
      $id = $_STORAGE->lastInsertRowid();
      $results = $_STORAGE->query('SELECT * FROM VariantVersion WHERE id='.$id.';');
      $details = $results->fetch();
      $iv = new ItemVersion($details, $this);
      $this->versions[$iv->getVersion()] = $iv;
      
      if ($clone !== null)
      {
        $_STORAGE->queryExec('INSERT INTO Field (itemversion,basefield,pos,field,textValue,intValue,dateValue) ' .
          'SELECT '.$id.',basefield,pos,field,textValue,intValue,dateValue FROM Field WHERE itemversion='.$clone->getId().';');
        $sourcefiles = $clone->getStoragePath();
        if (is_dir($sourcefiles))
        {
          $_STORAGE->queryExec('INSERT INTO File (itemversion,file,description) ' .
            'SELECT '.$id.',file,description FROM File WHERE itemversion='.$clone->getId().';');
          $targetfiles = $iv->getStoragePath();
          recursiveMkDir($targetfiles);
          recursiveCopy($sourcefiles, $targetfiles, true);
        }
      }
      
      $fields = $iv->getFields();
      foreach ($fields as $name => $field)
      {
        if ($clone === null)
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
  private $published;
  private $complete;
  private $current;
  private $fields = array();
  
  public function __construct($details, $variant = null)
  {
    $this->log = LoggerManager::getLogger('swim.itemversion');
    
    $this->id = $details['id'];
    if ($variant !== null)
      $this->variant = $variant;
    $this->variantid = $details['itemvariant'];
    $this->version = $details['version'];
    $this->itemview = FieldSetManager::getView($details['view']);
    if ($this->itemview === null)
    {
    	$this->log->warn('Unknown view for itemversion '.$this->id.' - '.$details['view']);
      $this->itemview = $this->getClass()->getDefaultView();
    }
    $this->modified = $details['modified'];
    $this->published = $details['published'];
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
  
  public function delete()
  {
    global $_STORAGE;
    
    recursiveDelete($this->getStoragePath());
    rmdir($this->getStoragePath());
    $_STORAGE->queryExec('DELETE FROM Field WHERE itemversion='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM File WHERE itemversion='.$this->id.';');
    $_STORAGE->queryExec('DELETE FROM VariantVersion WHERE id='.$this->id.';');
  }
  
  public function getStoragePath()
  {
    return $this->getItem()->getStoragePath().'/'.$this->getVariant()->getVariant().'/'.$this->version;
  }
  
  public function getStorageUrl()
  {
    return $this->getItem()->getStorageUrl().'/'.$this->getVariant()->getVariant().'/'.$this->version;
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
    
    if ($this->variant !== null)
      return $this->variant;
    $results = $_STORAGE->query('SELECT item,variant FROM ItemVariant WHERE id='.$this->variantid.';');
    if ($results->valid())
    {
      $details = $result->fetch();
      $item = Item::getItem($details['item']);
      if ($item !== null)
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
  
  public function getPublished()
  {
    return $this->published;
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

    if ($_STORAGE->queryExec('UPDATE VariantVersion SET complete='.$bit.' WHERE id='.$this->getId().';'))
      $this->complete = $value;
  }
  
  public function isCurrent()
  {
    return $this->current;
  }
  
  public function setCurrent($value)
  {
    global $_STORAGE;
    
    if ($this->current == $value)
      return;
      
    if ($value)
    {
	    if (($_STORAGE->queryExec('UPDATE VariantVersion SET current=0 WHERE current=1 AND itemvariant="'.$this->variantid.'";'))
	      && ($_STORAGE->queryExec('UPDATE VariantVersion SET current=1, published='.time().' WHERE id='.$this->getId().';')))
	      $this->current = true;
    }
    else
    {
	    if ($_STORAGE->queryExec('UPDATE VariantVersion SET current=0 WHERE id='.$this->getId().';'))
	    	$this->current = false;
    }
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
      
    if ($this->itemview === $value)
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
  
  public function getTemplate($extra = null)
  {
  	global $_PREFS;
  	
  	$dir = $_PREFS->getPref('storage.site.templates').'/classes/';
		$view = $this->itemview->getId();

		$cls = $this->getItem()->getClass();
		while ($cls !== null)
		{
			$class = $cls->getId();

			$paths = array();
			if ($extra !== null)
			{
				array_push($paths, $dir.$class.'/'.$extra);
				array_push($paths, $dir.$class.'/'.$view.'/'.$extra);
			}
			array_push($paths, $dir.$class.'/'.$view);
			array_push($paths, $dir.$class);
			
			foreach ($paths as $path)
			{
				$file = findDisplayableFile($path);
				if ($file !== null)
					return $file;
			}
			
			$cls = $cls->getParent();
		}
    return null;
  }
  
  public function getLinkTarget()
  {
    if ($this->getClass() === null)
      return $this;
    
    if ($this->getClass()->allowsLink())
      return $this;
      
    $sequence = $this->getMainSequence();
    if ($sequence !== null)
    {
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
    }
    return $this;
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
    if ($this->getClass() === null)
      return array();
    
    $fields = array();
    $bases = $this->getClass()->getFields();
    foreach ($bases as $name => $field)
      $fields[$name] = $this->getField($name);
    return $fields;
  }
  
  public function getViewFields()
  {
    if ($this->itemview === null)
      return array();
    
    $fields = array();
    $bases = $this->itemview->getFields();
    foreach ($bases as $name => $field)
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
  
  public function getFieldValue($name)
  {
    $field = $this->getField($name);
    if ($field !== null)
      return $field->getValue();
    return null;
  }
  
  public function setFieldValue($name, $value)
  {
    $field = $this->getField($name);
    if ($field !== null)
      $field->setValue($value);
  }
  
  public function updateModified($newtime = null)
  {
    global $_STORAGE;
    
    if ($this->complete)
      return;
     
    if ($newtime === null)
	    $newtime = time();
    if ($_STORAGE->queryExec('UPDATE VariantVersion SET modified='.$newtime.' WHERE id='.$this->getId().';'))
      $this->modified = $newtime;
  }
}

?>