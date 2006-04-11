<?

/*
 * Swim
 *
 * Site structure mutate method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_mutate($request)
{
	global $_USER;
	
  $log=LoggerManager::getLogger("swim.method.mutate");

  $parts=split('/',$request->resource,2);
  if ((count($parts)==2)&&($parts[1]=='categories'))
  {
		if ($_USER->hasPermission('documents',PERMISSION_WRITE))
		{
		  $cm = getContainer($parts[0]);
		  $category = $cm->getCategory($request->query['category']);
		  if ($category===null)
		  {
		  	$log->error('Category was null');
		  	displayGeneralError("Invalid category specified.");
		  	return;
		  }
		  if (isset($request->query['page']))
		  {
		  	$page = Resource::decodeResource($request->query['page']);
		  	if ($page===null)
		  	{
		  		$log->error('Page was null');
				  displayGeneralError("Invalid page specified.");
				  return;
		  	}
		  	if ($request->query['action']=='add')
		  	{
		  		$category->add($page);
          header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
          header("Content-Type: text/plain");
          print("Resource accepted");
          return;
		  	}
		  	else if ($request->query['action']=='remove')
		  	{
		  		$pos = $category->indexOf($page);
		  		if ($pos!==false)
		  			$category->remove($pos);
          header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
          header("Content-Type: text/plain");
          print("Resource accepted");
          return;
		  	}
		  	else
		  	{
		  		$log->error('Invalid action');
		  		displayGeneralError("Invalid action specified.");
		  		return;
		  	}
		  }
		}
		else
		{
		  $log->debug('No write permission');
		  header($_SERVER["SERVER_PROTOCOL"]." 401 Not Authorized");
		  print("You don't have permission to edit categories.");
		  return;
		}
	}
	else
	{
  	$log->warn('Unknown mutate to '.$request->resource);
    displayNotFound($request);
	}
}


?>