<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class BlockLayout
{
  var $prefs;
  
  function BlockLayout($clone=null)
  {
    if ($clone!==null)
    {
      $this->prefs = new Preferences($clone->prefs);
    }
    else
    {
      $this->prefs = new Preferences();
    }
  }
  
  function hasDefaultFiles()
  {
    return false;
  }
  
  function getDefaultFileDir()
  {
    return null;
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
      }
      $el=$el->nextSibling;
    }
  }
}

class PageLayout
{
  var $id;
  var $name;
  var $blocks = array();
  var $prefs;
  
  function PageLayout($id,$clone=null)
  {
    $this->id=$id;
    if ($clone!==null)
    {
      $this->name = $clone->name;
      $this->prefs = new Preferences($clone->prefs);
      foreach ($clone->blocks as $id => $block)
      {
        $blocks[$id]=new BlockLayout($block);
      }
    }
    else
    {
      $this->prefs = new Preferences();
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
  
  function load($element)
  {
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='blocklayout')
        {
          $id=$el->getAttribute('id');
          if ($el->hasAttribute('ref'))
          {
            $this->blocks[$id]=LayoutManager::getBlockLayout($el->getAttribute('ref'));
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
              $this->blocks[$id] = new BlockLayout();
            }
            $this->blocks[$id]->load($el);
          }
        }
        else if ($el->tagName=='preferences')
        {
          $this->prefs->loadFromDOM($el,'',true);
        }
      }
      $el=$el->nextSibling;
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
              $base = self::getPageLayout($el->getAttribute('extends'));
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
