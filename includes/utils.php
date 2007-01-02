<?

/*
 * Swim
 *
 * Utility functions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function linkorcopy($source, $target)
{
	if (is_dir($source))
	{
		if ((function_exists("symlink")) && (symlink($source,$target)))
			return true;
		mkdir($target);
		recursiveCopy($source, $target, true);
	}
	else
	{
		if ((function_exists("link")) && (link($source,$target)))
			return true;
		return copy($source, $target);
	}
}

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
		return true;
		
	if (!is_dir($dir))
	{
		if (recursiveMkDir(dirname($dir)))
			return mkdir($dir);
		return false;
	}
	return true;
}

function recursiveDelete($dir)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger('swim.utils.delete');
	$log->debug('Deleting '.$dir);
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if (($file!='.')&&($file!='..'))
			{
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

function recursiveCopy($dir, $target, $uselinks = false)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger('swim.utils.copy');
	$log->debug('Copying files from '.$dir.' to '.$target);
	if ($res=@opendir($dir))
	{
		while (($file=readdir($res))!== false)
		{
			if (($file!='.') && ($file!='..'))
			{
        if (is_link($dir.'/'.$file))
        {
          $target = $dir.'/'.$file;
          while (is_link($target))
            $target = readlink($target);
          if ($uselinks) 
          	linkorcopy($dir.'/'.$file, $target.'/'.$file);
          else
            copy($dir.'/'.$file, $target.'/'.$file);
        }
        else if (is_file($dir.'/'.$file))
        {
          if ($uselinks)
          	linkorcopy($dir.'/'.$file, $target.'/'.$file);
          else
            copy($dir.'/'.$file, $target.'/'.$file);
        }
				else if (is_dir($dir.'/'.$file))
				{
					mkdir($target.'/'.$file);
					recursiveCopy($dir.'/'.$file, $target.'/'.$file, $uselinks);
				}
				else
				{
					$log->warn('Found unknown directory entry at '.$dir.'/'.$file);
					copy($dir.'/'.$file, $target.'/'.$file);
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
  
	$log = LoggerManager::getLogger('swim');
	$log->errorTrace('General Error - '.$message);
	setNoCache();
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->assign('message', $message);
  $smarty->display($_PREFS->getPref('errors.general.template'));
}

function displayNotFound($request)
{
	global $_PREFS;

	setNoCache();
 	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.notfound.template'));
}

function displayServerError($request)
{
	global $_PREFS;

	$log = LoggerManager::getLogger('swim');
	$log->errorTrace('Server Error');
	setNoCache();
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.server.template'));
}

function setNoCache()
{
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
	header('Expires: '.httpdate(time()-3600));
}

function setCacheTime($minutes)
{
	header('Cache-Control: max-age='.($minutes*60).', public');
	header('Pragma: cache');
	header('Expires: '.httpdate(time()+($minutes*60)));
}

function setCacheInfo($date,$etag=false)
{
	$log=LoggerManager::getLogger('swim.cache');

	header('Cache-Control: must-revalidate');
	if ($date!=false)
		header('Last-Modified: '.httpdate($date));
	if ($etag!==false)
		header('ETag: '.$etag);

	if (((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
		||((isset($_SERVER['HTTP_IF_NONE_MATCH'])))&&($etag!==false))
	{
		$log->debug('Found a cache check header');
		if ((isset($_SERVER['HTTP_IF_NONE_MATCH']))&&($etag!==false))
		{
			$log->debug('Checking etag');
			if ($etag!=$_SERVER['HTTP_IF_NONE_MATCH'])
			{
				$log->debug('ETag differs');
				return;
			}
		}
		if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
		{
			$log->debug('Checking modification date');
			$checkdate=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if ($checkdate!=$date)
			{
				$log->debug('Date differs');
				return;
			}
		}
		$log->debug('Resource is cached');
		header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
		SwimEngine::shutdown();
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