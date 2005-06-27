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

class Page
{
	var $prefs;
	var $blocks;
	var $version;
	var $id;
	var $lock;
	var $resource;
	var $dir;
	var $container;
	var $modified;
	
	function Page($container,$id,$version)
	{
		global $_PREFS;
	
		$this->container=&$container;
		$this->id=$id;
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->resource=$container->getPageResource($id);
		$this->version=$version;
		$this->dir=getResourceVersion($this->resource,$this->version);
		
		$this->lockRead();
		$this->prefs->loadPreferences($this->getDir().'/page.conf','page');
		$this->unlock();
	}
	
	function display(&$request)
	{
		$template=&$this->getTemplate();
		$template->display($request,$this);
	}
	
	function displayAdmin(&$request)
	{
		$template=&$this->getTemplate();
		$template->displayAdmin($request,$this);
	}
	
	function getDir()
	{
		return $this->dir;
	}
	
	function getResource()
	{
		return $this->resource;
	}
	
	function lockRead()
	{
		$this->lock=lockResourceRead($this->getDir());
	}
	
	function lockWrite()
	{
		$this->lock=lockResourceWrite($this->getDir());
	}
	
	function unlock()
	{
		unlockResource($this->lock);
	}
	
	function savePreferences()
	{
		$this->lockWrite();
		$this->prefs->savePreferences($this->getDir().'/page.conf');
		$this->unlock();
	}
	
	function setBlock($id,&$block)
	{
		$block->setID($id);
		$this->blocks[$id]=&$block;
	}
	
	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			$stat=stat($this->getDir().'/page.conf');
			$this->modified=$stat['mtime'];
		}
		return $this->modified;
	}
	
	function isBlock($id)
	{
		return $this->prefs->isPrefSet('page.blocks.'.$id.'.id');
	}
	
	function &getBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref='page.blocks.'.$id;
			if ($this->prefs->isPrefSet($blockpref.'.id'))
			{
				$container=$this->prefs->getPref($blockpref.'.container');
				$block=$this->prefs->getPref($blockpref.'.id');
				if ($container=='page')
				{
					$version=$this->version;
					$blockobj = &loadBlock($this->getDir().'/blocks/'.$block,$this,$block,$version);
				}
				else
				{
					$container=&getContainer($container);
					if ($this->prefs->isPrefSet($blockpref.'.version'))
					{
						$version=$this->prefs->getPref($blockpref.'.version');
					}
					else
					{
						$version=false;
					}
					$blockobj=&$container->getBlock($block,$version);
				}
				$this->blocks[$id]=&$blockobj;
			}
			else
			{
				$this->blocks[$id]=null;
			}
		}
		return $this->blocks[$id];
	}
	
	function &getTemplate()
	{
		$templ=$this->prefs->getPref('page.template');
		list($container,$id)=explode('/',$templ);
		$cont=&getContainer($container);
		$template=&$cont->getTemplate($id);
		return $template;
	}
}

/*function &getPages($container)
{
	global $_PREFS;
	
	$pages=array();
	if ($_PREFS->isPref('storage.pages.'.$container))
	{
		$dir=$_PREFS->getPref('storage.pages.'.$container);
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$page=&loadPage($container,$entry);
				if ($page!==null)
				{
					$pages[]=&$page;
				}
			}
		}
	}
	return $pages;
}

function &getAllPages()
{
	global $_PREFS;
	
	$stores=$_PREFS->getPrefBranch('storage.pages');
	$pages=array();
	foreach ($stores as $container => $path)
	{
		$pages=array_marge($pages,getPages($container));
	}
	return $pages;
}*/

?>