<?

/*
 * Swim
 *
 * Resource locking functions using mkdir as the lock
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class MkdirLocker extends Locker
{
  public function getLockFiles()
  {
    global $_PREFS;
    
    return array($_PREFS->getPref('locking.lockfile'), $_PREFS->getPref('locking.mkdirlock'));
  }
  
  private function getLock($log,$lockdir,$staleage)
  {
    global $_PREFS;
    
    if (is_dir($lockdir))
    {
      if ((time() - filemtime($lockdir)) > $staleage)
      {
        $log->warn('Removing stale lock '.$lockdir);
        rmdir($lockdir);
      }
    }
  
    $locked=@mkdir($lockdir);
    while (!$locked)
    {
      usleep(10000);
      $locked=@mkdir($lockdir);
    }
  }
  
  public function &getReadLock($log,$dir)
  {
    global $_PREFS;
    $lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
    $lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
    $staleage=$_PREFS->getPref('locking.staleage');
    $this->getLock($log,$lockdir,$staleage);
  
    if ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockfile))<$staleage))
    {
      $file=fopen($lockfile,'r');
      if ($file===false)
      {
        $log->error('Error opening lockfile '.$lockfile);
        rmdir($lockdir);
        return false;
      }
      $count=(int)fgets($file);
      fclose($file);
    }
    else
    {
      if (is_file($lockfile))
      {
        $log->warn('Clearing stale lock file '.$lockfile);
      }
      $count=0;
    }
    
    $count++;
    $file=fopen($lockfile,'w');
    if ($file===false)
    {
      $log->error('Error opening lockfile '.$lockfile);
      rmdir($lockdir);
      return false;
    }
    fwrite($file,$count);
    fclose($file);
  
    rmdir($lockdir);
    $result = LOCK_READ;
    return $result;
  }
  
  public function &getWriteLock($log,$dir)
  {
    global $_PREFS;
    $lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
    $lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
    $staleage=$_PREFS->getPref('locking.staleage');
    $this->getLock($log,$lockdir,$staleage);
    while ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockfile))<$staleage))
    {
      rmdir($lockdir);
      sleep(1);
      $this->getLock($log,$lockdir,$staleage);
    }
    if (is_file($lockfile))
    {
      if (is_file($lockfile))
      {
        $log->warn('Clearing stale lock file '.$lockfile);
      }
      unlink($lockfile);
    }
    $result = true;
    return $result;
  }
  
  public function unlock($log,$dir,&$lock,$type)
  {
    global $_PREFS;
    $lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
  
    if (is_dir($dir))
    {
      $lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
      if ($type==LOCK_READ)
      {
        $staleage=$_PREFS->getPref('locking.staleage');
        $this->getLock($log,$lockdir,$staleage);
    
        if ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockfile))<$staleage))
        {
          $file=fopen($lockfile,'r');
          if ($file===false)
          {
            $log->error('Error opening lockfile '.$lockfile);
            rmdir($lockdir);
            return false;
          }
          $count=(int)fgets($file);
          fclose($file);
          $count--;
        }
        else
        {
          if (is_file($lockfile))
          {
            $log->warn('Clearing stale lock file '.$lockfile);
          }
          $count=0;
        }
    
        if ($count>0)
        {
          $file=fopen($lockfile,'w');
          fwrite($file,$count);
          fclose($file);
        }
        else if (is_file($lockfile))
        {
          unlink($lockfile);
        }
      }
      if (!@rmdir($lockdir))
      {
        $log->warntrace('Could not remove lock dir '.$lockdir);
      }
    }
    else
    {
      $log->info('Non-existant directory unlocked - '.$dir);
    }
  }
}

?>
