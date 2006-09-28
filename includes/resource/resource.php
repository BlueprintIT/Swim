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
	var $parent;
	var $id;
	var $version;
	var $prefs;
	var $modified;
	var $log;
	var $working;
  var $writable;
	
	var $resources = array();

	var $dir;
	
	function Resource($container, $id, $version)
	{
		global $_PREFS;
		
		$this->log=LoggerManager::getLogger('swim.resource.'.get_class($this));
		$this->id=$id;
		
		if ($container instanceof Container)
		{
			$this->container=$container;
			$this->version=$version;
			$this->dir=$this->container->getResourceDir($this);
		}
		else
		{
			$this->parent=$container;
			$this->version=$this->parent->version;
			$this->container=$container->container;
			if ($this instanceof File)
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

		$this->log->debug('Opening resource configuration');
		$file=$this->openFileRead('resource.conf');
		if ($file!==false)
		{
			$this->log->debug('Loading preferences');
			$this->prefs->loadPreferences($file);
			$this->log->debug('Closing configuration');
			$this->closeFile($file);
		}
		else
		{
			$this->log->debug('Resource configuration not found');
		}

    $this->writable=$this->prefs->getPref('resource.writable',true);
	}
	
  function isWritable()
  {
    return $this->writable;
  }
  
	function savePreferences()
	{
		$this->prefs->setPref('resource.modified',time());
		$file=$this->openFileWrite('resource.conf');
		$this->prefs->savePreferences($file);
		$this->closeFile($file);
	}
	
	function getTypeName()
	{
		if ($this instanceof Page)
		{
			return 'page';
		}
		if ($this instanceof Template)
		{
			return 'template';
		}
		if ($this instanceof File)
		{
			return 'file';
		}
		if ($this instanceof Block)
		{
			return 'block';
		}
	}
	
	function loadBlock($id,$version = false)
	{
		$block = loadBlock($this->getDir().'/blocks/'.$id,$this,$id,$version);
		if (($block!==false)&&($block->exists()))
		{
			return $block;
		}
		return false;
	}
	
	function loadTemplate($id,$version = false)
	{
		$template = new Template($this,$id,$version);
		if ($template->exists())
		{
			return $template;
		}
		return false;
	}
	
	function loadPage($id,$version = false)
	{
		$page = new Page($this,$id,$version);
		if ($page->exists())
		{
			return $page;
		}
		return false;
	}
	
	function loadFile($id,$version = false)
	{
		return new File($this,$id,$version);
	}
	
	function getBlock($id,$version = false)
	{
		return $this->getResource('block',$id,$version);
	}
	
	function getTemplate($id,$version = false)
	{
		return $this->getResource('template',$id,$version);
	}
	
	function getPage($id,$version = false)
	{
		return $this->getResource('page',$id,$version);
	}
	
	function getFile($id,$version = false)
	{
		return $this->getResource('file',$id);
	}
	
	function createNewResource($type, $id=false)
	{
    if (!is_dir($this->getDir()))
    {
      $this->log->errortrace("Resource dir does not exist - ".$this->getPath());
    }
		$this->lockWrite();
    if (!is_dir($this->getDir().'/'.$type.'s'))
    {
      mkdir($this->getDir().'/'.$type.'s');
    }
		$rdir=$this->getDir().'/'.$type.'s/';
		if ($id===false)
		{
			$id=rand(10000,99999);
		}
		while (is_dir($rdir.$id))
		{
			$id=rand(10000,99999);
		}
		$rdir=$rdir.$id;
		mkdir($rdir);
		$this->unlock();
		return array($id,$rdir);
	}
  
  function deleteResource($resource)
  {
    $this->lockWrite();
    $type='';
    if ($resource instanceof Page)
    {
      $type='page';
    }
    else if ($resource instanceof Block)
    {
      $type='block';
    }
    else if ($resource instanceof Block)
    {
      $type='template';
    }
    else if ($resource instanceof Block)
    {
      $type='file';
    }
    $dir = $this->getDir().'/'.$type.'s/'.$resource->id;
    if (is_dir($dir))
    {
      recursiveDelete($dir);
      rmdir($dir);
    }
    else
    {
      unlink($dir);
    }
    $this->unlock();
  }
	
	function hasResource($type,$id,$version = false)
	{
		return is_dir($this->getDir().'/'.$type.'s/'.$id);
	}
	
	function getResource($type,$id,$version = false)
	{
		$path=$type.'/'.$id.':'.$version;
		if (!isset($this->resources[$path]))
		{
			if ($type=='block')
			{
				$resource=$this->loadBlock($id,$version);
			}
			else if ($type=='template')
			{
				$resource=$this->loadTemplate($id,$version);
			}
			else if ($type=='page')
			{
				$resource=$this->loadPage($id,$version);
			}
			else if ($type=='file')
			{
				$resource=$this->loadFile($id,$version);
			}
			else
			{
				$resource=false;
			}
			$this->resources[$path]=$resource;
		}
		return $this->resources[$path];
	}
	
	function getResources($type)
	{
		$resources=array();
		$dir=$this->getDir().'/'.$type.'s';
    if (is_dir($dir))
    {
      $this->lockRead();
  		$dir=opendir($dir);
  		$locknames=LockManager::getLockFiles();
  		while (false !== ($entry=readdir($dir)))
  		{
  			if (($entry[0]!='.')&&(!in_array($entry,$locknames)))
  			{
  				$resource=$this->getResource($type,$entry);
  				if ($resource!==false)
  				{
  					$resources[]=$resource;
  				}
  			}
  		}
  		closedir($dir);
  		$this->unlock();
    }
		return $resources;
	}

	function getWorkingDetails()
	{
		if (!isset($this->working))
		{
			if (isset($this->parent))
			{
				$this->working=$this->parent->getWorkingDetails();
			}
			else
			{
				$this->working=$this->container->getResourceWorkingDetails($this);
			}
		}
		return $this->working;
	}
	
	function makeNewVersion()
	{
		$this->log->debug('Cloning '.$this->id.' to a new version');
		if (isset($this->parent))
		{
			$parentv=$this->parent->makeNewVersion();
			$newv=$parentv->getResource($this->getTypeName(),$this->id);
		}
		else
		{
			$newv=$this->container->makeNewResourceVersion($this);
		}
		$newv->savePreferences();
		return $newv;
	}
	
	function makeWorkingVersion()
	{
		if (isset($this->parent))
		{
			$parentv=$this->parent->makeWorkingVersion();
			return $parentv->getResource($this->getTypeName(),$this->id);
		}
		else
		{
			return $this->container->makeResourceWorkingVersion($this);
		}
	}
	
	function getVersions()
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
  
  function delete()
  {
    if (isset($this->parent))
    {
      $this->parent->deleteResource($this);
    }
    else
    {
      return $this->container->deleteResource($this);
    }
  }
  
	function makeCurrentVersion()
	{
		$this->log->debug('Making this the current version');
		if (isset($this->parent))
		{
			$this->parent->makeCurrentVersion();
		}
		else
		{
			$this->container->makeCurrentResourceVersion($this);
		}
		$this->log->debug('Complete');
	}
	
	function getCurrentVersion()
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
			if (isset($this->prefs->preferences['resource.modified']))
			{
				$this->modified=$this->prefs->preferences['resource.modified'];
			}
			
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

	function isVisible()
	{
		return $this->container->isVisible();
	}
	
	function isFile()
	{
		return $this instanceof File;
	}

	function isPage()
	{
		return $this instanceof Page;
	}

	function isBlock()
	{
		return $this instanceof Block;
	}

	function isTemplate()
	{
		return $this instanceof Template;
	}

	function lockRead()
	{
		$this->log->debug('lockRead - '.$this->id);
		if ($this->isWritable())
		{
			LockManager::lockResourceRead($this->getDir());
		}
	}
	
	function lockWrite()
	{
		$this->log->debug('lockWrite - '.$this->id);
		if ($this->isWritable())
		{
			LockManager::lockResourceWrite($this->getDir());
		}
		else
		{
			$this->log->warn('Obtained a write lock on a read only resource');
		}
	}
	
	function unlock()
	{
		$this->log->debug('unlock - '.$this->id);
		if ($this->isWritable())
		{
			LockManager::unlockResource($this->getDir());
		}
	}
	
	function dirExists($filename)
	{
		$this->log->debug('Testing for existance of '.$this->getDir().'/'.$filename);
		return is_dir($this->getDir().'/'.$filename);
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
			return (((!file_exists($this->getDir().'/'.$filename))||(is_writable($this->getDir().'/'.$filename)))&&($_USER->canWrite($this)));
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
			//$this->log->warn('Could not open '.$filename.' for writing');
		}
		return false;
	}
	
	function closeFile($file)
	{
		fclose($file);
		$this->unlock();
	}

	function decodeRelativeResource($parts,$version=false)
	{
    if (count($parts)==0)
    {
      return $this;
    }
		else if (count($parts)>=2)
		{
      $log=LoggerManager::getLogger('swim.resource');
      
			$type=$parts[0];
			$id=$parts[1];

      $log->debug("Decoding ".$type." resource ".$id." version ".$version);

			if ($type=='file')
			{
				$path=rawurldecode(implode('/',array_slice($parts,1)));
				return $this->getFile($path,$version);
			}
			else
			{
				$resource=$this->getResource($type,$id,$version);
				if ($resource!==false)
				{
					return $resource->decodeRelativeResource(array_slice($parts,2));
				}
        else
        {
          $log->debug("Request for ".$type." ".$id." version ".$version." failed.");
        }                
			}
		}
		return false;
	}
	
	static function decodeResource($request,$version=false)
	{
		global $_PREFS;
		
		$log=LoggerManager::getLogger('swim.resource');
		
		if (is_object($request))
		{
			$resource=$request->resource;
			
			if (($version===false)&&(isset($request->query['version'])))
			{
				$version=$request->query['version'];
			}
		}
		else
		{
			$resource=$request;
		}
		
		$log->debug('Decoding '.$resource);
		
		if (strlen($resource)==0)
		{
			$log->debug('No resource to decode');
			return false;
		}

		$parts = explode('/',$resource);
		if (($parts[0]=='version')&&(count($parts)>=2))
		{
			$version=$parts[1];
			$parts=array_slice($parts,2);
		}
		
		$log->debug('Creating resource version '.$version);
		
		if (count($parts)<=1)
			return false;
		
		$container=getContainer($parts[0]);
    if ($container!==null)
    {
  		return $container->decodeRelativeResource(array_slice($parts,1),$version);
    }
    else
    {
      return false;
    }
	}
}

function getAllResources($type)
{
  global $_PREFS;
  
  $resources=array();
  $containers=getAllContainers();
  foreach($containers as $container)
  {
    $newresources=$container->getResources($type);
    $resources=array_merge($resources,$newresources);
  }
  return $resources;
}

?>