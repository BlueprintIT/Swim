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

function displayGeneralError(&$request,$message)
{
	global $_PREFS;
	$container = &getContainer('internal');
	$page = &$container->getPage($_PREFS->getPref('errors.general.page'));
	$request->query['message']=$message;
	if ($page!==false)
	{
		$page->display($request);
	}
	else
	{
		// TODO What to do here?
	}
}

function displayNotFound(&$request)
{
	global $_PREFS;
	$container = &getContainer('internal');
	$page = &$container->getPage($_PREFS->getPref('errors.notfound.page'));
	if ($page!==false)
	{
		$page->display($request);
	}
	else
	{
		// TODO What to do here?
	}
}

function displayServerError(&$request)
{
	global $_PREFS;
	$container = &getContainer('internal');
	$page = &$container->getPage($_PREFS->getPref('errors.server.page'));
	if ($page!==false)
	{
		$page->display($request);
	}
	else
	{
		// TODO What to do here?
	}
}

function setValidTime($minutes)
{
	header('Cache-Control: max-age='.($minutes*60).', public');
	header('Pragma: cache');
	header('Expires: '.httpdate(time()+($minutes*60)));
}

function setModifiedDate($date)
{
	header('Last-Modified: '.httpdate($date));
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