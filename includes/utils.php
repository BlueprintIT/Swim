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

function displayAdminLogin($request)
{
	displayLogin($request,'You must log in to administer this website.');
}

function displayLogin($request,$message)
{
  global $_PREFS;
  displayAdminFile($request, $_PREFS->getPref('storage.admin.templates').'/login', array('message' => $message));
}

function displayAdminFile($request, $path, $vars = array())
{
  $file = findDisplayableFile($path);
  if ($file !== null)
  {
    if (isTemplateFile($file))
    {
      $smarty = createAdminSmarty($request);
      $smarty->assign($vars);
      $smarty->display($file);
    }
    else
      include($file);
  }
  else
    displayNotFound($request);
}

function displayGeneralError($request,$message)
{
	global $_PREFS;
  
	$log = LoggerManager::getLogger('swim');
	$log->errorTrace('General Error - '.$message);
	RequestCache::setNoCache();
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->assign('message', $message);
  $smarty->display($_PREFS->getPref('errors.general.template'));
}

function displayNotFound($request)
{
	global $_PREFS;

	RequestCache::setNoCache();
 	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.notfound.template'));
}

function displayServerError($request)
{
	global $_PREFS;

	$log = LoggerManager::getLogger('swim');
	$log->errorTrace('Server Error');
	RequestCache::setNoCache();
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
  $smarty = createSmarty($request);
  $smarty->display($_PREFS->getPref('errors.server.template'));
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