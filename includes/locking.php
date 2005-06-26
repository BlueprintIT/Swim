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
	global $_LOCKS;
	
	$log=&LoggerManager::getLogger('swim.locking');

	if ($id===false)
	{
		$id=0;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$log->info('Read locking '.$dir.' as '.$id);
	$lockfile = $dir.'/lock';
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_SH))
	{
	  $log->info('Lock complete');
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
	global $_LOCKS;
	
	$log=&LoggerManager::getLogger('swim.locking');

	if ($id===false)
	{
		$id=0;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$log->info('Write locking '.$dir.' as '.$id);
	$lockfile = $dir.'/lock';
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_EX))
	{
		$_LOCKS[$id]=&$file;
	  $log->info('Lock complete');
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
	  $log->info('Unlocking '.$id);
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