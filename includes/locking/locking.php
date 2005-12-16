<?

/*
 * Swim
 *
 * Resource locking functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('LOCK_READ',1);
define('LOCK_WRITE',2);

class Locker
{
  public function getLockFiles()
  {
    return array();
  }
  
  public function &getReadLock($log,$dir)
  {
    return true;
  }
  
  public function &getWriteLock($log,$dir)
  {
    return true;
  }
  
  public function &upgradeLock($log,$dir,&$lock)
  {
    $this->unlock($log,$dir,$lock,LOCK_READ);
    return $this->getReadLock($log,$dir);
  }
  
  public function unlock($log,$dir,&$lock,$type)
  {
  }
}

class LockManager
{
  private static $locks = array();
  private static $locker;
  
  public static function getLockFiles()
  {
    return self::$locker->getLockFiles();
  }
  
  public static function lockResourceRead($dir)
  {
  	global $_PREFS;
  	
  	if ($_PREFS->getPref('locking.alwaysexclusive',false))
  	{
  		return self::lockResourceWrite($dir);
  	}
  	
  	$log=LoggerManager::getLogger('swim.locking');
  
  	if (!isset(self::$locks[$dir]))
  	{
  		self::$locks[$dir] = array('dir' => $dir);
  	}
  		
  	if (!isset(self::$locks[$dir]['count']))
  	{
  	 	$lock=&self::$locker->getReadLock($log,$dir);
  	 	
  		if ($lock!==false)
  		{
  		  self::$locks[$dir]['type']=LOCK_READ;
  		  self::$locks[$dir]['lock']=&$lock;
  		  self::$locks[$dir]['count']=1;
  		}
  		else
  		{
  		  $log->warntrace('Lock failed on dir '.$dir);
  		}
  	}
  	else
  	{
  		self::$locks[$dir]['count']++;
  	}
  	return true;
  }
  
  public static function lockResourceWrite($dir)
  {
  	global $_PREFS;
  	
  	$log=LoggerManager::getLogger('swim.locking');
  
  	if (!isset(self::$locks[$dir]))
  	{
  		self::$locks[$dir] = array('dir' => $dir);
  	}
  	
  	if (!isset(self::$locks[$dir]['count']))
  	{
  		$lock=&self::$locker->getWriteLock($log,$dir);
  	 	
  		if ($lock!==false)
  		{
  		  self::$locks[$dir]['type']=LOCK_WRITE;
  		  self::$locks[$dir]['lock']=&$lock;
  		  self::$locks[$dir]['count']=1;
  		}
  		else
  		{
  		  $log->warntrace('Lock failed on dir '.$dir);
  		}
  	}
  	else if (self::$locks[$dir]['type']==LOCK_READ)
  	{
  		$lock=&self::$locker->upgradeLock($log,$dir,self::$locks[$dir]['lock']);
  		if ($lock)
  		{
  			self::$locks[$dir]['type']=LOCK_WRITE;
  			self::$locks[$dir]['lock']=&$lock;
  			self::$locks[$dir]['count']++;
  		}
  		else
  		{
  			$log->error('Failed to upgrade lock on '.$dir);
  		}
  	}
  	else
  	{
  	  self::$locks[$dir]['count']++;
  	}
  }
  
  public static function unlockResource($dir)
  {
  	global $_PREFS;
  	
  	$log=LoggerManager::getLogger('swim.locking');
  	if ((isset(self::$locks[$dir]))&&(isset(self::$locks[$dir]['count']))&&(self::$locks[$dir]['count']>0))
  	{
  	  self::$locks[$dir]['count']--;
  	  if ((self::$locks[$dir]['count']==0)&&(!$_PREFS->getPref('locking.extendedlocking')))
  	  {
  		  self::$locker->unlock($log,$dir,self::$locks[$dir]['lock'],self::$locks[$dir]['type']);
  	  	unset(self::$locks[$dir]['count']);
  	  	unset(self::$locks[$dir]['type']);
  	  	unset(self::$locks[$dir]['lock']);
  	  }
  	}
  	else
  	{
  		$log->warntrace('Attempt to unlock unlocked '.$dir);
  	}
  }
  
  public static function init()
  {
    global $_PREFS;
    
    $type = $_PREFS->getPref('locking.type','none');
    if ($type=='flock')
    {
      self::$locker = new FlockLocker();
    }
    else if ($type=='mkdir')
    {
      self::$locker = new MkdirLocker();
    }
    else if ($type=='storage')
    {
      self::$locker = new StorageLocker();
    }
    else
    {
      self::$locker = new Locker();
    }
  }
  
  public static function shutdown()
  {
  	global $_PREFS;
  	
  	$log=LoggerManager::getLogger('swim.locking');
  	foreach (self::$locks as $dir => $lock)
  	{
  		if (isset($lock['count']))
  		{
  			if ($lock['count']>0)
  			{
  				$log->warn($dir.' was not properly unlocked');
  				self::$locker->unlock($log,$dir,$lock['lock'],$lock['type']);
  			}
  			else if ($_PREFS->getPref('locking.extendedlocking'))
  			{
  				self::$locker->unlock($log,$dir,$lock['lock'],$lock['type']);
  			}
  		}
  	}
  	self::$locks=array();
  }
}

include $_PREFS->getPref('storage.includes').'/locking/storage.php';
include $_PREFS->getPref('storage.includes').'/locking/flock.php';
include $_PREFS->getPref('storage.includes').'/locking/mkdir.php';

LockManager::init();

?>