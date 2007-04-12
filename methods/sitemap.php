<?

/*
 * Swim
 *
 * Google Sitemap method
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_sitemap($request)
{
  global $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.sitemap');
  checkSecurity($request, false, true);
  
  RequestCache::setNoCache();
  
  $path = $_PREFS->getPref('storage.admin.templates').'/sitemap.tpl.xml';
  if ($path != null)
  {
    $type = determineContentType($path);
    setContentType($type);
    $smarty = createAdminSmarty($request, $type);
    $log->debug('Starting display.');
    $smarty->display($path);
    $log->debug('Display complete.');
  }
  else
    displayNotFound($request);
}

?>