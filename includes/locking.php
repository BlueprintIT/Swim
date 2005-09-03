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

function &flockRead(&$log,$dir)
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

function &flockWrite(&$log,$dir)
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

function &flockUpgrade(&$log,$dir,&$lock)
{
	flock($lock,LOCK_EX);
	return $lock;
}

function flockUnlock(&$log,$dir,&$lock,$type)
{
	flock($lock,LOCK_UN);
	fclose($lock);
}

function mkdirGetLock(&$log,$lockdir,$staleage)
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

function &mkdirRead(&$log,$dir)
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

function &mkdirWrite(&$log,$dir)
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

function &mkdirUpgrade(&$log,$dir,&$lock)
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

function mkdirUnlock(&$log,$dir,&$lock,$type)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
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

function &getReadLock(&$log,$dir)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  $lock=&flockRead($log,$dir);
 	}
  else if ($type=='mkdir')
 	{
	  $lock=&mkdirRead($log,$dir);
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
 	return $lock;
}

function &getWriteLock(&$log,$dir)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  $lock=&flockWrite($log,$dir);
 	}
  else if ($type=='mkdir')
 	{
	  $lock=&mkdirWrite($log,$dir);
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
 	return $lock;
}

function &upgradeLock(&$log,$dir,&$lock)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  $lock=&flockUpgrade($log,$dir,$lock);
 	}
  else if ($type=='mkdir')
 	{
	  $lock=&mkdirUpgrade($log,$dir,$lock);
 	}
 	else if ($type=='none')
 	{
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 	}
}

function unLock(&$log,$dir,&$lock,$type)
{
	global $_PREFS;
	
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  flockUnlock($log,$dir,$lock,$type);
 	}
  else if ($type=='mkdir')
 	{
	  mkdirUnlock($log,$dir,$lock,$type);
 	}
 	else if ($type=='none')
 	{
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 	}
}

function lockResourceRead($dir)
{
	global $_LOCKS,$_PREFS;
	
	if ($_PREFS->getPref('locking.alwaysexclusive',false))
	{
		return lockResourceWrite($dir);
	}
	
	$log=&LoggerManager::getLogger('swim.locking');

	if (!isset($_LOCK[$dir]))
	{
		$_LOCKS[$dir] = array('dir' => $dir, 'count' => 0);
	}
		
	if ($_LOCKS[$dir]['count']==0)
	{
		$log->debug('Read locking '.$dir);
	 	
	 	$lock=&getReadLock($log,$dir);
	 	
		if ($lock!==false)
		{
		  $log->debug('Lock complete');
		  $_LOCKS[$dir]['type']=LOCK_READ;
		  $_LOCKS[$dir]['lock']=&$lock;
		  $_LOCKS[$dir]['count']++;
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

function lockResourceWrite($dir,$id=false)
{
	global $_LOCKS,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.locking');

	if (!isset($_LOCK[$dir]))
	{
		$_LOCKS[$dir] = array('dir' => $dir, 'count' => 0);
	}
	
	if ($_LOCKS[$dir]['count']==0)
	{
		$log->debug('Write locking '.$dir);
		$lock=&getWriteLock($log,$dir);
	 	
		if ($lock!==false)
		{
		  $log->debug('Lock complete');
		  $_LOCKS[$dir]['type']=LOCK_WRITE;
		  $_LOCKS[$dir]['lock']=&$lock;
		  $_LOCKS[$dir]['count']++;
		}
		else
		{
		  $log->warntrace('Lock failed on dir '.$dir);
		}
	}
	else if ($_LOCKS[$dir]['type']==LOCK_READ)
	{
		$lock=&upgradeLock($log,$dir,$_LOCKS[$id]['lock']);
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
	return $id;
}

function unlockResource($dir)
{
	global $_LOCKS,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.locking');
	if ((isset($_LOCKS[$dir]))&&($_LOCKS[$dir]['count']>0))
	{
	  $log->debug('Unlocking '.$dir);
	  unLock($log,$dir,$_LOCKS[$dir]['lock'],$_LOCKS[$dir]['type']);
	  $_LOCKS[$dir]['count']--;
	  if ($_LOCKS[$dir]['count']==0)
	  {
	  	unset($_LOCKS[$dir]['type']);
	  	unset($_LOCKS[$dir]['lock']);
	  }
	}
	else
	{
		$log->warntrace('Attempt to unlock unlocked '.$dir);
	}
}

?>