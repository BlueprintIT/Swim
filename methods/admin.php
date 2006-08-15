<?

/*
 * Swim
 *
 * Admin site method
 *
 * Copyright Blueprint IT Ltd. 2006
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
    setContentType($type);
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