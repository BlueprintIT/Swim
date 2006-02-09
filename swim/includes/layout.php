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
  
  function Layout($id,$clone=null)
  {
    $this->id = $id;
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
  
  function BlockLayout($id,$clone=null)
  {
    $this->Layout($id,$clone);
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

class PageLayout extends Layout
{
  var $blocks = array();
  
  function PageLayout($id,$clone=null)
  {
    $this->Layout($id,$clone);
    if ($clone!==null)
    {
      foreach ($clone->blocks as $id => $block)
      {
        $this->blocks[$id]=new BlockLayout($id,$block);
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
        $this->blocks[$id] = new BlockLayout($id,LayoutManager::getBlockLayout($el->getAttribute('ref')));
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
          $this->blocks[$id] = new BlockLayout($id);
        }
      }
      $this->blocks[$id]->load($el);
    }
  }
}

class LayoutManager
{
  private static $pages = array();
  private static $blocks = array();
  
  static function init()
  {
    global $_PREFS;
    
    $log=LoggerManager::getLogger('swim.layout');
    $log->debug('Layout startup.');
    $doc = new DOMDocument();
    if ($doc->load($_PREFS->getPref('storage.config').'/layouts.xml'))
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
              $log->debug('Creating page layout '.$id.' That extends another.');
              $base = self::getPageLayout($el->getAttribute('extends'));
              $log->debug('Extends '.$base->getName());
              $layout = new PageLayout($id,$base);
            }
            else
            {
              $layout = new PageLayout($id);
            }
            self::$pages[$id]=$layout;
            $layout->load($el);
          }
          else if ($el->tabName=='blocklayout')
          {
            $id = $el->getAttribute('id');
            if ($el->hasAttribute('extends'))
            {
              $base = self::getBlockLayout($el->getAttribute('extends'));
              $layout = new BlockLayout($id,$base);
            }
            else
            {
              $layout = new BlockLayout($id);
            }
            self::$blocks[$id]=$layout;
            $layout->load($el);
          }
        }
        $el=$el->nextSibling;
      }
    }
    else
    {
      $log->error('Unable to load layouts template');
    }
  }
  
  static function getPageLayouts()
  {
    return self::$pages;
  }
  
  static function getPageLayout($id)
  {
    if (isset(self::$pages[$id]))
    {
      return self::$pages[$id];
    }
    else
    {
      return null;
    }
  }
  
  static function getBlockLayouts()
  {
    return self::$blocks;
  }
  
  static function getBlockLayout($id)
  {
    if (isset(self::$blocks[$id]))
    {
      return self::$blocks[$id];
    }
    else
    {
      return null;
    }
  }
}

LayoutManager::init();

?>
