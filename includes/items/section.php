<?

/*
 * Swim
 *
 * Sections.
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
  protected $id;
  protected $name = '';
  protected $item;
  protected $classes;
  protected $log;
  protected $roottype;
  
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
  
  private function getRootClass()
  {
    return $this->roottype;
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('roottype'))
      $this->roottype = $element->getAttribute('roottype');
  }
  
  protected function parseElement($element)
  {
  }
  
  protected function findRoot($complete = false)
  {
    global $_STORAGE;
    
    $results = $_STORAGE->query('SELECT id FROM Item WHERE root=1 AND section="'.$this->id.'" AND class="'.$this->getRootClass().'";');
    if ($results->valid())
    {
      $this->item = $results->fetchSingle();
    }
    else
    {
      $class = FieldSetManager::getClass($this->getRootClass());
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
      if ($variant === null)
      {
        $this->log->error('Unable to create variant assertion.');
        return;
      }
      $version = $variant->createNewVersion();
      if ($version === null)
      {
        $this->log->error('Unable to create version assertion.');
        return;
      }
      $field = $version->getField('name');
      if ($field === null)
      {
        $this->log->warn('No name field for this class.');
        return;
      }
      $field->setValue($this->name);
      if ($complete)
      {
        $version->setComplete(true);
        $version->setCurrent(true);
      }
    }
  }
  
  public function load($element)
  {
    global $_STORAGE;
    
    $this->parseAttributes($element);
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
    
    $this->findRoot();
  }

  public function getPriority()
  {
    return ADMIN_PRIORITY_CONTENT;
  }
  
  public static function getSection($element)
  {
    $id = $element->getAttribute('id');
    if ($element->hasAttribute('type'))
      $type = $element->hasAttribute('type');
    else
      $type = 'content';
    if ($type == 'mailing')
      return new MailingSection($id);
    return new ContentSection($id);
  }
}

class ContentSection extends Section
{
  public function getType()
  {
    return 'content';
  }
  
  public function getIcon()
  {
    global $_PREFS;
    
    return $_PREFS->getPref('url.admin.static').'/icons/sitemap-blue.gif';
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('items/index.tpl');
    $request->setQueryVar('section', $this->getId());
    return $request->encode();
  }
  
  public function isAvailable()
  {
    return Session::getUser()->hasPermission('documents',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,6)=='items/') && ($request->hasQueryVar('section')) && ($request->getQueryVar('section')==$this->getId()))
      return true;
    return false;
  }
}

?>