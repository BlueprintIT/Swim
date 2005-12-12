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
    {
      $attrs['class'].='horizmenu';
    }
    else
    {
      $attrs['class'].='vertmenu';
    }

    Block::displayIntro($attrs);
  }
  
  function displayVerticalTableItems($category,$depth,$showroot=true)
  {
    if ($showroot)
    {
      print('<table class="menupopup ');
      if ($this->orientation=='horizontal')
      {
        print('horizmenu');
      }
      else
      {
        print('vertmenu');
      }
      print('">'."\n");
    }
    
    foreach ($category->items() as $item)
    {
      print('<tr>'."\n");
      print('<td class="menuitem level'.($depth+1).'">'."\n");
      $this->displayItem($item,$depth);
      print('</td>'."\n");
      print('</tr>'."\n");
    }
    
    if ($showroot)
      print('</table>');
  }
  
  function displayHorizontalTableItems($category,$depth,$showroot=true)
  {
    if ($showroot)
    {
      print('<table class="menupopup ');
      if ($this->orientation=='horizontal')
      {
        print('horizmenu');
      }
      else
      {
        print('vertmenu');
      }
      print('">'."\n");
    }
    
    print('<tr>'."\n");
    foreach ($category->items() as $item)
    {
      print('<td class="menuitem level'.($depth+1).'">'."\n");
      $this->displayItem($item,$depth);
      print('</td>'."\n");
    }
    print('</tr>'."\n");
    
    if ($showroot)
      print('</table>');
  }
  
  function displayListItems($category,$depth,$showroot=true)
  {
    if ($showroot)
    {
      print('<ul class="menupopup ');
      if ($this->orientation=='horizontal')
      {
        print('horizmenu');
      }
      else
      {
        print('vertmenu');
      }
      print('">'."\n");
    }
    
    foreach ($category->items() as $item)
    {
      print('<li class="menuitem level'.($depth+1).'">'."\n");
      $this->displayItem($item,$depth);
      print('</li>'."\n");
    }
    
    if ($showroot)
      print('</ul>');
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
      $page = $item->getDefaultPage();
      if ($page!==null)
      {
        print('<anchor href="/'.$page->getPath().'">'.$item->name.'</anchor>');
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
      print('<anchor href="/'.$item->getPath().'">'.$item->prefs->getPref('page.variables.title').'</anchor>');
    }
    else
    {
      print('<a target="_blank" href="'.$item.'">'.$item.'</a>');
    }
  }
  
  function displayContent($parser,$attrs,$text)
  {
    print('<script src="/global/file/scripts/cbdom.js"/>');
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