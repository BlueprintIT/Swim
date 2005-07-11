<?

/*
 * Swim
 *
 * Resource revert method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_revert(&$request)
{
	global $_USER;
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
			}
			else if ($resource->isPage())
			{
				$resource->makeCurrentVersion();
				redirect($request->nested);
			}
			else
			{
				displayGeneralError($request,'You can only revert blocks or pages.');
			}
		}
		else
		{
			displayLogin($request);
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>