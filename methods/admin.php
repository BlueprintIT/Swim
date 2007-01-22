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
  global $_USER, $_STORAGE, $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.admin');
  checkSecurity($request, true, true);
  
	$path = $_PREFS->getPref('storage.admin.templates').'/'.$request->getPath();
  $path = findDisplayableFile($path);
  if ($path != null)
  {
    $type = determineContentType($path);
    if (($_PREFS->isPrefSet('admin.offline')) && ($type == 'text/html') && ($request->getPath() != 'offline.tpl'))
    {
    	$offline = new Request();
    	$offline->setMethod('admin');
    	$offline->setPath('offline.tpl');
    	redirect($offline);
    	return;
    }
    setContentType($type);
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
      $smarty->display($path);
      $log->debug('Display complete.');
    }
    else
      include($path);
  }
  else
    displayNotFound($request);
}

?>