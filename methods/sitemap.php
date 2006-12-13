<?

/*
 * Swim
 *
 * Google Sitemap method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_sitemap($request)
{
  global $_USER, $_STORAGE, $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.admin');
  checkSecurity($request, false, true);
  
  setNoCache();
  
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