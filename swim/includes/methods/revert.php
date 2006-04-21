<?

/*
 * Swim
 *
 * Resource revert method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_revert($request)
{
	global $_USER;
	
  checkSecurity($request, true, true);
  
	$resource = $request->resource;

	if ($resource!==null)
	{
		if ($_USER->canWrite($resource))
		{
			$resource->makeCurrentVersion();
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