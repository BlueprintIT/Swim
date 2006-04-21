<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Layout
{
  var $id;
  var $prefs;
  var $name = '';
  var $description = '';
  var $collection;
  
  function Layout($id, $collection, $clone=null)
  {
    $this->id = $id;
    $this->collection = $collection;
    if ($clone!==null)
    {
      $this->name = $clone->name;
      $this->description = $clone->description;
      $this->prefs = new Preferences($clone->prefs);
    }
    else
    {
      $this->prefs = new Preferences();
    }
  }

  function getName()
  {
    return $this->name;
  }
  
  function getDescription()
  {
    return $this->description;
  }
  
  function hasDefaultFiles()
  {
    return false;
  }
  
  function getDefaultFileDir()
  {
    return null;
  }  
  
  function parseElement($element)
  {
  }
  
  function load($element)
  {
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='preferences')
        {
          $this->prefs->loadFromDOM($el,'',true);
        }
        else if ($el->tagName=='name')
        {
          $this->name=getDOMText($el);
        }
        else if ($el->tagName=='description')
        {
          $this->description=getDOMText($el);
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

class BlockLayout extends Layout
{
  var $type;
  
  function BlockLayout($id, $collection, $clone=null)
  {
    $this->Layout($id, $collection, $clone);
    if ($clone!==null)
    {
      $this->type = $clone->type;
    }
  }
  
  function load($element)
  {
    if ($element->hasAttribute('type'))
    {
      $this->type=$element->getAttribute('type');
    }
    parent::load($element);
  }
  
  function getType()
  {
    return $this->type;
  }
}

class PageVariable
{
  var $preference;
  var $name;
  var $description;
  var $type;
  
  function PageVariable()
  {
  }
  
  function load($element)
  {
    $this->preference = $element->getAttribute('preference');
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='name')
        {
          $this->name = getDOMText($el);
        }
        else if ($el->tagName=='description')
        {
          $this->description = getDOMText($el);
        }
        else if ($el->tagName=='type')
        {
          $this->type = getDOMText($el);
        }
      }
      $el = $el->nextSibling;
    }
  }
}

class PageLayout extends Layout
{
  var $blocks = array();
  var $variables = array();
  var $hidden = false;
  
  function PageLayout($id, $collection, $clone=null)
  {
    $this->Layout($id, $collection, $clone);
    if ($clone!==null)
    {
      foreach ($clone->blocks as $id => $block)
      {
        $this->blocks[$id]=new BlockLayout($id, $collection, $block);
      }
    }
  }
  
  function getBlockLayout($id)
  {
    if (isset($this->blocks[$id]))
    {
      return $this->blocks[$id];
    }
    else
    {
      return null;
    }
  }
  
  function parseElement($el)
  {
    if ($el->tagName=='blocklayout')
    {
      $id=$el->getAttribute('id');
      if ($el->hasAttribute('ref'))
      {
        $this->blocks[$id] = new BlockLayout($id, $this->collection, $this->collection->getBlockLayout($el->getAttribute('ref')));
      }
      else
      {
        if (isset($this->blocks[$id]))
        {
          if ($el->hasAttribute('override'))
          {
            unset($this->blocks[$id]);
          }
        }
        if (!isset($this->blocks[$id]))
        {
          $this->blocks[$id] = new BlockLayout($id, $this->collection);
        }
      }
      $this->blocks[$id]->load($el);
    }
    else if ($el->tagName=='variables')
    {
      $node = $el->firstChild;
      while ($node!==null)
      {
        if (($node->nodeType==XML_ELEMENT_NODE) && ($node->tagName=='variable'))
        {
          $var = new PageVariable();
          $var->load($node);
          $this->variables[$var->preference]=$var;
        }
        $node = $node->nextSibling;
      }
    }
  }

  function load($element)
  {
    if ($element->hasAttribute('hidden'))
    {
      $this->hidden=$element->getAttribute('hidden');
    }
    parent::load($element);
  }  
}

class LayoutCollection
{
  private $pages = array();
  private $blocks = array();
  private $parent = null;
  private $log;
  
  function LayoutCollection($dir, $parent = null)
  {
    $this->parent = $parent;
    $this->log=LoggerManager::getLogger('swim.layout');
    $this->log->debug('Layout startup.');
    $this->loadLayouts($dir);
  }
  
  public function loadLayouts($dir)
  {
    $file = $dir.'/layouts.xml';
    $doc = new DOMDocument();
    if ((is_readable($file))&&($doc->load($file)))
    {
      $el=$doc->documentElement->firstChild;
      while ($el!==null)
      {
        if ($el->nodeType==XML_ELEMENT_NODE)
        {
          if ($el->tagName=='pagelayout')
          {
            $id = $el->getAttribute('id');
            if ($el->hasAttribute('extends'))
            {
              $this->log->debug('Creating page layout '.$id.' That extends another.');
              $base = $this->getPageLayout($el->getAttribute('extends'));
              $this->log->debug('Extends '.$base->getName());
              $layout = new PageLayout($id, $this, $base);
            }
            else
            {
              $layout = new PageLayout($id, $this);
            }
            $this->pages[$id]=$layout;
            $layout->load($el);
          }
          else if ($el->tabName=='blocklayout')
          {
            $id = $el->getAttribute('id');
            if ($el->hasAttribute('extends'))
            {
              $base = $this->getBlockLayout($el->getAttribute('extends'));
              $layout = new BlockLayout($id, $this, $base);
            }
            else
            {
              $layout = new BlockLayout($id, $this);
            }
            $this->blocks[$id]=$layout;
            $layout->load($el);
          }
        }
        $el=$el->nextSibling;
      }
    }
    else
    {
      $this->log->debug('Layout template does not exist in '.$dir);
    }
  }
  
  public function getPageLayouts()
  {
    if ($this->parent ===null)
    {
      return $this->pages;
    }
    else
    {
      return array_merge($this->parent->getPageLayouts(), $this->pages);
    }
  }
  
  public function getPageLayout($id)
  {
    if (isset($this->pages[$id]))
    {
      return $this->pages[$id];
    }
    else if ($this->parent !== null)
    {
      return $this->parent->getPageLayout($id);
    }
    else
    {
      return null;
    }
  }
  
  public function getBlockLayouts()
  {
    if ($this->parent ===null)
    {
      return $this->blocks;
    }
    else
    {
      return array_merge($this->parent->getBlockLayouts(), $this->blocks);
    }
  }
  
  public function getBlockLayout($id)
  {
    if (isset($this->blocks[$id]))
    {
      return $this->blocks[$id];
    }
    else if ($this->parent !== null)
    {
      return $this->parent->getBlockLayout($id);
    }
    else
    {
      return null;
    }
  }
}

class LayoutManager
{
  private static $root;
  
  static function init()
  {
    global $_PREFS;
    
    self::$root = new LayoutCollection($_PREFS->getPref('storage.config'));
  }
  
  static function getRootCollection()
  {
    return self::$root;
  }
}

LayoutManager::init();

?>
