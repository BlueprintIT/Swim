<?

/*
 * Swim
 *
 * Resource locking functions using storage backend
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class StorageLocker extends Locker
{
  public function &getReadLock($log,$dir)
  {
    return true;
  }
  
  public function &getWriteLock($log,$dir)
  {
    return true;
  }
  
  public function unlock($log,$dir,&$lock,$type)
  {
  }
}

?>
