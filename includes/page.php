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

class Page extends Resource
{
	var $blocks;
	
	function Page(&$container,$id,$version)
	{
		global $_PREFS;

		$this->Resource($container,$id,$version);
		$this->container=&$container;
		$this->id=$id;
		$this->version=$version;
	
		$this->blocks = array();
	}
	
	function &getResourceWorkingDetails(&$resource)
	{
		return $this->container->getResourceWorkingDetails($this);
	}
	
	function makeNewResourceVersion(&$resource)
	{
		$page=&$this->container->makeNewResourceVersion($this);
		if ($page===false)
		{
			return false;
		}
		else
		{
			return $page->getBlock($resource->id);
		}
	}
	
	function makeResourceWorkingVersion(&$resource)
	{
		$page=&$this->container->makeResourceWorkingVersion($this);
		if ($page===false)
		{
			return false;
		}
		else
		{
			return $page->getBlock($resource->id);
		}
	}

	function getResourceDir(&$resource)
	{
		if (is_a($resource,'Block'))
		{
			return $this->getDir().'/blocks/'.$resource->id;
		}
		if (is_a($resource,'File'))
		{
			return $this->getDir();
		}
	}
	
	function isCurrentResourceVersion(&$resource)
	{
		return $this->container->isCurrentResourceVersion($this);
	}
	
	function makeCurrentResourceVersion(&$resource)
	{
		$this->container->makeCurrentResourceVersion($this);
	}
	
	function &getCurrentResourceVersion(&$resource)
	{
		$page=&$this->container->getCurrentResourceVersion($this);
		if ($page===false)
		{
			return false;
		}
		else
		{
			return $page->getBlock($resource->id);
		}
	}
	
	function getBlockDir($id,$version)
	{
		return $this->getDir().'/blocks/'.$id;
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
	
	function savePreferences()
	{
		$file=$this->openFileWrite('resource.conf');
		$this->prefs->savePreferences($file);
		$this->closeFile($file);
	}
	
	function setBlock($id,&$block)
	{
		$block->setID($id);
		$this->blocks[$id]=&$block;
	}
	
	function isBlock($id=false)
	{
		if ($id===false)
		{
			return false;
		}
		else
		{
			return $this->prefs->isPrefSet('page.blocks.'.$id.'.id');
		}
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
					else if ($block->isCurrentVersion())
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
					$blockobj = &loadBlock($this,$block,$version);
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
		if (strpos($templ,'/')!==false)
		{
			list($container,$id)=explode('/',$templ);
			$cont=&getContainer($container);
		}
		else
		{
			$cont=&$this->container;
			$id=$templ;
		}
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