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

// Returns the temporary version of a resource.
// This also adds a permanent lock stopping anyone else retrieving the temp version or overwriting
// its contents.
function getTempVersion($dir)
{
	global $_USER,$_PREFS;
	
	$log=&LoggerManager::getLogger('swim.version');
	
	if (!$_USER->isLoggedIn())
	{
		return false;
	}

	$result=false;
	$temp=$dir.'/temp';
	if (!is_dir($temp))
	{
		$log->info('Created temp version at '.$temp);
		mkdir($temp);
	}
	
	$lock=lockResourceWrite($temp);
	if (is_file($temp.'/'.$_PREFS->getPref('locking.templockfile')))
	{
		$file=fopen($temp.'/'.$_PREFS->getPref('locking.templockfile'),'r');
		$line=trim(fgets($file));
		fclose($file);
		if ($line==$_USER->getUsername())
		{
			$result='temp';
		}
	}
	else
	{
		$file=fopen($temp.'/'.$_PREFS->getPref('locking.templockfile'),'w');
		fwrite($file,$_USER->getUsername());
		fclose($file);
		$result='temp';
	}
	unlockResource($lock);
	return $result;
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

// Clears the temporary version lock and wipes the temp contents.
function freeTempVersion($dir)
{
	global $_PREFS;
	
	$temp=getTempVersion($dir);
	if ($temp!==false)
	{
		$lock=lockResourceWrite($dir.'/'.$temp);
		recursiveDelete($dir.'/'.$temp,true);
		unlink($dir.'/'.$temp.'/'.$_PREFS->getPref('locking.templockfile'));
		unlockResource($lock);
		return true;
	}
	return false;
}

// Clones a version to the temporary version. This sets the lock on the temp version for the current user
function cloneTemp($dir,$version)
{
	$next=getTempVersion($dir);
	if ($next!==false)
	{
		$log=&LoggerManager::getLogger('swim.version');
		$log->debug('Attempt to copy from '.$dir.'/'.$version.' to '.$dir.'/'.$next);
		$targetlock=lockResourceWrite($dir.'/'.$next);
		$sourcelock=lockResourceRead($dir.'/'.$version);
		recursiveCopy($dir.'/'.$version,$dir.'/'.$next,true);
		unlockResource($sourcelock);
		unlockResource($targetlock);
		return $next;
	}
	return false;
}

// Clones a version to the next version of this resource. If source is false this clones the temporary version.
function cloneVersion($dir,$version=false)
{
	$next=createNextVersion($dir);
	if ($version===false)
	{
		$version=getTempVersion($dir);
	}
	$targetlock=lockResourceWrite($dir.'/'.$next);
	$sourcelock=lockResourceRead($dir.'/'.$version);
	recursiveCopy($dir.'/'.$version,$dir.'/'.$next,true);
	unlockResource($sourcelock);
	unlockResource($targetlock);
	return $next;
}

// Returns the next version of a resource
function createNextVersion($dir)
{
	$lock=fopen($dir.'/lock','a');
	flock($lock,LOCK_EX);
	
	$newest=-1;
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if (!(substr($file,0,1)=='.'))
			{
				if ((is_dir($dir.'/'.$file))&&(is_numeric($file)))
				{
					if ($file>$newest)
					{
						$newest=$file;
					}
				}
			}
		}
		closedir($res);
	}
	if ($newest>=0)
	{
		$next=$newest+1;
	}
	else
	{
		$next=1;
	}
	
	mkdir($dir.'/'.$next);

	flock($lock,LOCK_UN);
	fclose($lock);
	
	return $next;
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