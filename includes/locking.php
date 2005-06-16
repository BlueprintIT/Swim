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
	if (isset($_LOCKS[$id]))
	{
		$lock=$_LOCKS[$id];
		unset($_LOCKS[$id]);
		flock($lock,LOCK_UN);
		fclose($lock);
	}
}

?>