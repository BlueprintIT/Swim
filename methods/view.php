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
  
  $pos = strpos($request->getPath(), '/');
  if ($pos === false)
  {
	  $id = $request->getPath();
	  $extra = null;
  }
	else
	{
		$id = substr($request->getPath(), 0, $pos);
		$extra = substr($request->getPath(), $pos+1);
	}
	$item = Item::getItem($id);
	
  if ($item !== null && $item->isArchived())
    $item = null;
  if ($item !== null)
    $item = $item->getCurrentVersion(Session::getCurrentVariant());
  if ($item !== null)
  {
    if ($request->hasQueryVar('template'))
    {
      $template = $request->getQueryVar('template');
		  $template = $_PREFS->getPref('storage.site.templates').'/'.$template;
		  $template = findDisplayableFile($template);
    }
    else
      $template = $item->getClass()->getTemplate($extra);

    if ($template !== null)
    {
		  $type = determineContentType($template);
	    $smarty = createSmarty($request, $type);
	    $smarty->assign_by_ref('item', ItemWrapper::getWrapper($item));
	    $log->debug('Starting display.');
	    setContentType($type);
	    $smarty->display($template, $item->getId());
	    $log->debug('Display complete.');
    }
	  else
	  	displayNotFound($request);
  }
  else
    displayNotFound($request);
}

?>