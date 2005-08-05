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
	$lockfile = $dir.'/'.$_PREFS->getPref('locking.lockfile');
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_SH))
	{
	  $log->debug('Lock complete');
		$_LOCKS[$id]=&$file;
		return $id;
	}
	else
	{
	  $log->warn('Lock failed');
		return false;
	}
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
	$lockfile = $dir.'/'.$_PREFS->getPref('locking.lockfile');
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_EX))
	{
		$_LOCKS[$id]=&$file;
	  $log->debug('Lock complete');
		return $id;
	}
	else
	{
	  $log->warn('Lock failed');
		return false;
	}
}

function unlockResource($id)
{
	global $_LOCKS;
	
	$log=&LoggerManager::getLogger('swim.locking');
	if (isset($_LOCKS[$id]))
	{
	  $log->debug('Unlocking '.$id);
		$lock=$_LOCKS[$id];
		unset($_LOCKS[$id]);
		flock($lock,LOCK_UN);
		fclose($lock);
	}
	else
	{
		$log->warn('Attempt to unlock unlocked id '.$id);
	}
}

?>