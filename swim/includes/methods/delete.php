<?

/*
 * Swim
 *
 * Cancels block editing
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_delete($request)
{
  global $_USER;
  
  checkSecurity($request, true, true);
  
  $resource = $request->resource;

  if ($resource!==null)
  {
    if ($_USER->canWrite($resource))
    {
      $resource->delete();
      redirect($request->nested);
    }
    else
    {
      displayAdminLogin($request);
    }
  }
  else
  {
  	$parts = explode('/',$request->requestPath);
  	if ((count($parts)==3)&&($parts[1]=='categories'))
  	{
  		$container = getContainer($parts[0]);
  		if ($container !== null)
  		{
  			$category = $container->getCategory($parts[2]);
  			if ($category !== null)
  			{
  				$category->delete();
  				redirect($request->nested);
  			}
  			else
  			{
			    displayNotFound($request);
  			}
  		}
  		else
  		{
		    displayNotFound($request);
  		}
  	}
  	elseif ((count($parts)==3)&&($parts[1]=='links'))
  	{
  		$container = getContainer($parts[0]);
  		if ($container !== null)
  		{
  			$link = $container->getLink($parts[2]);
  			if ($link !== null)
  			{
  				$link->delete();
  				redirect($request->nested);
  			}
  			else
  			{
			    displayNotFound($request);
  			}
  		}
  		else
  		{
		    displayNotFound($request);
  		}
  	}
  	else
  	{
	    displayNotFound($request);
	  }
  }
}

?>