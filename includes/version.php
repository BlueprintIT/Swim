<?

/*
 * Swim
 *
 * Version control functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// Given the root dir of a resource this functon will return the dir of the current version of the resource.
// The current version is not necessarily the newest version, but usually will be.
function getCurrentResource($dir)
{
	$newest=getCurrentVersion($dir);
	if ($newest===false)
	{
		return false;
	}
	else
	{
		return $dir.'/'.$newest;
	}
}

function setCurrentVersion($dir,$version)
{
	$lock=fopen($dir.'/lock','a');
	flock($lock,LOCK_EX);
	
	$vers=fopen($dir.'/version','w');
	fwrite($vers,$version);
	fclose($vers);
	
	flock($lock,LOCK_UN);
	fclose($lock);
}

// Retrieves the latest version of a resource.
function getCurrentVersion($dir)
{
	if (is_readable($dir.'/version'))
	{
		$lock=fopen($dir.'/lock','a');
		flock($lock,LOCK_SH);
		
		$vers=fopen($dir.'/version','r');
		$version=fgets($vers);
		fclose($vers);
		
		flock($lock,LOCK_UN);
		fclose($lock);
	
		if (is_dir($dir.'/'.$version))
		{
			return $version;
		}
	}
	return false;
}

// Retrieves a particular version of a resource.
function getResourceVersion($dir,$version)
{
	if (is_dir($dir.'/'.$version))
	{
		return $dir.'/'.$version;
	}
	return false;
}

 ?>