<?

/*
 * Swim
 *
 * Admin site method
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_admin($request)
{
  global $_STORAGE, $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.admin');
  checkSecurity($request, true, true);
  
	$path = $_PREFS->getPref('storage.admin.templates').'/'.$request->getPath();
  $path = findDisplayableFile($path);
  if ($path != null)
  {
    $type = determineContentType($path);
    if (($_PREFS->isPrefSet('admin.offline')) && ($type == 'text/html') && ($request->getPath() != 'offline.tpl'))
    {
      displayAdminFile($request, $_PREFS->getPref('storage.admin.templates').'/offline');
    	return;
    }
		switch ($type)
		{
			case 'text/css':
			case 'text/javascript':
	  		RequestCache::setCacheInfo(filemtime($path));
				break;
			default:
				RequestCache::setNoCache();
		}
    if (isTemplateFile($path))
    {
      $smarty = createAdminSmarty($request, $type);
      $log->debug('Starting display.');
      $result = $smarty->fetch($path);
      setContentType($type);
      print($result);
      $log->debug('Display complete.');
    }
    else
    {
      setContentType($type);
      include($path);
    }
  }
  else
    displayNotFound($request);
}

?>