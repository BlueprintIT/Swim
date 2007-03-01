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
  global $_PREFS,$_STORAGE;
  
  $log = LoggerManager::getLogger('swim.method.layout');
  checkSecurity($request, true, true);
  
  $path = $_PREFS->getPref('storage.site.templates').'/'.$request->getPath();
  $path = findDisplayableFile($path);
  if ($path != null)
  {
    $type = determineContentType($path);
    $cachetime = filemtime($path);
    if (isTemplateFile($path))
    {
      $smarty = createSmarty($request, $type);
      $log->debug('Starting display.');
      $result = $smarty->fetch($path);
      if (!RequestCache::isCacheDefined())
        RequestCache::setCacheInfo(max($cachetime, $_STORAGE->singleQuery('SELECT MAX(published) FROM VariantVersion;')));
      setContentType($type);
      print($result);
      $log->debug('Display complete.');
    }
    else
    {
      setContentType($type);
      RequestCache::setCacheInfo($cachetime);
      include($path);
    }
  }
  else
    displayNotFound($request);
}

?>