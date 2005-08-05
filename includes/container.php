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
	
	function getDir()
	{
		return $this->dir;
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
	
	function getResourceBaseDir(&$resource)
	{
		if (is_a($resource,'File'))
		{
			return $this->getDir().'/files';
		}
		else
		{
			return $this->getDir().'/'.$resource->getTypeName().'s/'.$resource->id;
		}
	}
	
	function &getResourceVersions(&$resource)
	{
		$versions=array();

		$dir=$this->getResourceBaseDir($resource);

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
		
		$dir=$this->getResourceBaseDir($resource);
		if (is_a($resource,'Page'))
		{
			$type='page';
		}
		else if (is_a($resource,'Block'))
		{
			$type='block';
		}
		else if (is_a($resource,'Template'))
		{
			$type='template';
		}
		
		if (!isset($this->working[$type][$resource->id]))
		{
			$this->working[$type][$resource->id] = new WorkingDetails($this,$resource->id,$this->prefs->getPref('version.working'),$dir.'/'.$this->prefs->getPref('version.working'));
		}
		return $this->working[$type][$resource->id];
	}
	
	function &makeNewResourceVersion(&$resource)
	{
		$dir=$this->getResourceBaseDir($resource);
		
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
		$dir=$this->getResourceBaseDir($resource);

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
		$dir=$this->getResourceBaseDir($resource);

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
		if (is_a($resource,'File'))
		{
			return $this->getResourceBaseDir($resource);
		}
		else
		{
			return $this->getResourceBaseDir($resource).'/'.$resource->version;
		}
	}
	
	function &loadBlock($id,$version = false)
	{
		$block = &loadBlock($this->getDir().'/blocks/'.$id.'/'.$version,$this,$id,$version);
		if (($block!==false)&&($block->exists()))
		{
			return $block;
		}
		return false;
	}
	
	function hasResource($type,$id,$version = false)
	{
		$ext=$id;
		if ($version!==false)
		{
			$ext=$id.'/'.$version;
		}
		return is_dir($this->getDir().'/'.$type.'s/'.$ext);
	}
	
	function &getResource($type,$id,$version = false)
	{
		if (($type!='file')&&($version===false))
		{
			$version=$this->getCurrentVersion($this->getDir().'/'.$type.'s/'.$id);
		}
		return parent::getResource($type,$id,$version);
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