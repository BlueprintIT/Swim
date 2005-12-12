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

$_LOCKS = array();

define('LOCK_READ',1);
define('LOCK_WRITE',2);

function getLockFiles()
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  return array($_PREFS->getPref('locking.lockfile'));
 	}
  else if ($type=='mkdir')
 	{
	  return array($_PREFS->getPref('locking.lockfile'), $_PREFS->getPref('locking.mkdirlock'));
 	}
 	else
 	{
 		return array();
 	}
}

function &storageRead($log,$dir)
{
  global $_STORAGE;
  
  return true;
}

function &storageWrite($log,$dir)
{
  global $_STORAGE;
  
  return true;
}

function &storageUpgrade($log,$dir,&$lock)
{
  global $_STORAGE;
  
  return true;
}

function storageUnlock($log,$dir,&$lock,$type)
{
  global $_STORAGE;
  
  if ($type==LOCK_WRITE)
  {
    $_STORAGE->queryExec("DELETE FROM DirLock WHERE dir='".storage_escape($dir)."';");
  }
  else
  {
    $_STORAGE->queryExec('BEGIN TRANSACTION;');
    $_STORAGE->queryExec("UPDATE DirLock SET count=count-1 WHERE dir='".storage_escape($dir)."';");
    $count = $_STORAGE->singleQuery("SELECT count FROM DirLock WHERE dir='".storage_escape($dir)."';");
    if ($count==0)
    {
      $_STORAGE->queryExec("DELETE FROM DirLock WHERE dir='".storage_escape($dir)."';");
    }
    $_STORAGE->queryExec('COMMIT TRANSACTION;');
  }
}

function &flockRead($log,$dir)
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
		return false;
	}
}

function &flockWrite($log,$dir)
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
		return false;
	}
}

function &flockUpgrade($log,$dir,&$lock)
{
	flock($lock,LOCK_EX);
	return $lock;
}

function flockUnlock($log,$dir,&$lock,$type)
{
	flock($lock,LOCK_UN);
	fclose($lock);
}

function mkdirGetLock($log,$lockdir,$staleage)
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

function &mkdirRead($log,$dir)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	$staleage=$_PREFS->getPref('locking.staleage');
	mkdirGetLock($log,$lockdir,$staleage);

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
	return LOCK_READ;
}

function &mkdirWrite($log,$dir)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	$staleage=$_PREFS->getPref('locking.staleage');
	mkdirGetLock($log,$lockdir,$staleage);
	while ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockfile))<$staleage))
	{
		rmdir($lockdir);
		sleep(1);
		mkdirGetLock($log,$lockdir,$staleage);
	}
	if (is_file($lockfile))
	{
		if (is_file($lockfile))
		{
			$log->warn('Clearing stale lock file '.$lockfile);
		}
		unlink($lockfile);
	}
	return true;
}

function &mkdirUpgrade($log,$dir,&$lock)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	$staleage=$_PREFS->getPref('locking.staleage');
	$count=-1;

	mkdirGetLock($log,$lockdir,$staleage);
	while ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockfile))<$staleage))
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
		if ($count==1)
			break;
		rmdir($lockdir);
		sleep(1);
		mkdirGetLock($log,$lockdir,$staleage);
	}

	if (is_file($lockfile))
	{
		if ($count!=1)
			$log->warn('Clearing stale lock file '.$lockfile);
		unlink($lockfile);
	}
	return true;
}

function mkdirUnlock($log,$dir,&$lock,$type)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');

	if (is_dir($dir))
	{
		$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
		if ($type==LOCK_READ)
		{
			$staleage=$_PREFS->getPref('locking.staleage');
			mkdirGetLock($log,$lockdir,$staleage);
	
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

function &getReadLock($log,$dir)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  $log->debug('Calling '.$type.' to lock '.$dir.' to level '.LOCK_READ);
  if ($type=='flock')
 	{
	  $lock=&flockRead($log,$dir);
 	}
  else if ($type=='mkdir')
  {
    $lock=&mkdirRead($log,$dir);
  }
  else if ($type=='storage')
  {
    $lock=&storageRead($log,$dir);
  }
 	else if ($type=='none')
 	{
 		$lock=true;
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 		$lock=true;
 	}
  $log->debug('Lock complete');
 	return $lock;
}

function &getWriteLock($log,$dir)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  $log->debug('Calling '.$type.' to lock '.$dir.' to level '.LOCK_WRITE);
  if ($type=='flock')
 	{
	  $lock=&flockWrite($log,$dir);
 	}
  else if ($type=='mkdir')
  {
    $lock=&mkdirWrite($log,$dir);
  }
  else if ($type=='storage')
  {
    $lock=&storageWrite($log,$dir);
  }
 	else if ($type=='none')
 	{
 		$lock=true;
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 		$lock=true;
 	}
  $log->debug('Lock complete');
 	return $lock;
}

function &upgradeLock($log,$dir,&$lock)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  $log->debug('Calling '.$type.' to upgrade lock on '.$dir.' to level '.LOCK_WRITE);
  if ($type=='flock')
 	{
	  $lock=&flockUpgrade($log,$dir,$lock);
 	}
  else if ($type=='mkdir')
  {
    $lock=&mkdirUpgrade($log,$dir,$lock);
  }
  else if ($type=='storage')
  {
    $lock=&storageUpgrade($log,$dir,$lock);
  }
 	else if ($type=='none')
 	{
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 	}
  $log->debug('Upgrade complete');
 	return $lock;
}

function unLock($log,$dir,&$lock,$type)
{
	global $_PREFS;
	
  $ltype=$_PREFS->getPref('locking.type','flock');
  $log->debug('Calling '.$ltype.' to unlock '.$dir.' from level '.$type);
  if ($ltype=='flock')
 	{
	  flockUnlock($log,$dir,$lock,$type);
 	}
  else if ($ltype=='mkdir')
  {
    mkdirUnlock($log,$dir,$lock,$type);
  }
  else if ($ltype=='storage')
  {
    storageUnlock($log,$dir,$lock,$type);
  }
 	else if ($ltype=='none')
 	{
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 	}
  $log->debug('Unlock complete');
}

function lockResourceRead($dir)
{
	global $_LOCKS,$_PREFS;
	
	if ($_PREFS->getPref('locking.alwaysexclusive',false))
	{
		return lockResourceWrite($dir);
	}
	
	$log=LoggerManager::getLogger('swim.locking');

	if (!isset($_LOCKS[$dir]))
	{
		$_LOCKS[$dir] = array('dir' => $dir);
	}
		
	if (!isset($_LOCKS[$dir]['count']))
	{
	 	$lock=&getReadLock($log,$dir);
	 	
		if ($lock!==false)
		{
		  $_LOCKS[$dir]['type']=LOCK_READ;
		  $_LOCKS[$dir]['lock']=&$lock;
		  $_LOCKS[$dir]['count']=1;
		}
		else
		{
		  $log->warntrace('Lock failed on dir '.$dir);
		}
	}
	else
	{
		$_LOCKS[$dir]['count']++;
	}
	return true;
}

function lockResourceWrite($dir)
{
	global $_LOCKS,$_PREFS;
	
	$log=LoggerManager::getLogger('swim.locking');

	if (!isset($_LOCKS[$dir]))
	{
		$_LOCKS[$dir] = array('dir' => $dir);
	}
	
	if (!isset($_LOCKS[$dir]['count']))
	{
		$lock=&getWriteLock($log,$dir);
	 	
		if ($lock!==false)
		{
		  $_LOCKS[$dir]['type']=LOCK_WRITE;
		  $_LOCKS[$dir]['lock']=&$lock;
		  $_LOCKS[$dir]['count']=1;
		}
		else
		{
		  $log->warntrace('Lock failed on dir '.$dir);
		}
	}
	else if ($_LOCKS[$dir]['type']==LOCK_READ)
	{
		$lock=&upgradeLock($log,$dir,$_LOCKS[$dir]['lock']);
		if ($lock)
		{
			$_LOCKS[$dir]['type']=LOCK_WRITE;
			$_LOCKS[$dir]['lock']=&$lock;
			$_LOCKS[$dir]['count']++;
		}
		else
		{
			$log->error('Failed to upgrade lock on '.$dir);
		}
	}
	else
	{
	  $_LOCKS[$dir]['count']++;
	}
}

function unlockResource($dir)
{
	global $_LOCKS,$_PREFS;
	
	$log=LoggerManager::getLogger('swim.locking');
	if ((isset($_LOCKS[$dir]))&&(isset($_LOCKS[$dir]['count']))&&($_LOCKS[$dir]['count']>0))
	{
	  $_LOCKS[$dir]['count']--;
	  if (($_LOCKS[$dir]['count']==0)&&(!$_PREFS->getPref('locking.extendedlocking')))
	  {
		  unLock($log,$dir,$_LOCKS[$dir]['lock'],$_LOCKS[$dir]['type']);
	  	unset($_LOCKS[$dir]['count']);
	  	unset($_LOCKS[$dir]['type']);
	  	unset($_LOCKS[$dir]['lock']);
	  }
	}
	else
	{
		$log->warntrace('Attempt to unlock unlocked '.$dir);
	}
}

function shutdownLocking()
{
	global $_LOCKS,$_PREFS;
	
	$log=LoggerManager::getLogger('swim.locking');
	foreach ($_LOCKS as $dir => $lock)
	{
		if (isset($lock['count']))
		{
			if ($lock['count']>0)
			{
				$log->warn($dir.' was not properly unlocked');
				unLock($log,$dir,$lock['lock'],$lock['type']);
			}
			else if ($_PREFS->getPref('locking.extendedlocking'))
			{
				unLock($log,$dir,$lock['lock'],$lock['type']);
			}
		}
	}
	$_LOCKS=array();
}

?>