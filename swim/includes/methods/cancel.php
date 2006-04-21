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

function method_cancel($request)
{
	global $_USER;
	
  checkSecurity($request, true, true);
  
	$resource = $request->resource;

	if ($resource!==null)
	{
		if ($_USER->canWrite($resource))
		{
			$details=$resource->getWorkingDetails();
			if ($details->isMine())
			{
				$details->free();
			}
      if (($resource instanceof Page)&&(!isset($resource->parent)))
      {
        $vers = array_keys($resource->getVersions());
        if ((count($vers==1))&&($vers[0]=='base'))
        {
          if ($request->nested->resource===$resource)
          {
            $request->nested->resource=null;
          }
          $resource->delete();
        }
      }
			redirect($request->nested);
		}
		else
		{
			displayAdminLogin($request);
		}
	}
	else
	{
		displayNotFound($request);
	}
}

?>