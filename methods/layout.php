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
		switch ($type)
		{
			case 'text/css':
			case 'text/javascript':
	  		RequestCache::setCacheInfo(filemtime($path));
				break;
			default:
		}
    if (isTemplateFile($path))
    {
      $smarty = createSmarty($request, $type);
      $log->debug('Starting display.');
      $smarty->display($path);
      $result = $smarty->fetch($path);
      if (!RequestCache::isCacheDefined())
        RequestCache::setCacheInfo($_STORAGE->singleQuery('SELECT MAX(published) FROM VariantVersion;'));
      setContentType($type);
      print($result);
      $log->debug('Display complete.');
    }
    else
    {
      setContentType($type);
      RequestCache::setNoCache();
      include($path);
    }
  }
  else
    displayNotFound($request);
}

?>