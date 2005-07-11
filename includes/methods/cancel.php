<?

/*
 * Swim
 *
 * Cancels block editing
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_cancel(&$request)
{
	global $_USER;
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$details=&$resource->getWorkingDetails();
				if ($details->isMine())
				{
					$details->free();
				}
				redirect($request->nested);
			}
			else
			{
				displayGeneralError($request,'You cannot cancel anything other than a block edit.');
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