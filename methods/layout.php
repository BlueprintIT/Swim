<?

/*
 * Swim
 *
 * Site templates
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_layout($request)
{
  global $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.layout');
  checkSecurity($request, true, true);
  
  $path = $_PREFS->getPref('storage.site.templates').'/'.$request->getPath();
  $path = findDisplayableFile($path);
  if ($path != null)
  {
    $type = determineContentType($path);
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
      $smarty = createSmarty($request, $type);
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