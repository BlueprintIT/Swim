<?

/*
 * Swim
 *
 * Includes for items.
 *
 * Copyright Blueprint IT Ltd. 2007
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
  private $classes;
  private $log;
  
  public function __construct($id)
  {
    $this->id = $id;
    $this->log = LoggerManager::getLogger('swim.section');
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
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
    global $_STORAGE;
    
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
            if ($class !== null)
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
    
    $results = $_STORAGE->query('SELECT id FROM Item WHERE root=1 AND section="'.$this->id.'";');
    if ($results->valid())
    {
      $this->item = $results->fetchSingle();
    }
    else
    {
      if ($element->hasAttribute("roottype"))
      {
        $class = FieldSetManager::getClass($element->getAttribute("roottype"));
        if ($class === null)
        {
          $this->log->error('Invalid root type specified for '.$this->id.' section');
          return;
        }
        $item = Item::createItem($this, $class);
        if ($item === null)
        {
          $this->log->error('Unable to create item assertion.');
          return;
        }
        $this->item = $item->getId();
        $_STORAGE->queryExec('UPDATE Item SET root=1 WHERE id='.$this->item.';');
        $variant = $item->createVariant('default');
        if ($item === null)
        {
          $this->log->error('Unable to create variant assertion.');
          return;
        }
        $version = $variant->createNewVersion();
        if ($item === null)
        {
          $this->log->error('Unable to create version assertion.');
          return;
        }
        $field = $version->getField('name');
        if ($item === null)
        {
          $this->log->warn('No name field for this class.');
          return;
        }
        $field->setValue($this->name);
      }
      else
        $this->log->error('No root type specified for '.$this->id.' section');
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
    return Session::getUser()->hasPermission('documents',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,6)=='items/') && ($request->hasQueryVar('section')) && ($request->getQueryVar('section')==$this->id))
      return true;
    return false;
  }
}

?>