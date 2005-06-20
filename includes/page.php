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
	var $template;
	var $prefs;
	var $blocks;
	var $version;
	var $id;
	var $lock;
	var $resource;
	var $dir;
	var $container;
	var $modified;
	
	function Page($container,$id,$version=false)
	{
		global $_PREFS;
	
		$this->container=$container;
		$this->id=$id;
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->resource=$this->prefs->getPref('storage.pages.'.$this->container).'/'.$this->id;
		if ($version===false)
		{
			$version=getCurrentVersion($this->resource);
		}
		$this->version=$version;
		$this->dir=getResourceVersion($this->resource,$this->version);
		
		$this->lockRead();
		$this->prefs->loadPreferences($this->getDir().'/page.conf','page');
		$this->unlock();
	}
	
	function display(&$request)
	{
		$this->getTemplate();
		$this->template->display($request,$this);
	}
	
	function displayAdmin(&$request)
	{
		$this->getTemplate();
		$this->template->displayAdmin($request,$this);
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
	
	function saveProperties()
	{
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
		
	function getBlock($id)
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
					$container=$this;
					$version=$this->version;
				}
				else
				{
					if ($this->prefs->isPrefSet($blockpref.'.version'))
					{
						$version=$this->prefs->getPref($blockpref.'.version');
					}
					else
					{
						$version=false;
					}
				}
				
				$blockobj = &loadBlock($container,$block,$version);
				
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
		if (!isset($this->template))
		{
			// Find the page's template or use the default
			$templ=$this->prefs->getPref('page.template');
			$this->template=&loadTemplate($templ);
			$this->prefs->setParent($this->template->prefs);
		}
		return $this->template;
	}
}

function isValidPage($container,$id,$version=false)
{
	global $_PREFS;
	
	$log=&LoggerManager::getLogger('swim.page');
	
	if (!($_PREFS->isPrefSet('storage.pages.'.$container)))
	{
		return false;
	}
	
	$basedir=$_PREFS->getPref('storage.pages.'.$container).'/'.$id;
	$log->debug('Page storage is '.$basedir);
	if (is_dir($basedir))
	{
		if ($version===false)
		{
			$version=getCurrentVersion($basedir);
			$log->debug('Found default version of '.$version);
		}
		if ((is_dir($basedir.'/'.$version))&&(is_readable($basedir.'/'.$version.'/page.conf')))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		$log->debug('Invalid storage location');
		return false;
	}
}

?>