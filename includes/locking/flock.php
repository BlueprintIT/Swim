<?

/*
 * Swim
 *
 * Resource locking functions using flock to lock
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FlockLocker extends Locker
{
  public function getLockFiles()
  {
    global $_PREFS;
    
    return array($_PREFS->getPref('locking.lockfile'));
  }
  
  public function &getReadLock($log,$dir)
  {
    global $_PREFS;
    
    $lockfile = $dir.'/'.$_PREFS->getPref('locking.lockfile');
    $file = fopen($lockfile,'a+');
    if (flock($file,LOCK_SH))
    {
      return $file;
    }
    else
    {
      fclose($file);
      $result = false;
      return $result;
    }
  }
  
  public function &getWriteLock($log,$dir)
  {
    global $_PREFS;
    
    $lockfile = $dir.'/'.$_PREFS->getPref('locking.lockfile');
    $file = fopen($lockfile,'a+');
    if (flock($file,LOCK_EX))
    {
      return $file;
    }
    else
    {
      fclose($file);
      $result = false;
      return $result;
    }
  }
  
  public function unlock($log,$dir,&$lock,$type)
  {
    flock($lock,LOCK_UN);
    fclose($lock);
  }
}

?>
