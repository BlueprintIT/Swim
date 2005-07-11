<?

/*
 * Swim
 *
 * Displays the admin site
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_admin(&$request)
{
	global $_USER;
	
	$resource=&Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isPage())
			{
				if ($resource->prefs->getPref("page.editable")===false)
				{
					$resource->display($request);
				}
				else
				{
					$resource->displayAdmin($request);
				}
			}
			else
			{
				displayGeneralError($request,'You can only administrate pages.');
			}
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