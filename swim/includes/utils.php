<?

/*
 * Swim
 *
 * Utility functions
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function getReadableFileSize($path)
{
	$units = array('bytes','KB','MB','GB','TB');
	$filesize = filesize($path);
	
	$un=0;
	while (($filesize>1024)&&($un<(count($units)-1)))
	{
		$filesize=$filesize/1024;
		$un++;
	}
	$filesize = round($filesize, 2);
	return $filesize." ".$units[$un];
}

function recursiveMkDir($dir)
{
	if ((strlen($dir)==0)||($dir=='.'))
		return;
		
	if (!is_dir($dir))
	{
		recursiveMkDir(dirname($dir));
		mkdir($dir);
	}
}

function recursiveDelete($dir,$ignorelock=false)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger('swim.utils.delete');
	$log->debug('Deleting '.$dir);
	if ($res=@opendir($dir))
	{
		$lockfiles=LockManager::getLockFiles();
		while (($file=readdir($res))!== false)
		{
			if (($file!='.')&&($file!='..'))
			{
				if (((in_array($file,$lockfiles))||($file==$_PREFS->getPref('locking.templockfile')))&&($ignorelock))
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
	
	$log=LoggerManager::getLogger('swim.utils.copy');
	$log->debug('Copying files from '.$dir.' to '.$target);
	if ($res=@opendir($dir))
	{
		$lockfiles=LockManager::getLockFiles();
		while (($file=readdir($res))!== false)
		{
			if (($file!='.')&&($file!='..'))
			{
				if (((in_array($file,$lockfiles))||($file==$_PREFS->getPref('locking.templockfile')))&&($ignorelock))
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
					recursiveCopy($dir.'/'.$file,$target.'/'.$file,$ignorelock);
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

function formatdate($date)
{
	return date('g:ia d/m/Y',$date);
}

function displayLocked($request,$details,$resource)
{
	header($_SERVER["SERVER_PROTOCOL"]." 409 Conflict");
	$request->data['details']=$details;
	$request->data['resource']=$resource;
	$container = getContainer('internal');
	$page=$container->getPage('locked');
	if ($page!==null)
	{
		$page->display($request);
	}
}

function displayAdminLogin($request)
{
	displayLogin($request,'You must log in to administer this website.');
}

function displayLogin($request,$message)
{
  global $_PREFS;
  displayAdminFile($request, $_PREFS->getPref('storage.admin.templates').'/login', array('message' => $message));
}

function displayAdminFile($request, $path, $vars)
{
  if (is_file($path.'.tpl'))
  {
    $smarty = createAdminSmarty($request);
    $smarty->assign($vars);
    $smarty->display($path.'.tpl');
  }
  else if (is_file($path.'.php'))
    include($path.'.php');
  else if (is_file($path.'.html'))
    include($path.'.html');
  else
    displayNotFound($request);
}

function displayGeneralError($request,$message)
{
	global $_PREFS;
  
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->assign('message', $message);
  $smarty->display($_PREFS->getPref('errors.general.template'));
}

function displayNotFound($request)
{
	global $_PREFS;

 	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.notfound.template'));
}

function displayServerError($request)
{
	global $_PREFS;

	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.server.template'));
}

function setDefaultCache()
{
	header('Cache-Control: must-revalidate');
	header('Pragma:');
}

function setValidTime($minutes)
{
	header('Cache-Control: max-age='.($minutes*60).', public');
	header('Pragma: cache');
	header('Expires: '.httpdate(time()+($minutes*60)));
}

function setCacheInfo($date,$etag=false)
{
	$log=LoggerManager::getLogger('swim.cache');
	$cached=false;

	if ($date!=false)
		header('Last-Modified: '.httpdate($date));
	if ($etag!==false)
		header('ETag: '.$etag);

	if (((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
		||((isset($_SERVER['HTTP_IF_NONE_MATCH'])))&&($etag!==false))
	{
		$log->debug('Found a cache check header');
		$cached=true;
		if ((isset($_SERVER['HTTP_IF_NONE_MATCH']))&&($etag!==false))
		{
			$log->debug('Checking etag');
			if ($etag!=$_SERVER['HTTP_IF_NONE_MATCH'])
			{
				$log->debug('ETag differs');
				$cached=false;
			}
		}
		if (($cached)&&(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
		{
			$log->debug('Checking modification date');
			$checkdate=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if ($checkdate!=$date)
			{
				$log->debug('Date differs');
				$cached=false;
			}
		}
		if ($cached)
		{
			$log->debug('Resource is cached');
			header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
			SwimEngine::shutdown();
		}
	}
}

function saveSitePreferences()
{
	global $_PREFS;
	
	$confdir=$host->getPref('storage.config');
	LockManager::lockResourceWrite($confdir);
	$file=fopen($_PREFS->getPref('storage.config').'/site.conf','w');
	$_PREFS->savePreferences($file);
	fclose($file);
	LockManager::unlockResource($confdir);
}

function findDisplayableFile($path)
{
  if (is_file($path))
    return $path;
    
  if (is_dir($path))
  {
    $found = findDisplayableFile($path.'/index.php');
    if ($found != null)
      return $found;

    $found = findDisplayableFile($path.'/index.html');
    if ($found != null)
      return $found;

    if (is_file($path.'/index.tpl'))
      return $path.'/index.tpl';
    
    return null;
  }
  
  if (is_file($path.'.tpl'))
    return $path.'.tpl';
    
  $pos = strrpos($path,'.');
  if ($pos !== false)
  {
    $path = substr($path, 0 ,$pos).'.tpl'.substr($path, $pos);
    if (is_file($path))
      return $path;
  }
  
  return null;
}

function isTemplateFile($path)
{
  if (substr($path,-4)=='.tpl')
    return true;
    
  if (strpos($path, '.tpl.')!==false)
    return true;

  return false;
}

?>