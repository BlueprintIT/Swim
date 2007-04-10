<?

/*
 * Swim
 *
 * Class definitions for items.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('SWIM_FIELDSET_CACHE_VERSION',8);

class FieldSet extends XMLSerialized
{
  protected $id;
  protected $parent = null;
  protected $name = '';
  protected $description = '';
  protected $fields = array();
  protected $log;

  public function __construct($id, $parent = null)
  {
    $this->id = $id;
    $this->parent = $parent;
    $this->log = LoggerManager::getLogger('swim.fieldset');
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
    $this->log = LoggerManager::getLogger('swim.fieldset');
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getDescription()
  {
    return $this->description;
  }
  
  public function getParent()
  {
    return $this->parent;
  }
  
  public function getFieldType($name)
  {
    if (isset($this->fields[$name]))
      return $this->fields[$name]->getType();
    if ($this->parent !== null)
      return $this->parent->getFieldType($name);
    return null;
  }
  
  public function getFields()
  {
    if ($this->parent !== null)
      $fields = $this->parent->getFields();
    else
      $fields = array();
    foreach ($this->fields as $name => $field)
      $fields[$name] = $field;

    return $fields;
  }
  
  public function hasField($name)
  {
    if (isset($this->fields[$name]))
      return true;
    if ($this->parent !== null)
      return $this->parent->hasField($name);
    return false;
  }
  
  public function getField($item, $name)
  {
    if (isset($this->fields[$name]))
    {
    	if ($item === null)
    		return $this->fields[$name];
      if ($this->fields[$name] instanceof ClassField)
      {
        if (($item instanceof ItemVariant) || ($item instanceof ItemVersion))
          $item = $item->getItem();

        if ($item instanceof Item)
        {
          $field = clone $this->fields[$name];
          $field->setItem($item);
          return $field;
        }
        else
        {
          $this->log->errortrace('Attempt to retrieve a '.$this->fields[$name]->getType().' field for a '.get_class($item).'.');
          return null;
        }
      }
      else if ($item instanceof ItemVersion)
      {
        $field = clone $this->fields[$name];
        $field->setItemVersion($item);
        return $field;
      }
      else
      {
        $this->log->errortrace('Attempt to retrieve a '.$this->fields[$name]->getType().' field for a '.get_class($item).'.');
        return null;
      }
    }
    if ($this->parent !== null)
      return $this->parent->getField($item, $name);
    return null;
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName=='name')
      $this->name=getDOMText($element);
    else if ($element->tagName=='description')
      $this->description=getDOMText($element);
    else if ($element->tagName=='field')
    {
      $field = BaseField::getField($element);
      $this->fields[$field->getId()] = $field;
    }
    else
      parent::parseElement($element);
  }
}

class ItemView extends FieldSet
{
}

class ItemClass extends FieldSet
{
  private $views;
  private $mainsequence;
  private $allowlink;
  private $versioning;
  private $type;
  private $mimetypes;
  
  public function getType()
  {
    if (isset($this->type))
      return $this->type;

    if ($this->parent !== null)
      return $this->parent->getType();
      
    return 'normal';
  }
  
  public function getMimeTypes()
  {
    if (isset($this->mimetypes))
      return $this->mimetypes;

    if ($this->parent !== null)
      return $this->parent->getMimeTypes();
      
    return array();
  }
  
  public function getVersioning()
  {
    if (isset($this->versioning))
      return $this->versioning;
      
    if ($this->parent !== null)
      return $this->parent->getVersioning();
      
    return 'full';
  }
  
  public function allowsLink()
  {
    if (isset($this->allowlink))
      return $this->allowlink;
    
    if ($this->parent !== null)
      return $this->parent->allowsLink();
    
    return true;
  }
  
  public function getDefaultView()
  {
    if (isset($this->views))
    {
      if (count($this->views)>0)
        return $this->views[0];
      else
        return null;
    }

    if ($this->parent !== null)
      return $this->parent->getDefaultView();
      
    return null;
  }
  
  public function getViews()
  {
    if (isset($this->views))
      return $this->views;
    
    if ($this->parent !== null)
      return $this->parent->getViews();
    
    return array();
  }
  
  public function isValidView($view)
  {
    if ((!isset($this->views)) && ($this->parent !== null))
      return $this->parent->isValidView($view);
      
    if ($view === null)
      return ((!isset($this->views)) || (count($this->views)==0));
    
    if (isset($this->views))
      return in_array($view, $this->views, true);
    return false;
  }
  
  public function getMainSequenceName()
  {
    if (isset($this->mainsequence))
      return $this->mainsequence;
    if ($this->parent !==null)
      return $this->parent->getMainSequenceName();
    return null;
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName=='views')
    {
      $this->views = array();
      $views = explode(",", getDOMText($element));
      foreach ($views as $viewid)
      {
        $view = FieldSetManager::getView($viewid);
        if ($view !== null)
          array_push($this->views, $view);
        else
          LoggerManager::getLogger('swim.itemclass')->warn('Invalid view '.$viewid.' specified for '.$this->getId());
      }
    }
    else if ($element->tagName=='mimetypes')
    {
      $this->mimetypes = explode(",", getDOMText($element));
    }
    else
      parent::parseElement($element);
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('mainsequence'))
      $this->mainsequence = $element->getAttribute('mainsequence');
    if ($element->hasAttribute('allowlink'))
      $this->allowlink = ($element->getAttribute('allowlink') == 'true');
    if ($element->hasAttribute('versioning'))
      $this->versioning = $element->getAttribute('versioning');
    if ($element->hasAttribute('type'))
      $this->type = $element->getAttribute('type');
    parent::parseAttributes($element);
  }
  
  public function load($element)
  {
  	parent::load($element);
  	if (!$this->hasField('name'))
  		$this->log->warn('Class '.$this->name.' does not have a name field defined. This could cause problems in the admin interface.');
  	if (($this->type == 'file') && ((!isset($this->fields['file'])) || ($this->fields['file']->getType()!='file')))
  		$this->log->error('Class '.$this->name.' does not have a valid file field. Uploaded files will be lost.');
  }
}

class OptionSet extends XMLSerialized
{
  private $id;
  private $log;
  private $name;
  private $usename = true;
  private $options = array();
  
  public function __construct($id)
  {
    $this->id = $id;
    $this->log = LoggerManager::getLogger('swim.tag');
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    unset($vars['options']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
    $this->log = LoggerManager::getLogger('swim.tag');
    $this->options = array();
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function useName()
  {
    return $this->usename;
  }
  
  public function createOption($name, $value)
  {
    global $_STORAGE;
    
    if ($name == null)
      $name = 'NULL';
    else
      $name = '"'.$_STORAGE->escape($name).'"';

    $_STORAGE->queryExec('INSERT INTO OptionSet (optionset, name, value) VALUES ("'.$_STORAGE->escape($this->id).'", '.$name.', "'.$_STORAGE->escape($value).'");');
    return $this->getOption($_STORAGE->lastInsertRowid());
  }
  
  public function getOptions()
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT * FROM OptionSet WHERE optionset="'.$_STORAGE->escape($this->id).'";');
    while ($results->valid())
    {
      $details = $results->fetch();
      if (!isset($this->options[$details['id']]))
      {
        $option = new Option($this, $details);
        $this->options[$details['id']] = $option;
      }
    }
    return $this->options;
  }
  
  public function getOption($id)
  {
    global $_STORAGE;
    
    if (!isset($this->options[$id]))
    {
      $results = $_STORAGE->query('SELECT * FROM OptionSet WHERE optionset="'.$_STORAGE->escape($this->id).'" AND id='.$id.';');
      if ($results->valid())
      {
        $option = new Option($this, $results->fetch());
        $this->options[$id] = $option;
      }
    }
    return $this->options[$id];
  }
  
  public function getOptionsByName($name)
  {
    global $_STORAGE;
    
    $result = array();
    $results = $_STORAGE->query('SELECT * FROM OptionSet WHERE optionset="'.$_STORAGE->escape($this->id).'" AND name="'.$_STORAGE->escape($name).'";');
    while ($results->valid())
    {
      $details = $results->fetch();
      if (isset($this->options[$details['id']]))
        $result[$this->options[$details['id']]->getName()] = $this->options[$details['id']];
      else
      {
        $option = new Option($this, $details);
        $this->options[$details['id']] = $option;
        $result[$option->getName()] = $option;
      }
    }
    return $result;
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute("usename") && $element->getAttribute("usename")=="false")
      $this->usename = false;
    parent::parseAttributes($element);
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName=='name')
    {
      $this->name = getDOMText($element);
    }
    else
      parent::parseElement($element);
  }
}

class Option
{
  private $id;
  private $name;
  private $value;
  private $tagset;
  
  public function __construct($tagset, $details)
  {
    $this->tagset = $tagset;
    $this->id = $details['id'];
    $this->name = $details['name'];
    $this->value = $details['value'];
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function setName($value)
  {
    global $_STORAGE;
    
    $_STORAGE->queryExec('UPDATE OptionSet SET name="'.$_STORAGE->escape($value).'" WHERE id='.$this->id.';');
    $this->name = $value;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setValue($value)
  {
    global $_STORAGE;
    
    $_STORAGE->queryExec('UPDATE OptionSet SET value="'.$_STORAGE->escape($value).'" WHERE id='.$this->id.';');
    $this->value = $value;
  }
  
  public function getValue()
  {
    return $this->value;
  }
}

class FieldSetManager
{
  private static $sections = array();
  private static $classes = array();
  private static $views = array();
  private static $options = array();
  private static $log;
  
  public static function isCacheValid($cache, $files)
  {
    if (!is_readable($cache))
      return false;
      
    foreach ($files as $file)
    {
      if ((file_exists($file)) && (filemtime($cache)<filemtime($file)))
        return false;
    }
    return true;
  }
  
  public static function loadFromCache($cache, $files)
  {
    $results = unserialize(file_get_contents($cache));
    if (!is_array($results))
      return false;
    if ((!isset($results['version'])) || ($results['version'] != SWIM_FIELDSET_CACHE_VERSION))
      return false;
      
    if ((!isset($results['classes'])) || (!is_array($results['classes'])))
      return false;
    if ((!isset($results['views'])) || (!is_array($results['views'])))
      return false;
    if ((!isset($results['options'])) || (!is_array($results['options'])))
      return false;
    if ((!isset($results['sections'])) || (!is_array($results['sections'])))
      return false;

    self::$classes = $results['classes'];
    self::$views = $results['views'];
    self::$options = $results['options'];
    self::$sections = $results['sections'];

    self::$log->debug('Loaded '.count(self::$sections).' sections, '.count(self::$views).' views, '.count(self::$classes).' classes and '.count(self::$options).' optionsets from cache.');
    return true;
  }
  
  public static function init()
  {
    global $_PREFS;
    
    self::$log = LoggerManager::getLogger('swim.classmanager');
    
    $cache = $_PREFS->getPref('storage.sitecache');
    if (!is_dir($cache))
    	recursiveMkDir($cache);
    	
    $cache = $cache.'/fieldsets.ser';
    $files = array($_PREFS->getPref('storage.config').'/optionsets.xml', 
                   $_PREFS->getPref('storage.config').'/views.xml', 
                   $_PREFS->getPref('storage.config').'/classes.xml', 
                   $_PREFS->getPref('storage.config').'/sections.xml');
    if (!self::isCacheValid($cache, $files) || !self::loadFromCache($cache, $files))
    {
      self::loadFieldSets($files);
      $results = array('version' => SWIM_FIELDSET_CACHE_VERSION, 
                       'options' => self::$options,
                       'views' => self::$views, 
                       'classes' => self::$classes, 
                       'sections' => self::$sections);
      file_put_contents($cache, serialize($results));
      self::$log->debug('Loaded '.count(self::$sections).' sections, '.count(self::$views).' views, '.count(self::$classes).' classes and '.count(self::$options).' optionsets.');
    }
    
    foreach (self::$sections as $id => $section)
      AdminManager::addSection($section);
  }
  
  public static function loadFieldSets($files)
  {
  	$loads = array();
    $doc = new DOMDocument();
    foreach ($files as $file)
    {
      if ((is_readable($file))&&($doc->load($file)))
      {
        $el=$doc->documentElement->firstChild;
        while ($el!==null)
        {
          if ($el->nodeType==XML_ELEMENT_NODE)
          {
            if ($el->tagName=='section')
            {
              $section = Section::getSection($el);
              self::$sections[$section->getId()]=$section;
              array_push($loads, array('item' => $section, 'data' => $el));
            }
            if ($el->tagName=='class')
            {
              $id = $el->getAttribute('id');
              if ($el->hasAttribute('extends'))
              {
                self::$log->debug('Creating class '.$id.' That extends another.');
                $base = self::getClass($el->getAttribute('extends'));
                $class = new ItemClass($id, $base);
              }
              else
              {
                $class = new ItemClass($id);
              }
              self::$classes[$id]=$class;
              array_push($loads, array('item' => $class, 'data' => $el));
            }
            else if ($el->tagName=='view')
            {
              $id = $el->getAttribute('id');
              if ($el->hasAttribute('extends'))
              {
                self::$log->debug('Creating view '.$id.' That extends another.');
                $base = self::getView($el->getAttribute('extends'));
                $view = new ItemView($id, $base);
              }
              else
              {
                $view = new ItemView($id);
              }
              self::$views[$id]=$view;
              array_push($loads, array('item' => $view, 'data' => $el));
            }
            else if ($el->tagName=='optionset')
            {
              $id = $el->getAttribute('id');
              $tag = new OptionSet($id);
              self::$options[$id] = $tag;
              $tag->load($el);
            }
          }
          $el=$el->nextSibling;
        }
      }
      else
      {
        self::$log->debug('No fieldsets defined at '.$file);
      }
    }
    foreach ($loads as $item)
    	$item['item']->load($item['data']);
  }
  
  public static function getOptionSets()
  {
    return self::$options;
  }
  
  public static function getOptionSet($id)
  {
    if (isset(self::$options[$id]))
      return self::$options[$id];
    else
      return null;
  }
  
  public static function getSections()
  {
    return self::$sections;
  }
  
  public static function getSection($id)
  {
    if (isset(self::$sections[$id]))
      return self::$sections[$id];
    else
      return null;
  }
  
  public static function getClasses()
  {
    return self::$classes;
  }
  
  public static function getClass($id)
  {
    if (isset(self::$classes[$id]))
      return self::$classes[$id];
    else
      return null;
  }
  
  public static function getViews()
  {
    return self::$views;
  }
  
  public static function getView($id)
  {
    if (isset(self::$views[$id]))
      return self::$views[$id];
    else
      return null;
  }
}

FieldSetManager::init();

?>
