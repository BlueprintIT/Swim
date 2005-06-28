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
	var $container;
	var $resource;
	var $dir;
	var $prefs;
	var $blocks;
	var $version;
	var $id;
	var $lock;
	var $container;
	var $modified;
	
	function Page(&$container,$id,$version)
	{
		global $_PREFS;

		$this->container=&$container;
		$this->id=$id;
		$this->version=$version;
	
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->resource=$container->getPageResource($id);
		$this->dir=getResourceVersion($this->resource,$this->version);
		
		$this->lockRead();
		$this->prefs->loadPreferences($this->getDir().'/page.conf','page');
		$this->unlock();
	}
	
	function isWritable()
	{
		return $this->container->isWritable();
	}
	
	function isVisible()
	{
		return $this->container->isVisible();
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
	
	function getBlockUsage(&$block)
	{
		$container=$block->container->id;
		$result=array();
		$blocks=$this->prefs->getPrefBranch('page.blocks');
		foreach ($blocks as $key=>$id)
		{
			if ((substr($key,-3,3)=='.id')&&($id==$block->id))
			{
				$blk=substr($key,0,-3);
				$cont=$this->prefs->getPref('page.blocks.'.$blk.'.container');
				if ($cont==$container)
				{
					if (($this->prefs->isPrefSet('page.blocks.'.$blk.'.version'))&&($block->version==$this->prefs->getPref('page.blocks.'.$blk.'.version')))
					{
						$result[]=$blk;
					}
					else if ($block->version==getCurrentVersion($block->getResource()))
					{
						$result[]=$blk;
					}
				}
			}
		}
		return $result;
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

function &getAllPages()
{
	global $_PREFS;
	
	$pages=array();
	$containers=&getAllContainers();
	foreach(array_keys($containers) as $id)
	{
		$container=&$containers[$id];
		$newpages=&$container->getPages();
		$pages=array_merge($pages,$newpages);
	}
	return $pages;
}

?>