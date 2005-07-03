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
	
	function Container($id)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.container');
		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->dir=$this->prefs->getPref('container.'.$id.'.basedir');
		$this->log->debug('Container '.$id.' is at '.$this->dir);
		$file=$this->openFileRead('resource.conf');
		if ($file!==false)
		{
			$this->prefs->loadPreferences($file);
			$this->closeFile($file);
		}
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
	}
	
	function getResourceWorkingDir(&$resource)
	{
		global $_USER;
		
		if (!$_USER->isLoggedIn())
		{
			return false;
		}
	
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
		
		$result=false;
		$temp=$dir.'/'.$this->prefs->getPref('version.working');
		if (!is_dir($temp))
		{
			mkdir($temp);
		}
		
		$lock=lockResourceWrite($temp);
		if (is_file($temp.'/'.$this->prefs->getPref('locking.templockfile')))
		{
			$file=fopen($temp.'/'.$this->prefs->getPref('locking.templockfile'),'r');
			$line=trim(fgets($file));
			fclose($file);
			if ($line==$_USER->getUsername())
			{
				$result=$temp;
			}
		}
		else
		{
			$file=fopen($temp.'/'.$this->prefs->getPref('locking.templockfile'),'w');
			fwrite($file,$_USER->getUsername());
			fclose($file);
			$result=$temp;
		}
		unlockResource($lock);
		return $result;
	}
	
	function freeResourceWorkingDir(&$resource)
	{
		$temp=$this->getResourceWorkingDir($resource);
		if ($temp!==false)
		{
			$lock=lockResourceWrite($temp);
			recursiveDelete($temp,true);
			unlink($temp.'/'.$this->prefs->getPref('locking.templockfile'));
			unlockResource($lock);
			return true;
		}
		return false;
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
			return $this->getPage($resource->id,$this->prefs->getPref('version.working'));
		}
		else if (is_a($resource,'Block'))
		{
			return $this->getBlock($resource->id,$this->prefs->getPref('version.working'));
		}
		else if (is_a($resource,'Template'))
		{
			return $this->getTemplate($resource->id,$this->prefs->getPref('version.working'));
		}
	}
	
	function &makeResourceWorkingVersion(&$resource)
	{
		$source=$resource->getDir();
		$target=$this->getResourceWorkingDir($resource);
		$resource->lockRead();
		$lock=lockResourceWrite($target);
		recursiveCopy($source,$target,true);
		unlockResource($lock);
		$resource->unlock();

		if (is_a($resource,'Page'))
		{
			return $this->getPage($resource->id,$this->prefs->getPref('version.working'));
		}
		else if (is_a($resource,'Block'))
		{
			return $this->getBlock($resource->id,$this->prefs->getPref('version.working'));
		}
		else if (is_a($resource,'Template'))
		{
			return $this->getTemplate($resource->id,$this->prefs->getPref('version.working'));
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
		if ($version===false)
		{
			$version=$this->getCurrentVersion($this->getDir().'/blocks/'.$id);
		}
		if (!isset($this->blocks[$id][$version]))
		{
			$block = &loadBlock($this,$id,$version);
			if ($block->exists())
			{
				$this->blocks[$id][$version]=&$block;
			}
			else
			{
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