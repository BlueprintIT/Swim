<?

/*
 * Swim
 *
 * Containers hold the pages, blocks, templates and files that the website can serve
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Container extends Resource
{
	var $log;
	
	var $templates = array();
	var $pages = array();
	var $blocks = array();
	var $working = array();
	
	function Container($id)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.container');
		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->dir=$this->prefs->getPref('container.'.$id.'.basedir');
		$this->log->debug('Container '.$id.' is at '.$this->dir);
		if (is_readable($this->getDir().'/resource.conf'))
		{
			$file=fopen($this->getDir().'/resource.conf','r');
			if ($file!==false)
			{
				$this->prefs->loadPreferences($file);
				fclose($file);
			}
		}
	}
	
	function getPath()
	{
		return $this->id;
	}
	
	function fileIsWritable($filename)
	{
		return false;
	}
	
	function fileIsReadable($filename)
	{
		return (is_readable($this->getDir().'/'.$filename));
	}
	
	function getCurrentVersion($dir)
	{
		if ($this->isWritable())
		{
			$lock=lockResourceRead($dir);
		}

		$vers=fopen($dir.'/version','r');
		$version=fgets($vers);
		fclose($vers);

		if ($this->isWritable())
		{
			unlockResource($lock);
		}
		
		return $version;
	}
	
	function &getResourceVersions(&$resource)
	{
		$versions=array();

		if (is_a($resource,'Page'))
		{
			$dir=$this->getDir().'/pages/'.$resource->id;
		}
		else if (is_a($resource,'Block'))
		{
			$dir=$this->getDir().'/blocks/'.$resource->id;
		}
		else if (is_a($resource,'Template'))
		{
			$dir=$this->getDir().'/templates/'.$resource->id;
		}

		if ($res=@opendir($dir))
		{
			while (($file=readdir($res))!== false)
			{
				if (!(substr($file,0,1)=='.'))
				{
					if ((is_dir($dir.'/'.$file))&&(is_numeric($file)))
					{
						if (is_a($resource,'Page'))
						{
							$versions[$file] = $this->getPage($resource->id,$file);
						}
						else if (is_a($resource,'Block'))
						{
							$versions[$file] = $this->getBlock($resource->id,$file);
						}
						else if (is_a($resource,'Template'))
						{
							$versions[$file] = $this->getTemplate($resource->id,$file);
						}
					}
				}
			}
			closedir($res);
		}
		
		return $versions;
	}
	
	function &getResourceWorkingDetails(&$resource)
	{
		global $_USER;
		
		if (is_a($resource,'Page'))
		{
			$type='page';
			$dir=$this->getDir().'/pages/'.$resource->id;
		}
		else if (is_a($resource,'Block'))
		{
			$type='block';
			$dir=$this->getDir().'/blocks/'.$resource->id;
		}
		else if (is_a($resource,'Template'))
		{
			$type='template';
			$dir=$this->getDir().'/templates/'.$resource->id;
		}
		
		if (!isset($this->working[$type][$resource->id]))
		{
			$this->working[$type][$resource->id] = new WorkingDetails($this,$resource->id,$this->prefs->getPref('version.working'),$dir.'/'.$this->prefs->getPref('version.working'));
		}
		return $this->working[$type][$resource->id];
	}
	
	function &makeNewResourceVersion(&$resource)
	{
		if (is_a($resource,'Page'))
		{
			$dir=$this->getDir().'/pages/'.$resource->id;
		}
		else if (is_a($resource,'Block'))
		{
			$dir=$this->getDir().'/blocks/'.$resource->id;
		}
		else if (is_a($resource,'Template'))
		{
			$dir=$this->getDir().'/templates/'.$resource->id;
		}
		
		$lock=fopen($dir.'/'.$this->prefs->getPref('locking.lockfile'),'a');
		flock($lock,LOCK_EX);
		
		$newest=-1;
		if ($res=@opendir($dir))
		{
			while (($file=readdir($res))!== false)
			{
				if (!(substr($file,0,1)=='.'))
				{
					if ((is_dir($dir.'/'.$file))&&(is_numeric($file)))
					{
						if ($file>$newest)
						{
							$newest=$file;
						}
					}
				}
			}
			closedir($res);
		}
		if ($newest>=0)
		{
			$next=$newest+1;
		}
		else
		{
			$next=1;
		}
		
		mkdir($dir.'/'.$next);
	
		flock($lock,LOCK_UN);
		fclose($lock);
		
		$source=$resource->getDir();
		$target=$dir.'/'.$next;
		$resource->lockRead();
		$lock=lockResourceWrite($target);
		recursiveCopy($source,$target,true);
		unlockResource($lock);
		$resource->unlock();

		if (is_a($resource,'Page'))
		{
			return $this->getPage($resource->id,$next);
		}
		else if (is_a($resource,'Block'))
		{
			return $this->getBlock($resource->id,$next);
		}
		else if (is_a($resource,'Template'))
		{
			return $this->getTemplate($resource->id,$next);
		}
	}
	
	function &makeResourceWorkingVersion(&$resource)
	{
		$source=$resource->getDir();
		$details=&$resource->getWorkingDetails();
		if ($details->isNew())
		{
			$resource->lockRead();
			$lock=lockResourceWrite($details->getDir());
			recursiveCopy($source,$details->getDir(),true);
			unlockResource($lock);
			$resource->unlock();
		}

		if (is_a($resource,'Page'))
		{
			return $this->getPage($resource->id,$details->version);
		}
		else if (is_a($resource,'Block'))
		{
			return $this->getBlock($resource->id,$details->version);
		}
		else if (is_a($resource,'Template'))
		{
			return $this->getTemplate($resource->id,$details->version);
		}
	}
	
	function isCurrentResourceVersion(&$resource)
	{
		$current=&$this->getCurrentResourceVersion($resource);
		return ($current->version==$resource->version);
	}
	
	function makeCurrentResourceVersion(&$resource)
	{
		if (is_a($resource,'Page'))
		{
			$dir=$this->getDir().'/pages/'.$resource->id;
		}
		else if (is_a($resource,'Block'))
		{
			$dir=$this->getDir().'/blocks/'.$resource->id;
		}
		else if (is_a($resource,'Template'))
		{
			$dir=$this->getDir().'/templates/'.$resource->id;
		}

		if ($this->isWritable())
		{
			$lock=lockResourceWrite($dir);
		}
		$vers=fopen($dir.'/version','w');
		fwrite($vers,$resource->version);
		fclose($vers);
		if ($this->isWritable())
		{
			unlockResource($lock);
		}
	}
	
	function &getCurrentResourceVersion(&$resource)
	{
		if (is_a($resource,'Page'))
		{
			$dir=$this->getDir().'/pages/'.$resource->id;
		}
		else if (is_a($resource,'Block'))
		{
			$dir=$this->getDir().'/blocks/'.$resource->id;
		}
		else if (is_a($resource,'Template'))
		{
			$dir=$this->getDir().'/templates/'.$resource->id;
		}

		$version=$this->getCurrentVersion($dir);

		if (is_a($resource,'Page'))
		{
			return $this->getPage($resource->id,$version);
		}
		else if (is_a($resource,'Block'))
		{
			return $this->getBlock($resource->id,$version);
		}
		else if (is_a($resource,'Template'))
		{
			return $this->getTemplate($resource->id,$version);
		}
	}
	
	function isVisible()
	{
		return $this->prefs->getPref('container.visible',true);
	}
	
	function isWritable()
	{
		return $this->prefs->getPref('container.writable',true);
	}
	
	function getResourceDir(&$resource)
	{
		if (is_a($resource,'Page'))
		{
			return $this->dir.'/pages/'.$resource->id.'/'.$resource->version;
		}
		if (is_a($resource,'Block'))
		{
			return $this->getBlockDir($resource->id,$resource->version);
		}
		if (is_a($resource,'Template'))
		{
			return $this->dir.'/templates/'.$resource->id.'/'.$resource->version;
		}
		if (is_a($resource,'File'))
		{
			return $this->dir.'/files';
		}
	}
	
	function getBlockDir($id,$version)
	{
		return $this->getDir().'/blocks/'.$id.'/'.$version;
	}
	
	function &getBlock($id,$version=false)
	{
		$this->log->debug('Getting block '.$id.' '.$version);
		if ($version===false)
		{
			$version=$this->getCurrentVersion($this->getDir().'/blocks/'.$id);
		}
		if (!isset($this->blocks[$id][$version]))
		{
			$block = &loadBlock($this,$id,$version);
			if (($block!==false)&&($block->exists()))
			{
				$this->blocks[$id][$version]=&$block;
			}
			else
			{
				$this->log->debug('Failed');
				$this->blocks[$id][$version]=false;
			}
		}
		return $this->blocks[$id][$version];
	}

	function &getBlocks()
	{
		$blocks=array();
		$dir=$this->dir.'/blocks';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$block=&$this->getBlock($entry);
				if ($block!==false)
				{
					$blocks[]=&$block;
				}
			}
		}
		closedir($dir);
		return $blocks;
	}
	
	function &createPage(&$layout, $id=false)
	{
		$this->lockWrite();
		if ($id===false)
		{
			do
			{
				$id=rand(10000,99999);
			} while (is_dir($this->getDir().'/pages/'.$id));
		}
		mkdir($this->getDir().'/pages/'.$id);
		$version=1;
		$pdir=$this->getDir().'/pages/'.$id.'/'.$version;
		mkdir($pdir);
		$this->unlock();
		if ($layout===false)
		{
			$layout=&getLayout($this->prefs->getPref('layouts.default'));
		}
		$lock=lockResourceWrite($pdir);
		recursiveCopy($layout->getDir(),$pdir,true);
		unlockResource($lock);

		$newpage=&$this->getPage($id,$version);
		$newpage->makeCurrentVersion();

		return $newpage;
	}
	
	function &getPage($id,$version=false)
	{
		if ($version===false)
		{
			$version=$this->getCurrentVersion($this->getDir().'/pages/'.$id);
		}
		if (!isset($this->pages[$id][$version]))
		{
			$page = new Page($this,$id,$version);
			if ($page->exists())
			{
				$this->pages[$id][$version]=&$page;
			}
			else
			{
				$this->pages[$id][$version]=false;
			}
		}
		return $this->pages[$id][$version];
	}
	
	function &getPages()
	{
		$pages=array();
		$dir=$this->dir.'/pages';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$page=&$this->getPage($entry);
				if ($page!==false)
				{
					$pages[]=&$page;
				}
			}
		}
		closedir($dir);
		return $pages;
	}

	function &getTemplate($id,$version=false)
	{
		if ($version===false)
		{
			$version=$this->getCurrentVersion($this->getDir().'/templates/'.$id);
		}
		if (!isset($this->templates[$id][$version]))
		{
			$template = new Template($this,$id,$version);
			if ($template->exists())
			{
				$this->templates[$id][$version] = &$template;
			}
			else
			{
				$this->templates[$id][$version]=false;
			}
		}
		return $this->templates[$id][$version];
	}

	function &getTemplates()
	{
		$templates=array();
		$dir=$this->dir.'/templates';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$template=&$this->getTemplate($entry);
				if ($template!==false)
				{
					$templates[]=&$template;
				}
			}
		}
		closedir($dir);
		return $templates;
	}
}

function &getAllContainers()
{
	global $_PREFS;
	
	$containers=array();
	$list = $_PREFS->getPrefBranch('container');
	foreach (array_keys($list) as $text)
	{
		list($id,$base)=explode('.',$text,2);
		if ($base=='basedir')
		{
			$container=&getContainer($id);
			if ($container->isVisible())
				$containers[$id]=&getContainer($id);
		}
	}
	return $containers;
}

$_CONTAINERS = array();

function &getContainer($id)
{
	global $_CONTAINERS,$_PREFS;
	
	if (!isset($_CONTAINERS[$id]))
	{
		if ($_PREFS->isPrefSet('container.'.$id.'.basedir'))
		{
			$_CONTAINERS[$id] = new Container($id);
		}
		else
		{
			$_CONTAINERS[$id]=null;
		}
	}
	return $_CONTAINERS[$id];
}

?>