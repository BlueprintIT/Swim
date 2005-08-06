<?

/*
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class WorkingDetails
{
	var $user;
	var $date;
	var $version;
	var $dir;
	var $id;
	var $blank;
	var $container;
	var $log;
	
	function WorkingDetails(&$container,$id,$version,$dir)
	{
		global $_USER;
		
		$this->id=$id;
		$this->container=&$container;
		$this->version=$version;
		$this->dir=$dir;
		$this->user=&$_USER;
		$this->blank=true;
		$this->log=&LoggerManager::getLogger('swim.working');
		
		if (!is_dir($dir))
		{
			mkdir($dir);
		}
		
		$this->loadDetails();
	}
	
	function isMine()
	{
		global $_USER;
		
		return $_USER->getUsername()==$this->user->getUsername();
	}
	
	function getDir()
	{
		return $this->dir;
	}
	
	function isNew()
	{
		return $this->blank;
	}
	
	function internalClean()
	{
		recursiveDelete($this->dir,true);
		$this->blank=true;
	}
	
	function clean()
	{
		$lock=lockResourceWrite($this->dir);
		$this->internalClean();
		unlockResource($lock);
	}
	
	function takeOver()
	{
		global $_USER;
		
		$lock=lockResourceWrite($this->dir);
		$this->user=&$_USER;
		$this->internalSave();
		unlockResource($lock);
	}
	
	function takeOverClean()
	{
		global $_USER;
		
		$lock=lockResourceWrite($this->dir);
		$this->user=&$_USER;
		$this->internalClean();
		$this->internalSave();
		unlockResource($lock);
	}
	
	function free()
	{
		global $_PREFS;
		
		$lock=lockResourceWrite($this->dir);
		$this->internalClean();
		unlink($this->dir.'/'.$_PREFS->getPref('locking.templockfile'));
		unlockResource($lock);
		return true;
	}
	
	function loadDetails()
	{
		global $_PREFS;
		
		$lock=lockResourceWrite($this->dir);
		if (is_readable($this->dir.'/'.$_PREFS->getPref('locking.templockfile')))
		{
			$this->blank=false;
			$file=fopen($this->dir.'/'.$_PREFS->getPref('locking.templockfile'),'r');
			$line=trim(fgets($file));
			$user=new User($line);
			if ($user->userExists())
			{
				$this->user=&$user;
			}
			$this->date=trim(fgets($file));
			fclose($file);
		}
		else
		{
			$this->internalSave();
		}
		unlockResource($lock);
	}
	
	function internalSave()
	{
		global $_PREFS;
		
		$this->date=time();
		$file=fopen($this->dir.'/'.$_PREFS->getPref('locking.templockfile'),'w');
		fwrite($file,$this->user->getUsername()."\n");
		fwrite($file,$this->date."\n");
		fclose($file);
	}
	
	function saveDetails()
	{
		$lock=lockResourceWrite($this->dir);
		$this->internalSave();
		unlockResource($lock);
	}
}

class Resource
{
	var $container;
	var $parent;
	var $id;
	var $version;
	var $prefs;
	var $modified;
	var $log;
	var $working;
	
	var $resources = array();

	var $readLock;
	var $writeLock;
	var $lockCount=0;
	
	var $dir;
	
	function Resource(&$container, $id, $version)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.resource.'.get_class($this));
		$this->id=$id;
		
		if (is_a($container,"Container"))
		{
			$this->container=&$container;
			$this->version=$version;
			$this->dir=$this->container->getResourceDir($this);
		}
		else
		{
			$this->parent=&$container;
			$this->version=$this->parent->version;
			$this->container=&$container->container;
			if (is_a($this,'File'))
			{
				$this->dir=$this->parent->getDir();
			}
			else
			{
				$this->dir=$this->parent->getDir().'/'.$this->getTypeName().'s/'.$this->id;
			}
		}

		$this->prefs = new Preferences();
		$this->prefs->setParent($this->container->prefs);
		if (!is_a($this,'File'))
		{
			$file=$this->openFileRead('resource.conf');
			if ($file!==false)
			{
				$this->prefs->loadPreferences($file);
				$this->closeFile($file);
			}
		}
	}
	
	function getTypeName()
	{
		if (is_a($this,'Page'))
		{
			return 'page';
		}
		if (is_a($this,'Template'))
		{
			return 'template';
		}
		if (is_a($this,'File'))
		{
			return 'file';
		}
		if (is_a($this,'Block'))
		{
			return 'block';
		}
	}
	
	function &loadBlock($id,$version = false)
	{
		$block = &loadBlock($this->getDir().'/blocks/'.$id,$this,$id,$version);
		if (($block!==false)&&($block->exists()))
		{
			return $block;
		}
		return false;
	}
	
	function &loadTemplate($id,$version = false)
	{
		$template = new Template($this,$id,$version);
		if ($template->exists())
		{
			return $template;
		}
		return false;
	}
	
	function &loadPage($id,$version = false)
	{
		$page = new Page($this,$id,$version);
		if ($page->exists())
		{
			return $page;
		}
		return false;
	}
	
	function &loadFile($id,$version = false)
	{
		return new File($this,$id,$version);
	}
	
	function &getBlock($id,$version = false)
	{
		return $this->getResource('block',$id,$version);
	}
	
	function &getTemplate($id,$version = false)
	{
		return $this->getResource('template',$id,$version);
	}
	
	function &getPage($id,$version = false)
	{
		return $this->getResource('page',$id,$version);
	}
	
	function &getFile($id)
	{
		return $this->getResource('file',$id);
	}
	
	function hasResource($type,$id,$version = false)
	{
		return is_dir($this->getDir().'/'.$type.'s/'.$id);
	}
	
	function &getResource($type,$id,$version = false)
	{
		$path=$type.'/'.$id.':'.$version;
		if (!isset($this->resources[$path]))
		{
			if ($type=='block')
			{
				$resource=&$this->loadBlock($id,$version);
			}
			else if ($type=='template')
			{
				$resource=&$this->loadTemplate($id,$version);
			}
			else if ($type=='page')
			{
				$resource=&$this->loadPage($id,$version);
			}
			else if ($type=='file')
			{
				$resource=&$this->loadFile($id,$version);
			}
			else
			{
				return false;
			}
			$this->resources[$path]=&$resource;
		}
		return $this->resources[$path];
	}
	
	function &getResources($type)
	{
		$resources=array();
		$dir=$this->getDir().'/'.$type.'s';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$resource=&$this->getResource($type,$entry);
				if ($resource!==false)
				{
					$resources[]=&$resource;
				}
			}
		}
		closedir($dir);
		return $resources;
	}

	function &getWorkingDetails()
	{
		if (!isset($this->working))
		{
			if (isset($this->parent))
			{
				$this->working=&$this->parent->getWorkingDetails();
			}
			else
			{
				$this->working=&$this->container->getResourceWorkingDetails($this);
			}
		}
		return $this->working;
	}
	
	function &makeNewVersion()
	{
		if (isset($this->parent))
		{
			$parentv=&$this->parent->makeNewVersion();
			return $parentv->getResource($this->getTypeName(),$this->id);
		}
		else
		{
			return $this->container->makeNewResourceVersion($this);
		}
	}
	
	function &makeWorkingVersion()
	{
		if (isset($this->parent))
		{
			$parentv=&$this->parent->makeWorkingVersion();
			return $parentv->getResource($this->getTypeName(),$this->id);
		}
		else
		{
			return $this->container->makeResourceWorkingVersion($this);
		}
	}
	
	function &getVersions()
	{
		if (isset($this->parent))
		{
			return $this->parent->getVersions();
		}
		else
		{
			return $this->container->getResourceVersions($this);
		}
	}
	
	function isCurrentVersion()
	{
		if (isset($this->parent))
		{
			return $this->parent->isCurrentVersion();
		}
		else
		{
			return $this->container->isCurrentResourceVersion($this);
		}
	}
	
	function makeCurrentVersion()
	{
		if (isset($this->parent))
		{
			$this->parent->makeCurrentVersion();
		}
		else
		{
			$this->container->makeCurrentResourceVersion($this);
		}
	}
	
	function &getCurrentVersion()
	{
		if (isset($this->parent))
		{
			return $this->parent->getCurrentVersion();
		}
		else
		{
			return $this->container->getCurrentResourceVersion($this);
		}
	}
	
	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			if (is_readable($this->getDir().'/resource.conf'))
			{
				$stat=stat($this->getDir().'/resource.conf');
				$this->modified=$stat['mtime'];
			}
			else
			{
				$this->modified=0;
			}
		}
		return $this->modified;
	}
	
	function exists()
	{
		return is_dir($this->getDir());
	}
	
	function getPath()
	{
		if (isset($this->parent))
		{
			$path=$this->parent->getPath();
		}
		else
		{
			$path=$this->container->getPath();
		}
		return $path.'/'.$this->getTypeName().'/'.$this->id;
	}
	
	function getETag()
	{
		return $this->getPath().':'.$this->version;
	}
	
	function getDir()
	{
		return $this->dir;
	}

	function isWritable()
	{
		return $this->container->isWritable();
	}
	
	function isVisible()
	{
		return $this->container->isVisible();
	}
	
	function isFile()
	{
		return is_a($this,'File');
	}

	function isPage()
	{
		return is_a($this,'Page');
	}

	function isBlock()
	{
		return is_a($this,'Block');
	}

	function isTemplate()
	{
		return is_a($this,'Template');
	}

	function lockRead()
	{
		$this->log->debug('lockRead - '.$this->id);
		if (($this->isWritable())&&(!isset($this->readLock))&&(!isset($this->writeLock)))
		{
			$this->log->debug('Making read lock');
			$this->readLock=lockResourceRead($this->getDir());
			$this->log->debug('Locked as '.$this->readLock);
		}
		$this->lockCount++;
	}
	
	function lockWrite()
	{
		$this->log->debug('lockWrite - '.$this->id);
		if ($this->isWritable())
		{
			if (isset($this->readLock))
			{
				$this->log->warn('Write locking read locked template '.$this->id);
				unlockResource($this->readLock);
				unset($this->readLock);
				
				if (isset($this->writeLock))
				{
					$this->log->error('Both read and write lock were applied');
				}
			}
			
			if (!isset($this->writeLock))
			{
				$this->log->debug('Making write lock');
				$this->writeLock=lockResourceWrite($this->getDir());
				$this->log->debug('Locked as '.$this->writeLock);
			}
		}
		else
		{
			$this->log->warn('Obtained a write lock on a read only resource');
		}
		$this->lockCount++;
	}
	
	function unlock()
	{
		$this->log->debug('unlock - '.$this->id);
		if ($this->lockCount>0)
		{
			$this->lockCount--;
			if ($this->lockCount==0)
			{
				if ($this->isWritable())
				{
					if (isset($this->writeLock))
					{
						$this->log->debug('Unlocking write lock');
						unlockResource($this->writeLock);
						unset($this->writeLock);
					}
					else if (isset($this->readLock))
					{
						$this->log->debug('Unlocking read lock');
						unlockResource($this->readLock);
						unset($this->readLock);
					}
					else
					{
						$this->log->error('Unlocking but there is no lock');
					}
				}
			}
			else
			{
				$this->log->debug($this->lockCount.' locks left in play.');
			}
		}
		else
		{
			$this->log->warn('Cannot unlock resource '.$this->id.' since it is not locked');
		}
	}
	
	function fileExists($filename)
	{
		$this->log->debug('Testing for existance of '.$this->getDir().'/'.$filename);
		return is_file($this->getDir().'/'.$filename);
	}
	
	function fileIsWritable($filename)
	{
		global $_USER;
		if ($this->isWritable())
		{
			return (((!is_file($this->getDir().'/'.$filename))||(is_writable($this->getDir().'/'.$filename)))&&($_USER->canWrite($this)));
		}
		return false;
	}
	
	function fileIsReadable($filename)
	{
		global $_USER;
		return ((is_readable($this->getDir().'/'.$filename))&&($_USER->canRead($this)));
	}
	
	function openFileRead($filename)
	{
		if ($this->fileIsReadable($filename))
		{
			$this->lockRead();
			$file=fopen($this->getDir().'/'.$filename,'r');
			if ($file===false)
			{
				$this->log->warn('Failed to open '.$filename);
				$this->unlock();
			}
			return $file;
		}
		else
		{
			//$this->log->warn('Could not open '.$filename.' for reading');
		}
		return false;
	}
	
	function openFileWrite($filename,$append=false)
	{
		$this->log->debug('openFileWrite');
		if ($this->fileIsWritable($filename))
		{
			$this->lockWrite();
			$mode='w';
			if ($append)
			{
				$mode='a';
			}
			$file=fopen($this->getDir().'/'.$filename,$mode);
			if ($file===false)
			{
				$this->log->warn('Failed to open '.$filename);
				$this->unlock();
			}
			return $file;
		}
		else
		{
			$this->log->warn('Could not open '.$filename.' for writing');
		}
		return false;
	}
	
	function closeFile($file)
	{
		fclose($file);
		$this->unlock();
	}

	function &decodeRelativeResource($parts,$version=false)
	{
		if (count($parts)>=2)
		{
			$type=$parts[0];
			$id=$parts[1];

			if ($type=='file')
			{
				$path=implode('/',array_slice($parts,1));
				return $this->getFile($path);
			}
			else
			{
				$resource=&$this->getResource($type,$id,$version);
				if ($resource!==false)
				{
					return $resource->decodeRelativeResource(array_slice($parts,2));
				}
			}
		}
		return $this;
	}
	
	function &decodeResource($request)
	{
		global $_PREFS;
		
		$log=&LoggerManager::getLogger('swim.resource');
		
		if (is_object($request))
		{
			$resource=$request->resource;
			if (isset($request->query['version']))
			{
				$version=$request->query['version'];
			}
			else
			{
				$version=false;
			}
		}
		else
		{
			$resource=$request;
			$version=false;
		}
		
		$log->debug('Decoding '.$resource);
		
		if (strlen($resource)==0)
		{
			$log->info('No resource to decode');
			return false;
		}

		$parts = explode('/',$resource);
		if (($parts[0]=='version')&&(count($parts)>=2))
		{
			$version=$parts[1];
			$parts=array_slice($parts,2);
		}
		
		if (count($parts)<=1)
			return false;
		
		$container=&getContainer($parts[0]);

		return $container->decodeRelativeResource(array_slice($parts,1),$version);
	}
}

class File extends Resource
{
	function delete()
	{
		if ($this->fileIsWritable())
		{
			$this->parent->lockWrite();
			unlink($this->getDir().'/'.$this->id);
			$this->parent->unlock();
		}
	}
	
	function exists()
	{
		return ((parent::fileExists($this->id))||(parent::fileExists($this->id.'.php')));
	}
	
	function fileIsReadable()
	{
		return ((parent::fileIsReadable($this->id))||(parent::fileIsReadable($this->id.'.php')));
	}
	
	function fileIsWritable()
	{
		return parent::fileIsWritable($this->id);
	}

	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			$file=$this->getDir().'/'.$this->id;
			if (!is_file($file))
			{
				$file=$file.'.php';
				if (!is_file($file))
					return false;
			}
			$stat=stat($file);
			$this->modified=$stat['mtime'];
		}
		return $this->modified;
	}
	
	function getContentType()
	{
		return determineContentType($this->getDir().'/'.$this->id);
	}
	
	function outputFile()
	{
		if (isset($this->parent))
		{
			$this->parent->lockRead();
		}
		else
		{
			$this->container->lockRead();
		}
		if (is_file($this->getDir().'/'.$this->id))
		{
			readfile($this->getDir().'/'.$this->id);
		}
		if (is_file($this->getDir().'/'.$this->id.'.php'))
		{
			include($this->getDir().'/'.$this->id.'.php');
		}
		if (isset($this->parent))
		{
			$this->parent->unlock();
		}
		else
		{
			$this->container->unlock();
		}
	}
	
	function openFileRead()
	{
		return parent::openFileRead($this->id);
	}
	
	function makeDir($dir)
	{
		if (is_dir($dir))
		{
			return;
		}
		$base=dirname($dir);
		if (is_dir($base))
		{
			mkdir($dir);
			return;
		}
		else
		{
			$this->makeDir($base);
		}
	}
	
	function openFileWrite($append=false)
	{
		$dir=dirname($this->id);
		if ($dir=='.')
		{
			$dir=$this->getDir();
		}
		else
		{
			$dir=$this->getDir().'/'.$dir;
		}
		$this->makeDir($dir);

		return parent::openFileWrite($this->id,$append);
	}
}

?>