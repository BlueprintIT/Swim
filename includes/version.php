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

function recursiveDelete($dir,$ignorelock=false)
{
	global $_PREFS;
	
	$log=&LoggerManager::getLogger('swim.version');
	$log->debug('Deleting '.$dir);
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if ($file[0]!='.')
			{
				if ((($file==$_PREFS->getPref('locking.lockfile'))||($file==$_PREFS->getPref('locking.templockfile')))&&($ignorelock))
				{
					$log->debug('Ignoring lock file '.$file);
					continue;
				}
				if ((is_file($dir.'/'.$file))||(is_link($dir.'/'.$file)))
				{
					unlink($dir.'/'.$file);
				}
				else if (is_dir($dir.'/'.$file))
				{
					recursiveDelete($dir.'/'.$file);
					rmdir($dir.'/'.$file);
				}
				else
				{
					$log->warn('Found unknown directory entry at '.$dir.'/'.$file);
					unlink($dir.'/'.$file);
				}
			}
		}
		closedir($res);
	}
}

function recursiveCopy($dir,$target,$ignorelock=false)
{
	global $_PREFS;
	
	$log=&LoggerManager::getLogger('swim.version');
	$log->debug('Copying files from '.$dir.' to '.$target);
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if ($file[0]!='.')
			{
				if ((($file==$_PREFS->getPref('locking.lockfile'))||($file==$_PREFS->getPref('locking.templockfile')))&&($ignorelock))
				{
					$log->debug('Ignoring lock file '.$file);
					continue;
				}
				if ((is_file($dir.'/'.$file))||(is_link($dir.'/'.$file)))
				{
					copy($dir.'/'.$file,$target.'/'.$file);
				}
				else if (is_dir($dir.'/'.$file))
				{
					mkdir($target.'/'.$file);
					recursiveCopy($dir.'/'.$file,$target.'/'.$file);
				}
				else
				{
					$log->warn('Found unknown directory entry at '.$dir.'/'.$file);
					copy($dir.'/'.$file,$target.'/'.$file);
				}
			}
		}
		closedir($res);
	}
}

function setCurrentVersion($dir,$version)
{
	$lock=lockResourceWrite($dir);
	
	$vers=fopen($dir.'/version','w');
	fwrite($vers,$version);
	fclose($vers);
	
	unlockResource($lock);
}

// Retrieves the latest version of a resource.
function getCurrentVersion($dir)
{
	if (is_readable($dir.'/version'))
	{
		$lock=lockResourceRead($dir);
		
		$vers=fopen($dir.'/version','r');
		$version=fgets($vers);
		fclose($vers);
		
		unlockResource($lock);
	
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