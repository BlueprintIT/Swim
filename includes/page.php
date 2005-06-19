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
	var $container;
	
	function Page($container,$id,$version)
	{
		global $_PREFS;
		
		$this->container=$container;
		$this->version=$version;
		$this->id=$id;
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
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
		return getResourceVersion($this->prefs->getPref('storage.pages.'.$this->container).'/'.$this->id,$this->version);
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
	
	function setBlock($id,&$block)
	{
		$block->setPage($this);
		$this->blocks[$id]=&$block;
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
					$blockdir=$this->getDir().'/blocks/'.$block;
				}
				else if ($this->prefs->isPrefSet('storage.blocks.'.$container))
				{
					if ($this->prefs->isPrefSet($blockpref.'.version'))
					{
						$version=$this->prefs->getPref($blockpref.'.version');
						$blockdir=getResourceVersion($this->prefs->getPref('storage.blocks.'.$container).'/'.$block,$version);
					}
					else
					{
						$blockdir=getCurrentResource($this->prefs->getPref('storage.blocks.'.$container).'/'.$block);
					}
				}
				else
				{
					trigger_error('Block container not set');
				}
				
				$blockobj = &loadBlock($block,$blockdir);
				$blockobj->setPage($this);
				$blockobj->setContainer($container);
				
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