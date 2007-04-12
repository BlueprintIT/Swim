<?

/*
 * Swim
 *
 * Item viewing method
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/view.php $
 * $LastChangedBy: dave $
 * $Date: 2007-04-05 14:48:05 +0100 (Thu, 05 Apr 2007) $
 * $Revision: 1414 $
 */

function method_robots_txt($request)
{
  global $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.robots.txt');
  checkSecurity($request, false, true);
  
  RequestCache::setNoCache();
  
  $path = $_PREFS->getPref('storage.admin.templates').'/robots.tpl.txt';
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
