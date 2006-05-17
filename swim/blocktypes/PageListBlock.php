<?

/*
 * Swim
 *
 * Defines a block that just displays html source.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class PageListBlock extends Block
{
	private $cont;
	private $template = 'block.html';
	private $sort;
	
  function PageListBlock($container,$id,$version)
  {
    $this->Block($container,$id,$version);
    if ($this->prefs->isPrefSet('block.sort'))
      $this->sort=$this->prefs->getPref('block.sort');
    if ($this->prefs->isPrefSet('block.template'))
      $this->template=$this->prefs->getPref('block.template');
    if ($this->prefs->isPrefSet('block.container'))
      $this->cont=$this->prefs->getPref('block.container');
    else
      $this->cont=$_PREFS->getPref('container.default');
  }
  
  function findPages($category, $depth, &$pages, &$seen)
  {
    if ($depth==0)
      return;
      
    $this->log->debug('Listing category '.$category->id);
  	$items = $category->items();
  	foreach ($items as $id => $item)
  	{
  		if ($item instanceof Category)
  			$this->findPages($item, $depth-1, $pages, $seen);
  			
  		if (($item instanceof Page) && (!in_array($item,$seen)))
  		{
  			array_push($seen,$item);
  			if (isset($this->sort))
  			{
  				$value = $item->getPref($this->sort);
  				if (isset($pages[$value]))
  				{
  					array_push($pages[$value], $item);
  				}
  				else
  				{
  					$pages[$value] = array($item);
  				}
  			}
  			else
  			{
 		  		array_push($pages,array($item));
		  	}
	  	}
  	}
  }
  
  function displayCategories($parser,$category,$depth)
  {
    if ($depth==0)
      return;
      
    $parser->parseText('<ul id="subcategories" class="menu vertmenu">');
    $items = $category->items();
    
    if (count($items)>0)
    {
      foreach ($items as $item)
      {
        if ($item instanceof Category)
        {
          $parser->parseText('<li class="menuitem">');
          $linked = false;
          if ($item->container->prefs->isPrefSet('categories.customlink'))
          {
            $request = new Request();
            $request->method = 'view';
            $request->resourcePath = $item->container->id.'/categories/'.$item->id;
            $parser->parseText('<a class="category" href="'.$request->encode().'">');
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
                $parser->parseText('<a class="page" href="'.$request->encode().'">');
              }
              else if ($page instanceof Link)
              {
                $parser->parseText('<a class="link" ');
                if ($page->newwindow)
                  $parser->parseText('target="_blank" ');
                $parser->parseText('href="'.$page->address.'">');
              }
            }
            $linked = true;
          }
          if ($item->icon!==null)
            $parser->parseText('<image class="icon" src="'.$item->icon.'"/>');
          else if ($this->prefs->isPrefSet('block.defaulticon'))
            $parser->parseText('<image class="icon" src="'.$this->prefs->getPref('block.defaulticon').'"/>');
          
          if ($item->hovericon!==null)
            $parser->parseText('<image class="hoverIcon" src="'.$item->hovericon.'"/>');
          else if ($item->icon!==null)
            $parser->parseText('<image class="hoverIcon" src="'.$item->icon.'"/>');
          else if ($this->prefs->isPrefSet('block.defaulticon'))
            $parser->parseText('<image class="hoverIcon" src="'.$this->prefs->getPref('block.defaulticon').'"/>');
          
          $parser->parseText('<span>'.$item->name.'</span>');
          if ($linked)
            $parser->parseText('</a>');
          
          if ($this->prefs->getPref('block.subcategories',false))
            $this->displayCategories($parser,$item,$depth-1);

          $parser->parseText('</li>');
        }
      }
    }
    $parser->parseText('</ul>');
  }
  
  function displayContent($parser,$attrs,$text)
  {
    $parser->parseText('<script src="/internal/file/yahoo/YAHOO.js"/>');
    $parser->parseText('<script src="/internal/file/scripts/BlueprintIT.js"/>');
    $parser->parseText('<script src="/internal/file/yahoo/event.js"/>');
    $parser->parseText('<script src="/internal/file/yahoo/dom.js"/>');
    $parser->parseText('<script src="/internal/file/scripts/dom.js"/>');
    $parser->parseText('<script src="/internal/file/scripts/bpmm.js"/>');

    $cm = getContainer($this->cont);
    if (isset($parser->data['request']->data['category']))
      $category = $parser->data['request']->data['category'];
    else
      $category = $cm->getRootCategory();

    $this->displayCategories($parser,$category,$this->prefs->getPref('block.categorydepth',0));
    
    $parser->parseText('<script type="text/javascript">menuManager.loadFrom(document.getElementById("subcategories"));</script>');
    
    if ($this->fileIsReadable($this->template))
    {
	    $realpage = $parser->data['page'];
	    
	    $pages = array();
	    $seen = array();
	    $this->findPages($category, $this->prefs->getPref('block.pagedepth',-1), $pages, $seen);
	    
	    foreach ($pages as $pagearray)
	    {
	    	foreach ($pagearray as $page)
	    	{
	    		$this->log->debug('Displaying page '.$page->id);
	    		$parser->data['page'] = $page;
		      $file = $this->openFileRead($this->template);
		      $text=stream_get_contents($file);
		      $this->closeFile($file);
		      $parser->parseText($text);
		   	}
	    }
	    $parser->data['page'] = $realpage;
    }
    else
    {
    	$this->log->warn('Unable to access block template file '.$this->template);
    }
    
    return true;
  }
}

?>