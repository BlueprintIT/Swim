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
  
  function WorkingDetails($container,$id,$version,$dir)
  {
    global $_USER;
    
    $this->id=$id;
    $this->container=$container;
    $this->version=$version;
    $this->dir=$dir;
    $this->user=$_USER;
    $this->blank=true;
    $this->log=LoggerManager::getLogger('swim.working');
    
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
    LockManager::lockResourceWrite($this->dir);
    $this->internalClean();
    LockManager::unlockResource($this->dir);
  }
  
  function takeOver()
  {
    global $_USER;
    
    LockManager::lockResourceWrite($this->dir);
    $this->user=$_USER;
    $this->internalSave();
    LockManager::unlockResource($this->dir);
  }
  
  function takeOverClean()
  {
    global $_USER;
    
    LockManager::lockResourceWrite($this->dir);
    $this->user=$_USER;
    $this->internalClean();
    $this->internalSave();
    LockManager::unlockResource($this->dir);
  }
  
  function free()
  {
    global $_PREFS;
    
    $this->log->debug('Freeing working version');
    LockManager::lockResourceWrite($this->dir);
    $this->internalClean();
    unlink($this->dir.'/'.$_PREFS->getPref('locking.templockfile'));
    LockManager::unlockResource($this->dir);
    return true;
  }
  
  function loadDetails()
  {
    global $_PREFS;
    
    LockManager::lockResourceWrite($this->dir);
    if (is_readable($this->dir.'/'.$_PREFS->getPref('locking.templockfile')))
    {
      $this->blank=false;
      $file=fopen($this->dir.'/'.$_PREFS->getPref('locking.templockfile'),'r');
      $line=trim(fgets($file));
      $user=new User($line);
      if ($user->userExists())
      {
        $this->user=$user;
      }
      $this->date=trim(fgets($file));
      fclose($file);
    }
    else
    {
      $this->internalSave();
    }
    LockManager::unlockResource($this->dir);
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
    LockManager::lockResourceWrite($this->dir);
    $this->internalSave();
    LockManager::unlockResource($this->dir);
  }
}

?>