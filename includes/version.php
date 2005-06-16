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
	global $_USER;
	
	if (!$_USER->isLoggedIn())
	{
		return false;
	}

	$result=false;
	$temp=$dir.'/temp';
	if (!is_dir($temp))
	{
		mkdir($temp);
	}
	$lock=fopen($temp.'/templock','r+');
	if (flock($lock,LOCK_EX))
	{
		$stat=fstat($lock);
		if ($stat['size']==0)
		{
			// Mark this user as owner of the temp version.
			fputs($lock,'LOCK:'.$_USER->getUsername());
			$result='temp';
		}
		else
		{
			$line=fgets($lock);
			if ($line=='LOCK:'.$_USER->getUsername())
			{
				// This user already owns the temp version.
				$result='temp';
			}
		}
		flock($lock,LOCK_UN);
	}
	fclose($lock);
	return $result;
}

// Clears the temporary version lock and wipes the temp contents.
function freeTempVersion($dir)
{
}

// Clones a version to the temporary version.
function cloneTemp($version)
{
}

// Clones a version to the next version of this resource. If source is false this clones the temporary version.
function cloneVersion($version=false)
{
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