<?

/*
 * Swim
 *
 * Item viewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_view($request)
{
	global $_PREFS;
	
  $log = LoggerManager::getLogger('swim.method.view');
  checkSecurity($request, false, false);
  
  $item = Item::getItem($request->getPath());
  if ($item !== null && $item->isArchived())
    $item = null;
  if ($item !== null)
    $item = $item->getCurrentVersion(Session::getCurrentVariant());
  if ($item !== null)
  {
    if ($request->hasQueryVar('template'))
      $template = $request->getQueryVar('template');
    else
      $template = $item->getClass()->getTemplate();
	  $path = $_PREFS->getPref('storage.site.templates').'/'.$template;
	  $path = findDisplayableFile($path);
	  $type = determineContentType($path);
    $smarty = createSmarty($request, $type);
    $smarty->assign_by_ref('item', new ItemWrapper($item));
    $log->debug('Starting display.');
    setContentType($type);
    $smarty->display($path, $item->getId());
    $log->debug('Display complete.');
  }
  else
    displayNotFound($request);
}

?>