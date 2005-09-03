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

function &flockRead(&$log,$dir,$id)
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

function &flockWrite(&$log,$dir,$id)
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

function flockUnlock(&$log,$id,&$lock)
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

function &mkdirRead(&$log,$dir,$id)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	$staleage=$_PREFS->getPref('locking.staleage');
	mkdirGetLock($log,$lockdir,$staleage);

	if ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockdir))<$staleage))
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
	return array($dir,'read');
}

function &mkdirWrite(&$log,$dir,$id)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	$staleage=$_PREFS->getPref('locking.staleage');
	mkdirGetLock($log,$lockdir,$staleage);
	while ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockdir))<$staleage))
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
	return array($dir,'write');
}

function mkdirUnlock(&$log,$id,&$lock)
{
	global $_PREFS;
	$dir=$lock[0];
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	if ($lock[1]=='read')
	{
		$staleage=$_PREFS->getPref('locking.staleage');
		mkdirGetLock($log,$lockdir,$staleage);

		if ((is_file($lockfile))&&(filesize($lockfile)>0)&&((time()-filemtime($lockdir))<$staleage))
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

function lockResourceRead($dir,$id=false)
{
	global $_LOCKS,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.locking');

	if ($id===false)
	{
		$id=1;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$log->debug('Read locking '.$dir.' as '.$id);
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  $_LOCKS[$id]=&flockRead($log,$dir,$id);
 	}
  else if ($type=='mkdir')
 	{
	  $_LOCKS[$id]=&mkdirRead($log,$dir,$id);
 	}
 	else if ($type=='none')
 	{
 		$_LOCKS[$id]=true;
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 		$_LOCKS[$id]=true;
 	}
 	
	if ($_LOCKS[$id]!==false)
	{
	  $log->debug('Lock complete');
	}
	else
	{
	  $log->warntrace('Lock failed on dir '.$dir);
	}
	return $id;
}

function lockResourceWrite($dir,$id=false)
{
	global $_LOCKS,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.locking');

	if ($id===false)
	{
		$id=0;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$log->debug('Write locking '.$dir.' as '.$id);
  $type=$_PREFS->getPref('locking.type','flock');
  if ($type=='flock')
 	{
	  $_LOCKS[$id]=&flockWrite($log,$dir,$id);
 	}
  else if ($type=='mkdir')
 	{
	  $_LOCKS[$id]=&mkdirWrite($log,$dir,$id);
 	}
 	else if ($type=='none')
 	{
 		$_LOCKS[$id]=true;
 	}
 	else
 	{
 		$log->error('No valid locking type specified');
 		$_LOCKS[$id]=true;
 	}
 	
	if ($_LOCKS[$id]!==false)
	{
	  $log->debug('Lock complete');
	}
	else
	{
	  $log->warntrace('Lock failed on dir '.$dir);
	}
	return $id;
}

function unlockResource($id)
{
	global $_LOCKS,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.locking');
	if (isset($_LOCKS[$id]))
	{
		if ($_LOCKS[$id]!==false)
		{
		  $log->debug('Unlocking '.$id);
		  $type=$_PREFS->getPref('locking.type','flock');
		  if ($type=='flock')
		 	{
			  flockUnlock($log,$id,$_LOCKS[$id]);
		 	}
		  else if ($type=='mkdir')
		 	{
			  mkdirUnlock($log,$id,$_LOCKS[$id]);
		 	}
		 	else if ($type=='none')
		 	{
		 	}
		 	else
		 	{
		 		$log->error('No valid locking type specified');
		 	}
		}
 		unset($_LOCKS[$id]);
	}
	else
	{
		$log->warntrace('Attempt to unlock unlocked id '.$id);
	}
}

?>