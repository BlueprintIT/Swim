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
  protected $parent;
  protected $name = '';
  protected $description = '';
  protected $fields = array();

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
          $this->fields[$el->getAttribute('id')] = $el;
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
  private $mainsequence = null;
  private $allowlink = true;
  
  public function allowsLink()
  {
    return $this->allowlink;
  }
  
  public function getTemplate()
  {
    return 'classes/'.$this->id.'.tpl';
  }
  
  public function getDefaultView()
  {
    foreach ($this->views as $id)
    {
      $view = ViewManager::getView($id);
      if ($view !== null)
        return $view;
    }
    if ($this->parent !== null)
      return $this->parent->getDefaultView();
      
    return null;
  }
  
  public function getViews()
  {
    if (!isset($this->views))
    {
      if ($this->parent !== null)
        return $this->parent->getViews();
      else
        return array();
    }

    $result = array();
    foreach ($this->views as $id)
    {
      $view = ViewManager::getView($id);
      if ($view !== null)
        array_push($result, $view);
    }
    return $result;
  }
  
  public function isValidView($view)
  {
    if ((!isset($this->views)) && ($this->parent !== null))
      return $this->parent->isValidView($view);
      
    if ($view === null)
      return ((!isset($this->views)) || (count($this->views)==0));
      
    return in_array($view->getId(), $this->views);
  }
  
  public function getMainSequence($item)
  {
    if ($this->mainsequence != null)
      return $this->getField($item, $this->mainsequence);
    if ($this->parent != null)
      return $this->parent->getMainSequence($item);
    return null;
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName=='views')
      $this->views = explode(",", getDOMText($element));
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('mainsequence'))
      $this->mainsequence = $element->getAttribute('mainsequence');
    if (($element->hasAttribute('allowlink')) && ($element->getAttribute('allowlink') == 'false'))
      $this->allowlink = false;
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
              self::$log->debug('Creating class '.$id.' That extends another.');
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

class ViewManager
{
  private static $views = array();
  private static $log;
  
  public static function init()
  {
    global $_PREFS;
    
    self::$log = LoggerManager::getLogger('swim.viewmanager');
    self::loadViews($_PREFS->getPref('storage.config'));
  }
  
  public static function loadViews($dir)
  {
    $file = $dir.'/views.xml';
    $doc = new DOMDocument();
    if ((is_readable($file))&&($doc->load($file)))
    {
      $el=$doc->documentElement->firstChild;
      while ($el!==null)
      {
        if ($el->nodeType==XML_ELEMENT_NODE)
        {
          if ($el->tagName=='view')
          {
            $id = $el->getAttribute('id');
            if ($el->hasAttribute('extends'))
            {
              self::$log->debug('Creating view '.$id.' That extends another.');
              $base = self::getView($el->getAttribute('extends'));
              self::$log->debug('Extends '.$base->getName());
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
      self::$log->debug('No views defined at '.$dir);
    }
  }
  
  public static function getViews()
  {
    return self::$viewss;
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

ViewManager::init();
ClassManager::init();

?>
