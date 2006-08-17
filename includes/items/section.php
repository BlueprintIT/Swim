<?

/*
 * Swim
 *
 * Includes for items.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Section extends AdminSection
{
  private $id;
  private $name = '';
  private $item;
  private $variant = 'default';
  private $classes;
  private $log;
  
  public function __construct($id)
  {
    $this->id = $id;
    $this->log = LoggerManager::getLogger('swim.section');
  }

  public function getItems()
  {
    global $_STORAGE;
    
    $items = array();
    $results = $_STORAGE->query('SELECT id FROM Item WHERE section="'.$_STORAGE->escape($this->id).'";');
    while ($results->valid())
    {
      array_push($items, Item::getItem($results->fetchSingle()));
    }
    return $items;
  }
  
  public function getVisibleClasses()
  {
    if (isset($this->classes))
      return $this->classes;
    else
      return FieldSetManager::getClasses();
  }
  
  public function getRootItem()
  {
    return Item::getItem($this->item);
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  protected function parseElement($element)
  {
  }
  
  public function load($element)
  {
    $this->item = $element->getAttribute('item');
    if ($element->hasAttribute('variant'))
      $this->variant = $element->getAttribute('variant');
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='name')
        {
          $this->name=getDOMText($el);
        }
        else if ($el->tagName=='classes')
        {
          $this->classes = array();
          $items = explode(',', getDOMText($el));
          foreach ($items as $name)
          {
            $class = FieldSetManager::getClass($name);
            if ($class != null)
              $this->classes[$name] = $class; 
          }
        }
        else
        {
          $this->parseElement($el);
        }
      }
      $el=$el->nextSibling;
    }
  }

  public function getIcon()
  {
    global $_PREFS;
    
    return $_PREFS->getPref('url.admin.static').'/icons/sitemap-blue.gif';
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_CONTENT;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('items/index.tpl');
    $request->setQueryVar('section', $this->id);
    return $request->encode();
  }
  
  public function isAvailable()
  {
    global $_USER;
    
    return $_USER->hasPermission('documents',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,6)=='items/') && ($request->getQueryVar('section')==$this->id))
      return true;
    return false;
  }
}

class SectionManager
{
  private static $sections = array();
  private static $log;
  
  public static function init()
  {
    global $_PREFS;
    
    self::$log = LoggerManager::getLogger('swim.sectionmanager');
    self::loadSections($_PREFS->getPref('storage.config'));
  }
  
  public static function loadSections($dir)
  {
    $file = $dir.'/sections.xml';
    $doc = new DOMDocument();
    if ((is_readable($file))&&($doc->load($file)))
    {
      $el=$doc->documentElement->firstChild;
      while ($el!==null)
      {
        if ($el->nodeType==XML_ELEMENT_NODE)
        {
          if ($el->tagName=='section')
          {
            $id = $el->getAttribute('id');
            $section = new Section($id);
            self::$sections[$id]=$section;
            $section->load($el);
            AdminManager::addSection($section);
          }
        }
        $el=$el->nextSibling;
      }
    }
    else
    {
      self::$log->debug('No sections defined at '.$dir);
    }
  }
  
  public static function getSections()
  {
    return self::$sections;
  }
  
  public static function getSection($id)
  {
    if (isset(self::$sections[$id]))
    {
      return self::$sections[$id];
    }
    else
    {
      return null;
    }
  }
}

SectionManager::init();

?>