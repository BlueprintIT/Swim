<?

/*
 * Swim
 *
 * Page changing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_change(&$request)
{
	global $_USER;
	
	$resource=&Resource::decodeResource($request);
	$nested=&Resource::decodeResource($request->nested);
	$log=&LoggerManager::getLogger("swim.method.change");

	if ($resource->isPage())
	{
		if ($_USER->canRead($resource))
		{
			if ($_USER->canWrite($nested))
			{
				$resource->display($request);
			}
			else
			{
				displayAdminLogin($request);
			}
		}
		else
		{
			displayLogin($request,'You must log in to view this resource.');
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>