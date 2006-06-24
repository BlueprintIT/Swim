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
  
  public function getField($item, $name)
  {
    if (isset($this->fields[$name]))
      return Field::getField($this->fields[$name], $item, $name);
    if ($this->parent != null)
      return $this->parent->getField($item, $name);
    return Field::getField(null, $item, $name);
  }
  
  protected function parseElement($element)
  {
  }
  
  public function load($element)
  {
    if ($element->hasAttribute('mainsequence'))
      $this->mainsequence = $element->getAttribute('mainsequence');
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
          $this->fields[$el->getAttribute('name')] = $el;
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
              $base = $this->getClass($el->getAttribute('extends'));
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
