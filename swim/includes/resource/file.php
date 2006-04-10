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