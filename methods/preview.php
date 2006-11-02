<?

/*
 * Swim
 *
 * Item previewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_preview($request)
{
	global $_PREFS;
	
  $log = LoggerManager::getLogger('swim.method.preview');
  checkSecurity($request, false, false);
  
  $parts = explode('/', $request->getPath(), 4);
  if (count($parts)>=3)
  {
  	$item = Item::getItem($parts[0]);
  	if ($item !== null)
  		$item = $item->getVariant($parts[1]);
  	if ($item !== null)
  		$item = $item->getVersion($parts[2]);
  	if (count($parts)>3)
  		$extra = $parts[3];
  	else
  		$extra = null;
  }
  else
  	$item = null;
  	
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