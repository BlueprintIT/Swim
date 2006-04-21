<?

/*
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

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
  
  function isVersioned()
  {
    if (isset($this->parent))
      return $this->parent->isVersioned();
    else
      return false;
  }
  
  function getSubfile($name)
  {
    if (isset($this->parent))
    {
      return $this->parent->getFile($this->id.'/'.$name,$this->version);
    }
    else
    {
      return $this->container->getFile($this->id.'/'.$name,$this->version);
    }
  }
  
  function isExistingDir()
  {
    return parent::dirExists($this->id);
  }
  
  function isExistingFile()
  {
    return ((parent::fileExists($this->id))||(parent::fileExists($this->id.'.php')));
  }
  
  function exists()
  {
    return (($this->isExistingFile())||($this->isExistingDir()));
  }
  
  function mkDir()
  {
    if ($this->isExistingFile())
    {
      $this->log->error('Attempt to create a dir when a file already exists with the same name.');
      return false;
    }
    else if (!$this->isExistingDir())
    {
      $this->lockWrite();
      $dir=$this->getDir().'/'.$this->id;
      recursiveMkDir($dir);
      $this->unlock();
      return true;
    }
    return true;
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
  
  function getFileName()
  {
  	return $this->getDir().'/'.$this->id;
  }
  
	function getViewPath()
	{
	  if ((!$this->isDynamic())
	   && ($this->prefs->getPref('url.allowdirect'))
	   && ($this->prefs->isPrefSet('container.'.$this->container->id.'.directpath')))
	  {
	    $path = $this->prefs->getPref('container.'.$this->container->id.'.directpath');
	    $path .= $this->getContainerPath().'/'.$this->id;
	    return $path;
	  }
	  else
  	  return parent::getViewPath();
	}
	
  function isDynamic()
  {
    if (is_file($this->getDir().'/'.$this->id.'.php'))
      return true;
    return FileHandlers::canHandle($this->getContentType());
  }
  
  function outputRealFile()
  {
    if (isset($this->parent))
    {
      $this->parent->lockRead();
    }
    else
    {
      $this->container->lockRead();
    }
    
  	if (is_file($this->getDir().'/'.$this->id.'.php'))
      include($this->getDir().'/'.$this->id.'.php');
    else if (is_file($this->getDir().'/'.$this->id))
      readfile($this->getDir().'/'.$this->id);
    
    if (isset($this->parent))
    {
      $this->parent->unlock();
    }
    else
    {
      $this->container->unlock();
    }
  }
  
  function outputFile($request = null)
  {
    if ($request === null)
      $this->log->warntrace("Unset request to outputfile");
      
    $type = $this->getContentType();
    if (FileHandlers::canHandle($type))
  		FileHandlers::output($type, $request, $this);
  	else
  	  $this->outputRealFile();
  }
  
  function openFileRead($name = null)
  {
    if ($name==null)
      $name=$this->id;
      
    return parent::openFileRead($name);
  }
  
  function openFileWrite($append=false)
  {
    $this->lockWrite();
    $dir=dirname($this->id);
    if ($dir=='.')
    {
      $dir=$this->getDir();
    }
    else
    {
      $dir=$this->getDir().'/'.$dir;
    }
    recursiveMkDir($dir);
    $this->unlock();
    
    return parent::openFileWrite($this->id,$append);
  }
}

?>