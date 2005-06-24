<?

/*
 * Swim
 *
 * Defines a block that displays a dynamic menu structure.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class MenuItem
{
	var $parentMenu;
	var $submenu;
	var $url;
	var $text;
	var $log;
	
	function MenuItem()
	{
		$this->log = LoggerManager::getLogger('swim.menu.item');
	}
	
	function display()
	{
		print('<a href="'.$this->url.'">'.$this->text.'</a>');
		if (isset($this->submenu))
		{
			$this->submenu->display(true);
		}
	}
	
	function setAttribute($name, $value)
	{
		$this->log->debug('Setting '.$name.' to '.$value);
		if ($name=='text')
		{
			$this->text=$value;
		}
		else if ($name=='url')
		{
			$this->url=$value;
		}
		else
		{
			$this->log->warn('Attempt to set invalid attribute '.$name);
		}
	}
}

class Menu
{
	var $parentItem;
	var $orientation = 'vertical';
	var $type;
	var $items = array();
	var $log;
	
	function Menu()
	{
		$this->log = LoggerManager::getLogger('swim.menu.menu');
	}
	
	function displayVerticalTableItems()
	{
		foreach (array_keys($this->items) as $k)
		{
			$item=&$this->items[$k];
			print('<tr>'."\n");
			print('<td class="menuitem">'."\n");
			$item->display();
			print('</td>'."\n");
			print('</tr>'."\n");
		}
	}
	
	function displayHorizontalTableItems()
	{
		print('<tr>'."\n");
		foreach (array_keys($this->items) as $k)
		{
			$item=&$this->items[$k];
			print('<td class="menuitem">'."\n");
			$item->display();
			print('</td>'."\n");
		}
		print('</tr>'."\n");
	}
	
	function displayListItems()
	{
		foreach (array_keys($this->items) as $k)
		{
			$item=&$this->items[$k];
			print('<li class="menuitem">'."\n");
			$item->display();
			print('</li>'."\n");
		}
	}
	
	function display($showtags)
	{
		$tag=$this->determineTag();
		if ($showtags)
		{
			print('<'.$tag.' class="menupopup ');
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
		
		if ($tag=='table')
		{
			if ($this->orientation=='horizontal')
			{
				$this->displayHorizontalTableItems();
			}
			else
			{
				$this->displayVerticalTableItems();
			}
		}
		else if ($tag=='ul')
		{
			$this->displayListItems();
		}
		
		if ($showtags)
		{
			print('</'.$tag.'>'."\n");
		}
	}
	
	function determineTag()
	{
		if (isset($this->type))
		{
			return $this->type;
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
	
	function setAttribute($name, $value)
	{
		$this->log->debug('Setting '.$name.' to '.$value);
		if ($name=='orientation')
		{
			$this->orientation=$value;
		}
		else if ($name=='type')
		{
			$this->type=$value;
		}
		else
		{
			$this->log->warn('Attempt to set invalid attribute '.$name);
		}
	}
	
	function addItem(&$item)
	{
		$this->log->debug('Added new item');
		$this->items[]=&$item;
		$item->parentMenu=&$this;
	}
}

class MenuParser extends StackedParser
{
	var $menu;
	var $current;
	
  function onAttribute($name,$value)
  {
  	if (isset($this->current))
  	{
  		$this->current->setAttribute($name,$value);
  	}
  }
  
  function onStartTag($tag)
  {
  	$this->_log->debug('Start Tag: '.$tag);
  	$this->pushStack($tag);
  	if ($tag=='item')
 		{
  		$this->_log->debug('Adding item');
 			$newitem = new MenuItem();
 			$this->current->addItem($newitem);
 			$this->current=&$newitem;
 			return true;
 		}
 		else if ($tag=='menu')
 		{
  		$this->_log->debug('Adding menu');
 			if (!isset($this->current))
 			{
 				$this->_log->info('Adding a menu with no current item');
 			}
 			$newmenu = new Menu($this->current);
			if (isset($this->current))
			{
				$newmenu->parentItem=&$this->current;
				$this->current->submenu=&$newmenu;
			}
			else
			{
 				$this->menu=&$newmenu;
			}
 			$this->current=&$newmenu;
	 		return true;
 		}
  }
  
  function onEndTag($tag)
  {
  	$this->_log->debug('End Tag: '.$tag);
  	$result=$this->popStack();
  	if ($result['tag']=='item')
  	{
  		$this->current=&$this->current->parentMenu;
  	}
  	else if ($result['tag']=='menu')
  	{
  		$this->current=&$this->current->parentItem;
  	}
  }
}

class MenuBlock extends Block
{
	var $rootmenu;
	
	function MenuBlock()
	{
		$this->Block();
	}
	
	function &getBlockEditor()
	{
		return loadPage('internal','menuedit');
	}
	
	function init()
	{
		$file=$this->prefs->getPref('block.menublock.filename','block.xml');
		$parser = new MenuParser();
		//LoggerManager::setLogLevel('',SWIM_LOG_ALL);
		$parser->parseFile($this->getDir().'/'.$file);
		//LoggerManager::setLogLevel('',SWIM_LOG_WARN);
		$this->rootmenu=&$parser->menu;
	}
	
	function displayIntro($attrs)
	{
		if (isset($attrs['class']))
			$attrs['class'].=' menu ';
		else
			$attrs['class']='menu ';
			
		if ($this->rootmenu->orientation=='horizontal')
		{
			$attrs['class'].='horizmenu';
		}
		else
		{
			$attrs['class'].='vertmenu';
		}

		$this->type=$this->rootmenu->determineTag();
		Block::displayIntro($attrs);
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		$this->rootmenu->display(false);
		return true;
	}
}

?>