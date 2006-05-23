<?

/*
 * Swim
 *
 * Defines a block that displays a dynamic menu structure based on the site structure.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class CategoryMenuBlock extends Block
{
  var $maxdepth = 2;
  var $root;
  var $orientation = 'vertical';
  
  function CategoryMenuBlock($container,$id,$version)
  {
    $this->Block($container,$id,$version);
    if ($this->prefs->isPrefSet('block.maxdepth'))
      $this->maxdepth=$this->prefs->getPref('block.maxdepth');
    if ($this->prefs->isPrefSet('block.root'))
      $this->root=$this->prefs->getPref('block.root');
    if ($this->prefs->isPrefSet('block.orientation'))
      $this->orientation=$this->prefs->getPref('block.orientation');
  }
  
  function getModifiedDate()
  {
    $cm = getCategoryManager('website');
    return $cm->getModifiedDate();
  }
  
  function getType()
  {
    if ($this->prefs->isPrefSet('block.type'))
    {
      return $this->prefs->getPref('block.type');
    }
    if ($this->orientation=='horizontal')
    {
      return 'table';
    }
    else
    {
      return 'ul';
    }
  }
  
  function displayIntro($attrs)
  {
    if (isset($attrs['class']))
      $attrs['class'].=' menu ';
    else
      $attrs['class']='menu ';
      
    if ($this->orientation=='horizontal')
      $attrs['class'].='horizmenu';
    else
      $attrs['class'].='vertmenu';

    if ($this->prefs->isPrefSet('block.animation'))
      $attrs['class'].=' '.$this->prefs->getPref('block.animation');
      
    Block::displayIntro($attrs);
  }
  
  function displayTableItem($item,$depth)
  {
    print('<td class="menuitem">'."\n");
    $this->displayItem($item,$depth);
    print('</td>'."\n");
  }
  
  function displayVerticalTableItem($item,$depth)
  {
    print('<tr>'."\n");
    $this->displayTableItem($item,$depth);
    print('</tr>'."\n");
  }
  
  function displayVerticalTableItems($category,$depth,$showroot=true)
  {
    if ($showroot)
    {
      print('<table class="menupopup vertmenu">'."\n");
    }
    
    foreach ($category->items() as $item)
    {
      $this->displayVerticalTableItem($item,$depth);
    }
    
    if ($showroot)
      print('</table>');
  }
  
  function displayHorizontalTableItem($item,$depth)
  {
    $this->displayTableItem($item,$depth);
  }
  
  function displayHorizontalTableItems($category,$depth,$showroot=true)
  {
    if ($showroot)
    {
      print('<table class="menupopup horizmenu">'."\n");
    }
    
    print('<tr>'."\n");
    foreach ($category->items() as $item)
    {
      $this->displayHorizontalTableItem($item,$depth);
    }
    print('</tr>'."\n");
    
    if ($showroot)
      print('</table>');
  }
  
  function displayListItem($item,$depth)
  {
    print('<li class="menuitem">'."\n");
    $this->displayItem($item,$depth);
    print('</li>'."\n");
  }
  
  function displayListItems($category,$depth,$showroot=true)
  {
    $items = $category->items();
    
    if (count($items)>0)
    {
      if ($showroot)
      {
        print('<ul class="menupopup vertmenu">'."\n");
      }
      
      foreach ($items as $item)
      {
        $this->displayListItem($item,$depth);
      }
      
      if ($showroot)
        print('</ul>');
    }
  }
  
  function displayItems($category,$depth,$showroot=true)
  {
    $tag=$this->getType();
    if ($tag=='table')
    {
      if ($this->orientation=='horizontal')
      {
        $this->displayHorizontalTableItems($category,$depth,$showroot);
      }
      else
      {
        $this->displayVerticalTableItems($category,$depth,$showroot);
      }
    }
    else if ($tag=='ul')
    {
      $this->displayListItems($category,$depth,$showroot);
    }
  }
  
  function displayItem($item,$depth)
  {
    if ($item instanceof Category)
    {
      $page = $item->getDefaultItem();
      if ($page!==null)
      {
        if ($page instanceof Page)
        {
          print('<anchor class="page" href="/'.$page->getPath().'">');
          print('<span>'.$item->name.'</span>');
          print('</anchor>');
        }
        else if ($page instanceof Link)
        {
          print('<a class="link" ');
          if ($this->prefs->getPref('block.newwindowlink', true))
            print('target="_blank" ');
          print('href="'.$page->address.'">');
          print('<span>'.$item->name.'</span>');
          print('</a>');
        }
      }
      else
      {
        print($item->name);
      }
      if ($this->maxdepth>$depth)
        $this->displayListItems($item,$depth+1);
    }
    else if ($item instanceof Page)
    {
      print('<anchor class="page" href="/'.$item->getPath().'">');
      print('<span>'.$item->prefs->getPref('page.variables.title').'</span>');
      print('</anchor>');
    }
    else if ($item instanceof Link)
    {
      print('<a class="link" ');
      if ($this->prefs->getPref('block.newwindowlink', true))
        print('target="_blank" ');
      print('href="'.$item->address.'">');
      print('<span>'.$item->name.'</span>');
      print('</a>');
    }
  }
  
  function displayContent($parser,$attrs,$text)
  {
    print('<script src="/global/file/yahoo/YAHOO.js"/>');
    print('<script src="/global/file/yahoo/dom.js"/>');
    print('<script src="/global/file/yahoo/event.js"/>');
    print('<script src="/global/file/scripts/BlueprintIT.js"/>');
    print('<script src="/global/file/scripts/dom.js"/>');
    print('<script src="/global/file/scripts/bpmm.js"/>');
    
    $cm = getCategoryManager('website');
    
    if ((isset($this->root))&&($this->root<=0))
    {
      $cats=$cm->getPageCategories($parser->data['page']);
      if (count($cats)>0)
      {
        $root=$cats[0];
        $pos=$this->root;
        while (($root->parent!==null)&&($pos<0))
        {
          $pos++;
          $root=$root->parent;
        }
      }
      else
      {
        $root = $cm->getRootCategory();
      }
    }
    else
    {
      $root = $cm->getRootCategory();
    }
    $this->displayItems($root,0,false);
    
    return true;
  }
}

?>