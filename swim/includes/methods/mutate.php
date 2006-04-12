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
		  if (isset($request->query['item']))
		  {
		  	$pos = $request->query['item'];
		  	$item = $category->item($pos);
		  	if ($item === null)
		  	{
		  		$log->error('Invalid item - '.$pos.' of '.count($items));
		  		foreach ($items as $k => $i)
		  		{
		  			$log->error($k." => ".get_class($i));
		  		}
		  		displayGeneralError("Invalid item specified.");
		  		return;
		  	}
		  	
		  	if ($request->query['action']=='moveup')
		  	{
		  		$log->debug('Move item '.get_class($item).' to '.($pos-1));
		  		$category->insert($item, $pos-1);
		  	}
		  	else if ($request->query['action']=='movedown')
		  	{
		  		$log->debug('Move item '.get_class($item).' to '.($pos+1));
		  		$category->insert($item, $pos+1);
		  	}
		  	else if ($request->query['action']=='remove')
		  	{
		  		if ($pos!==false)
		  			$category->remove($pos);
		  	}
		  	else
		  	{
		  		$log->error('Invalid action');
		  		displayGeneralError("Invalid action specified.");
		  		return;
		  	}
		  }
		  else if (isset($request->query['page']))
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
		  		$log->debug('Adding page');
		  		$category->add($page);
		  	}
		  	else if ($request->query['action']=='remove')
		  	{
		  		$pos = $category->indexOf($page);
		  		if ($pos!==false)
		  		{
			  		$log->debug('Adding page');
		  			$category->remove($pos);
		  		}
		  		else
		  		{
			  		$log->error('Page does not exist in category');
		  		}
		  	}
		  	else
		  	{
		  		$log->error('Invalid action');
		  		displayGeneralError("Invalid action specified.");
		  		return;
		  	}
		  }
		  else if (isset($request->query['subcategory']))
		  {
		  	$subcat = $cm->getCategory($request->query['subcategory']);
		  	if ($subcat!==null)
		  	{
		  		$parent = $category;
		  		while ($parent!==null)
		  		{
		  			if ($parent===$subcat)
		  			{
				  		$log->error('Invalid move specified');
				  		displayGeneralError("Invalid move specified.");
				  		return;
		  			}
		  			$parent = $parent->parent;
		  		}
		  		$log->debug('Moving category');
		  		$category->add($subcat);
		  	}
		  	else
		  	{
		  		$log->error('Invalid sub category');
		  		displayGeneralError("Invalid category specified.");
		  		return;
		  	}
		  }
      header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
      header("Content-Type: text/plain");
      print("Resource accepted");
      return;
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