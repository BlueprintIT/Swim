<?

/*
 * Swim
 *
 * Page viewing method for smart URLs
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_site($request)
{
	global $_USER,$_PREFS;
	
	$log=LoggerManager::getLogger("swim.method.site");
  
  $parts = explode('/',$request->resourcePath);

  if (count($parts)<3)
  {
    $log->debug('Invalid path '.$request->resourcePath);
    displayNotFound($request);
    return;
  }
  
  $container = getContainer($parts[0]);
  if ($container === null)
  {
    $log->debug('Unable to find container '.$parts[0]);
    displayNotFound($request);
    return;
  }
  
  if ($parts[2] != '-')
  {
    $page = $container->getPage($parts[2]);
    if ($page === null)
    {
      $log->debug('Unable to find page '.$parts[2]);
      displayNotFound($request);
      return;
    }
    
    $request->resource = $page;
    $request->method = 'view';
    SwimEngine::processRequest($request);
    return;
  }
  
  if ($parts[1] != '-')
  {
    return;
  }

  displayNotFound($request);
}
?>
