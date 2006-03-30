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
	private $recursive = false;
	private $template = 'block.html';
	private $sort;
	
  function PageListBlock($container,$id,$version)
  {
    $this->Block($container,$id,$version);
    if ($this->prefs->isPrefSet('block.recursive'))
      $this->recursive=$this->prefs->getPref('block.recursive');
    if ($this->prefs->isPrefSet('block.sort'))
      $this->sort=$this->prefs->getPref('block.sort');
    if ($this->prefs->isPrefSet('block.template'))
      $this->template=$this->prefs->getPref('block.template');
    if ($this->prefs->isPrefSet('block.container'))
      $this->cont=$this->prefs->getPref('block.container');
    else
      $this->cont=$_PREFS->getPref('container.default');
  }
  
  function findPages($category, &$pages)
  {
    $this->log->debug('Listing category '.$category->id);
  	$items = $category->items();
  	foreach ($items as $id => $item)
  	{
  		if (($item instanceof Category) && ($this->recursive))
  			$this->findPages($item, $pages);
  			
  		if ($item instanceof Page)
  		{
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
  
  function displayContent($parser,$attrs,$text)
  {
    if ($this->fileIsReadable($this->template))
    {
    	$cm = getContainer($this->cont);
	    if (isset($parser->data['request']->query['category']))
	    {
	    	$category = $cm->getCategory($parser->data['request']->query['category']);
	    }
	    else
	    {
	    	$category = $cm->getRootCategory();
	    }
	    $realpage = $parser->data['page'];
	    
	    $pages = array();
	    $this->findPages($category, $pages);
	    
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