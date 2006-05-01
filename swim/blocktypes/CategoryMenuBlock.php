<?

/*
 * Swim
 *
 * Defines a block that displays a dynamic menu structure based on the site structure.
 *
 * Copyright Blueprint IT Ltd. 2006
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
  var $cont;
  
  function CategoryMenuBlock($container,$id,$version)
  {
    global $_PREFS;
    
    $this->Block($container,$id,$version);
    if ($this->prefs->isPrefSet('block.maxdepth'))
      $this->maxdepth=$this->prefs->getPref('block.maxdepth');
    if ($this->prefs->isPrefSet('block.root'))
      $this->root=$this->prefs->getPref('block.root');
    if ($this->prefs->isPrefSet('block.orientation'))
      $this->orientation=$this->prefs->getPref('block.orientation');
    if ($this->prefs->isPrefSet('block.container'))
      $this->cont=$this->prefs->getPref('block.container');
    else
      $this->cont=$_PREFS->getPref('container.default');
  }
  
  function getModifiedDate()
  {
    $cm = getContainer($this->cont);
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
  
  function displayTableItem($item,$parent,$depth)
  {
    print('<td class="menuitem">'."\n");
    $this->displayItem($item,$parent,$depth);
    print('</td>'."\n");
  }
  
  function displayVerticalTableItem($item,$parent,$depth)
  {
    print('<tr>'."\n");
    $this->displayTableItem($item,$parent,$depth);
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
      $this->displayVerticalTableItem($item,$category,$depth);
    }
    
    if ($showroot)
      print('</table>');
  }
  
  function displayHorizontalTableItem($item,$parent,$depth)
  {
    $this->displayTableItem($item,$parent,$depth);
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
      $this->displayHorizontalTableItem($item,$category,$depth);
    }
    print('</tr>'."\n");
    
    if ($showroot)
      print('</table>');
  }
  
  function displayListItem($item,$parent,$depth)
  {
    print('<li class="menuitem">'."\n");
    $this->displayItem($item,$parent,$depth);
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
        $this->displayListItem($item,$category,$depth);
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
    else if ($tag=='ul' || $depth>0)
    {
      $this->displayListItems($category,$depth,$showroot);
    }
  }
  
  function displayItem($item,$parent,$depth)
  {
    if ($item instanceof Category)
    {
      $linked = false;
      if ($item->container->prefs->isPrefSet('categories.customlink'))
      {
        $request = new Request();
        $request->method = 'view';
        $request->resource = $item->container->prefs->getPref('categories.customlink');
        $request->query['category'] = $item->id;
        print('<a class="page" href="'.$request->encode().'">');
        $linked = true;
      }
      else
      {
        $page = $item->getDefaultItem();
        if ($page!==null)
        {
          if ($page instanceof Page)
          {
            $request = new Request();
            $request->method = 'view';
            $request->resource = $page;
            print('<a class="page" href="'.$request->encode().'">');
          }
          else if ($page instanceof Link)
          {
            print('<a class="link" ');
            if ($page->newwindow)
  	          print('target="_blank" ');
            print('href="'.$page->address.'">');
          }
        }
        $linked = true;
      }
      if ($item->icon!==null)
        print('<image class="icon" src="'.$item->icon.'"/>');
      else if ($this->prefs->isPrefSet('block.defaulticon'))
        print('<image class="icon" src="'.$this->prefs->getPref('block.defaulticon').'"/>');
      
      if ($item->hovericon!==null)
        print('<image class="hoverIcon" src="'.$item->hovericon.'"/>');
      else if ($item->icon!==null)
        print('<image class="hoverIcon" src="'.$item->icon.'"/>');
      else if ($this->prefs->isPrefSet('block.defaulticon'))
        print('<image class="hoverIcon" src="'.$this->prefs->getPref('block.defaulticon').'"/>');
      
      print('<span>'.$item->name.'</span>');
      if ($linked)
        print('</a>');

      if ($this->maxdepth>$depth)
        $this->displayListItems($item,$depth+1);
    }
    else if ($item instanceof Page)
    {
      $request = new Request();
      $request->method = 'view';
      $request->resource = $item;
      $request->data['category'] = $parent;
      print('<a class="page" href="'.$request->encode().'"><span>'.$item->prefs->getPref('page.variables.title').'</span></a>');
    }
    else if ($item instanceof Link)
    {
      print('<a class="link" target="_blank" href="'.$item->address.'"><span>'.$item->name.'</span></a>');
    }
  }
  
  function displayContent($parser,$attrs,$text)
  {
    print('<script src="/internal/file/yahoo/YAHOO.js"/>');
    print('<script src="/internal/file/scripts/BlueprintIT.js"/>');
    print('<script src="/internal/file/yahoo/event.js"/>');
    print('<script src="/internal/file/yahoo/dom.js"/>');
    print('<script src="/internal/file/scripts/dom.js"/>');
    print('<script src="/internal/file/scripts/bpmm.js"/>');
    
    $this->log->debug('Displaying category menu');
    
    $cm = getContainer($this->cont);
    
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
    $this->log->debug('Got root category');
    
    $this->displayItems($root,0,false);
    
    print('<script type="text/javascript">menuManager.loadFrom(document.getElementById("'.$attrs['id'].'"));</script>');
    
    return true;
  }
}

?>