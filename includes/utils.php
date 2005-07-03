<?

/*
 * Swim
 *
 * Utility functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

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

function httpdate($date)
{
	return gmdate("D, j M Y G:i:s",$date).' GMT';
}

function displayLogin(&$request)
{
	$newrequest = new Request();
	$newrequest->method='displayLogin';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

function displayError(&$request)
{
	$newrequest = new Request();
	$newrequest->method='error';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

function setModifiedDate($date)
{
	//header('Cache-Control: public');
	//header('Pragma: ');
	header('Last-Modified: '.httpdate($date));
	//header('Expires: '.httpdate(time()+3600));
}

function callMethod(&$request)
{
	global $_PREFS;
	
	$methodfile=$request->method.".php";
	$methodfunc='method_'.$request->method;
	if (is_readable($_PREFS->getPref('storage.methods')))
	{
		require_once($_PREFS->getPref('storage.methods').'/'.$methodfile);
		$methodfunc($request);
	}
}

?>