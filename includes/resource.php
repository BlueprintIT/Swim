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
	
	function WorkingDetails(&$container,$id,$version,$dir)
	{
		global $_USER;
		
		$this->id=$id;
		$this->container=&$container;
		$this->version=$version;
		$this->dir=$dir;
		$this->user=&$_USER;
		$this->blank=true;
		
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
	
	function free()
	{
		global $_PREFS;
		
		$lock=lockResourceWrite($this->dir);
		recursiveDelete($this->dir,true);
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
	var $id;
	var $version;
	var $prefs;
	var $modified;
	var $log;
	
	var $readLock;
	var $writeLock;
	var $lockCount=0;
	
	var $dir;
	
	function Resource(&$container, $id, $version)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.resource.'.get_class($this));
		$this->id=$id;
		$this->version=$version;
		$this->container=&$container;

		$this->dir=$this->container->getResourceDir($this);

		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$file=$this->openFileRead('resource.conf');
		if ($file!==false)
		{
			$this->prefs->loadPreferences($file);
			$this->closeFile($file);
		}
	}
	
	function &getWorkingDetails()
	{
		return $this->container->getResourceWorkingDetails($this);
	}
	
	function makeNewVersion()
	{
		return $this->container->makeNewResourceVersion($this);
	}
	
	function makeWorkingVersion()
	{
		return $this->container->makeResourceWorkingVersion($this);
	}
	
	function &getVersions()
	{
		return $this->container->getResourceVersions($this);
	}
	
	function getETag()
	{
		return $this->container->getETag().'/'.get_class($this).'/'.$this->id.':'.$this->version;
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
	
	function isCurrentVersion()
	{
		return $this->container->isCurrentResourceVersion($this);
	}
	
	function makeCurrentVersion()
	{
		return $this->container->makeCurrentResourceVersion($this);
	}
	
	function getCurrentVersion()
	{
		return $this->container->getCurrentResourceVersion($this);
	}
	
	function exists()
	{
		return is_dir($this->getDir());
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
		$this->log->debug('lockRead');
		if (($this->isWritable())&&(!isset($this->readLock))&&(!isset($this->writeLock)))
		{
			$this->log->debug('Making read lock');
			$this->readLock=lockResourceRead($this->dir);
		}
		$this->lockCount++;
	}
	
	function lockWrite()
	{
		$this->log->debug('lockWrite');
		if ($this->isWritable())
		{
			if (isset($this->readLock))
			{
				$this->log->warn('Write locking read locked template '.$this->id);
				unlockResource($this->readLock);
				unset($this->readLock);
			}
			
			if (!isset($this->writeLock))
			{
				$this->log->debug('Making write lock');
				$this->writelock=lockResourceWrite($this->dir);
			}
		}
		$this->lockCount++;
	}
	
	function unlock()
	{
		$this->log->debug('unlock');
		if ($this->lockCount>0)
		{
			$this->lockCount--;
			if ($this->lockCount==0)
			{
				if ($this->isWritable())
				{
					if (isset($this->writeLock))
					{
						unlockResource($this->writeLock);
						unset($this->writeLock);
					}
					else if (isset($this->readLock))
					{
						unlockResource($this->readLock);
						unset($this->readLock);
					}
				}
			}
		}
		else
		{
			$this->log->warn('Cannot unlock template '.$this->id.' since it is not locked');
		}
	}
	
	function fileExists($filename)
	{
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
			$this->log->warn('Could not open '.$filename.' for reading');
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
		if (count($parts)<3)
			return false;
			
		list($container,$type)=$parts;
		
		$container=&getContainer($container);

		if ($type=='file')
		{
			$log->debug('Found file');
			$result = new File($container,implode('/',array_slice($parts,2)));
		}
		else
		{
			$id=$parts[2];

			if ($type=='page')
			{
				$log->debug('Found page: '.$id);
				$result=&$container->getPage($id,$version);
				if ($result==null)
				{
					$log->warn('Invalid page');
					return false;
				}
				if (count($parts)>3)
				{
					$log->debug('Testing for block '.$parts[3]);
					if ($result->isBlock($parts[3]))
					{
						$log->debug('Found page block '.$parts[3]);
						$result=&$result->getBlock($parts[3]);
						if (count($parts)>4)
						{
							$result = new File($result,implode('/',array_slice($parts,4)));
						}
					}
				}
			}
			else if ($type=='template')
			{
				$log->debug('Found template: '.$id);
				$result=&$container->getTemplate($id,$version);
				if ($result==null)
				{
					$log->warn('Invalid template');
					return false;
				}
				if (count($parts)>3)
				{
					$path=implode('/',array_slice($parts,3));
					$result = new File($result,$path);
				}
			}
			else if ($type=='block')
			{
				$log->debug('Found block: '.$id);
				$result=&$container->getBlock($id,$version);
				if ($result==null)
				{
					$log->warn('Invalid block');
					return false;
				}
				if (count($parts)>3)
				{
					$path=implode('/',array_slice($parts,3));
					$result = new File($result,$path);
				}
			}
			else
			{
				return false;
			}
		}
		
		return $result;
	}
}

class File extends Resource
{
	var $parent;
	
	function File($parent,$path)
	{
		global $_PREFS;

		$this->log=&LoggerManager::getLogger('swim.resource.'.get_class($this));
		$this->parent=$parent;
		if (is_a($parent,'Container'))
		{
			$this->version='noversion';
			$this->container=$parent;
			$this->dir=$this->container->getResourceDir($this);
		}
		else
		{
			$this->version=$parent->version;
			$this->container=$parent->container;
			$this->dir=$this->parent->getDir($this);
		}
		$this->id=$path;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
	}

	function delete()
	{
		if ($this->fileIsWritable())
		{
			$this->parent->lockWrite();
			unlink($this->getDir().'/'.$this->id);
			$this->parent->unlock();
		}
	}
	
	function getETag()
	{
		return $this->parent->getETag().':'.$this->id;
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
		$this->parent->lockRead();
		if (is_file($this->getDir().'/'.$this->id))
		{
			readfile($this->getDir().'/'.$this->id);
		}
		if (is_file($this->getDir().'/'.$this->id.'.php'))
		{
			include($this->getDir().'/'.$this->id.'.php');
		}
		$this->parent->unlock();
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