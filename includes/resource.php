<?

/*
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Resource
{
	var $container;
	var $id;
	var $version;
	var $prefs;
	var $modified;
	
	var $readlock;
	var $writeLock;
	var $lockCount=0;
	
	var $dir;
	
	function Resource(&$container, $id, $version)
	{
		global $_PREFS;
		
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
	
	function getWorkingDir()
	{
		return $this->container->getResourceWorkingDir($this);
	}
	
	function freeWorkingDir()
	{
		$this->container->freeResourceWorkingDir($this);
	}
	
	function makeNewVersion()
	{
		return $this->container->makeNewResourceVersion($this);
	}
	
	function makeWorkingVersion()
	{
		return $this->container->makeResourceWorkingVersion($this);
	}
	
	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			$stat=stat($this->getDir().'/resource.conf');
			$this->modified=$stat['mtime'];
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
		if ((!isset($this->readLock))&&($this->isWritable()))
		{
			$this->readlock=lockResourceRead($this->dir);
		}
		$this->lockCount++;
	}
	
	function lockWrite()
	{
		if ($this->isWritable())
		{
			if (isset($this->readLock))
			{
				$this->log->warn('Write locking read locked template '.$this->id);
				unlockResource($this->readLock);
				$this->writelock=lockResourceWrite($this->dir);
				$this->lockCount++;
			}
			else if (!isset($this->writeLock))
			{
				$this->writelock=lockResourceWrite($this->dir);
				$this->lockCount++;
			}
		}
		$this->lockCount++;
	}
	
	function unlock()
	{
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
		if ($this->isWritable())
		{
			return is_writable($this->getDir().'/'.$filename);
		}
		return false;
	}
	
	function fileIsReadable($filename)
	{
		return is_readable($this->getDir().'/'.$filename);
	}
	
	function openFileRead($filename)
	{
		if ($this->fileIsReadable($filename))
		{
			$this->lockRead();
			return fopen($this->getDir().'/'.$filename,'r');
		}
		return false;
	}
	
	function openFileWrite($filename,$append=false)
	{
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
				$this->unlock();
			}
			return $file;
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

		$this->parent=$parent;
		if (is_a($parent,'Container'))
		{
			$this->container=$parent;
			$this->dir=$this->container->getResourceDir($this);
		}
		else
		{
			$this->container=$parent->container;
			$this->dir=$this->parent->getDir($this);
		}
		$this->id=$path;
		$this->version='noversion';
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
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
	
	function openFileWrite($append=false)
	{
		return parent::openFileWrite($this->id,$append);
	}
}

?>