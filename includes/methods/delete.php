<?

/*
 * Swim
 *
 * Delete method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_delete(&$request)
{
	global $_USER;
	
	$resource=&Resource::decodeResource($request);
	$log=&LoggerManager::getLogger("swim.method.view");

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
      if ($resource->isFile())
			{
				if ($resource->version=="temp")
				{
					$details=$resource->getWorkingDetails();
					if ($details->isMine())
					{
						$log->warn('Deleting file');
						$resource->delete();
						if ($_SERVER['REQUEST_METHOD']=='DELETE')
						{
		        	header($_SERVER["SERVER_PROTOCOL"]." 202 Accepted");
		        	print("Resource accepted");
		        	return;
						}
						else
						{
							redirect($request->nested);
						}
					}
					else
					{
						displayLocked($request,$resource);
					}
				}
				else
				{
					displayGeneralError($request,"You can only delete files from the working version");
				}
			}
			else
			{
				displayGeneralError($request,"You can only delete files");
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
