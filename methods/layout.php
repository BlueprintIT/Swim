<?

/*
 * Swim
 *
 * Site templates
 *
 * Copyright Blueprint IT Ltd. 2006
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
		/*if (($type == 'text/css') || ($type == 'text/javascript'))
		{
			setValidTime(60);
			setCacheInfo(filemtime($path));
		}*/
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