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

function mkdirGetLock(&$log,$lockdir)
{
	global $_PREFS;

	$staleage=$_PREFS->getPRef('locking.staleage');
	
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
		$locked=@mkdir($lockdir);
	}
}

function &mkdirRead(&$log,$dir,$id)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	mkdirGetLock($log,$lockdir);
	if (is_file($lockfile))
	{
		$file=fopen($lockfile,'a+');
		if ($file===false)
		{
			$log->error('Error opening lockfile '.$lockfile);
			rmdir($lockdir);
			return false;
		}
		fseek($file,0);
		$count=fgets($file);
		fseek($file,0);
		fwrite($file,$count+1);
		fclose($file);
	}
	else
	{
		$file=fopen($lockfile,'w');
		if ($file===false)
		{
			$log->error('Error opening lockfile '.$lockfile);
			rmdir($lockdir);
			return false;
		}
		fwrite($file,'1');
		fclose($file);
	}
	rmdir($lockdir);
	return array($dir,'read');
}

function &mkdirWrite(&$log,$dir,$id)
{
	global $_PREFS;
	$lockdir=$dir.'/'.$_PREFS->getPref('locking.mkdirlock');
	$lockfile=$dir.'/'.$_PREFS->getPref('locking.lockfile');
	mkdirGetLock($log,$lockdir);
	while (is_file($lockfile))
	{
		rmdir($lockdir);
		sleep(1);
		mkdirGetLock($log,$lockdir);
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
		mkdirGetLock($log,$lockdir);
		$file=fopen($lockfile,'r+');
		$count=fgets($file);
		if ($count>1)
		{
			fseek($file,0);
			fwrite($file,$count-1);
			fclose($file);
		}
		else
		{
			fclose($file);
			unlink($lockfile);
		}
	}
	rmdir($lockdir);
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