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

class ItemClass
{
  private $id;
  private $parent;
  private $name = '';
  private $description = '';
  private $fields;
  private $mainsequence = null;
  private $allowlink = true;
  
  public function __construct($id, $parent = null)
  {
    $this->id = $id;
    $this->parent = $parent;
  }

  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function allowsLink()
  {
    return $this->allowlink;
  }
  
  public function getTemplate()
  {
    return 'classes/'.$this->id.'.tpl';
  }
  
  public function getDescription()
  {
    return $this->description;
  }
  
  public function getMainSequence($item)
  {
    if ($this->mainsequence != null)
      return $this->getField($item, $this->mainsequence);
    if ($this->parent != null)
      return $this->parent->getMainSequence($item);
    return null;
  }
  
  protected function addMissingFields(&$fields, $item)
  {
    foreach ($this->fields as $name => $el)
      if (!isset($fields[$name]))
        $fields[$name] = Field::getField($el, $item, $name);
    if ($this->parent != null)
      $this->parent->addMissingFields($fields, $item);
  }
  
  public function getFields($item)
  {
    $fields = array();
    $this->addMissingFields($fields, $item);
    return $fields;
  }
  
  public function getField($item, $name)
  {
    if (isset($this->fields[$name]))
      return Field::getField($this->fields[$name], $item, $name);
    if ($this->parent != null)
      return $this->parent->getField($item, $name);
    return null;
  }
  
  protected function parseElement($element)
  {
  }
  
  public function load($element)
  {
    if ($element->hasAttribute('mainsequence'))
      $this->mainsequence = $element->getAttribute('mainsequence');
    if (($element->hasAttribute('allowlink')) && ($element->getAttribute('allowlink') == 'false'))
      $this->allowlink = false;
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='name')
        {
          $this->name=getDOMText($el);
        }
        else if ($el->tagName=='description')
        {
          $this->description=getDOMText($el);
        }
        else if ($el->tagName=='field')
        {
          $this->fields[$el->getAttribute('id')] = $el;
        }
        else
        {
          $this->parseElement($el);
        }
      }
      $el=$el->nextSibling;
    }
  }
}

class ClassManager
{
  private static $classes = array();
  private static $log;
  
  public static function init()
  {
    global $_PREFS;
    
    self::$log = LoggerManager::getLogger('swim.classmanager');
    self::loadClasses($_PREFS->getPref('storage.config'));
  }
  
  public static function loadClasses($dir)
  {
    $file = $dir.'/classes.xml';
    $doc = new DOMDocument();
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
              self::$log->debug('Creating page layout '.$id.' That extends another.');
              $base = self::getClass($el->getAttribute('extends'));
              self::$log->debug('Extends '.$base->getName());
              $class = new ItemClass($id, $base);
            }
            else
            {
              $class = new ItemClass($id);
            }
            self::$classes[$id]=$class;
            $class->load($el);
          }
        }
        $el=$el->nextSibling;
      }
    }
    else
    {
      self::$log->debug('No classes defined at '.$dir);
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
}

ClassManager::init();

?>
