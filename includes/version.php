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
function getCurrentVersion($dir)
{
	$newest=-1;
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if (!(substr($file,0,1)=="."))
			{
				if ((is_dir($dir."/".$file))&&(is_numeric($file)))
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
		return $dir."/".$newest;
	}
	else
	{
		return false;
	}
}

 ?>