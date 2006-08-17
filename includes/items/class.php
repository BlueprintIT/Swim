<?

/*
 * Swim
 *
 * Class definitions for items.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FieldSet
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
  }
  
  protected function parseAttributes($element)
  {
  }

  public function load($element)
  {
    $this->parseAttributes($element);
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='name')
          $this->name=getDOMText($el);
        else if ($el->tagName=='description')
          $this->description=getDOMText($el);
        else if ($el->tagName=='field')
        {
          $field = BaseField::getField($el);
          $this->fields[$field->getId()] = $field;
        }
        else
          $this->parseElement($el);
      }
      $el=$el->nextSibling;
    }
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
  
  public function getTemplate()
  {
    return 'classes/'.$this->id.'.tpl';
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
      return in_array($view, $this->views);
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
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('mainsequence'))
      $this->mainsequence = $element->getAttribute('mainsequence');
    if ($element->hasAttribute('allowlink'))
      $this->allowlink = ($element->getAttribute('allowlink') == 'true');
    if ($element->hasAttribute('versioning'))
      $this->versioning = $element->getAttribute('versioning');
    parent::parseAttributes($element);
  }
}

class FieldSetManager
{
  private static $classes = array();
  private static $views = array();
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
  
  public static function init()
  {
    global $_PREFS;
    
    self::$log = LoggerManager::getLogger('swim.classmanager');
    
    $cache = $_PREFS->getPref('storage.sitecache').'/fieldsets.ser';
    $files = array($_PREFS->getPref('storage.config').'/views.xml', $_PREFS->getPref('storage.config').'/classes.xml');
    if (self::isCacheValid($cache, $files))
    {
      $results = unserialize(file_get_contents($cache));
      self::$classes = $results['classes'];
      self::$views = $results['views'];
      self::$log->debug('Loaded '.count(self::$views).' views and '.count(self::$classes).' classes from cache.');
    }
    else
    {
      self::loadFieldSets($files);
      $results = array('views' => self::$views, 'classes' => self::$classes);
      file_put_contents($cache, serialize($results));
      self::$log->debug('Loaded '.count(self::$views).' views and '.count(self::$classes).' classes.');
    }
  }
  
  public static function loadFieldSets($files)
  {
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
              $class->load($el);
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
              $view->load($el);
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
  }
  
  public static function getClasses()
  {
    return self::$classes;
  }
  
  public static function getClass($id)
  {
    if (isset(self::$classes[$id]))
    {
      return self::$classes[$id];
    }
    else
    {
      return null;
    }
  }
  
  public static function getViews()
  {
    return self::$views;
  }
  
  public static function getView($id)
  {
    if (isset(self::$views[$id]))
    {
      return self::$views[$id];
    }
    else
    {
      return null;
    }
  }
}

FieldSetManager::init();

?>