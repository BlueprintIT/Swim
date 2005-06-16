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
	
	if ($id===false)
	{
		$id=0;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$lockfile = $dir.'/lock';
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_SH))
	{
		$_LOCKS[$id]=&$file;
		return $id;
	}
	else
	{
		return false;
	}
}

function lockResourceWrite($dir,$id=false)
{
	global $_LOCKS;
	
	if ($id===false)
	{
		$id=0;
		while (isset($_LOCKS[$id]))
		{
			$id++;
		}
	}
	
	$lockfile = $dir.'/lock';
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_EX))
	{
		$_LOCKS[$id]=&$file;
		return $id;
	}
	else
	{
		return false;
	}
}

function unlockResource($id)
{
	global $_LOCKS;
	
	if (isset($_LOCKS[$id]))
	{
		$lock=$_LOCKS[$id];
		unset($_LOCKS[$id]);
		flock($lock,LOCK_UN);
		fclose($lock);
	}
	else
	{
		$log=LoggerManager::getLogger('swim.locking');
		$log->warn('Attempt to unlock unlocked id '.$id);
	}
}

?>