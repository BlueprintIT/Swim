<?

/*
 * Swim
 *
 * Selects a block for a page
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_setblock($request)
{
	global $_USER,$_PREFS;
	
	$resource = Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isPage())
			{
				$block=false;
				if ((isset($request->query['block']))&&(strlen($request->query['block'])>0))
				{
					$block=Resource::decodeResource($request->query['block']);
					if (($block===false)||(!$block->isBlock()))
					{
						displayGeneralError($request,"Invalid arguments");
						return;
					}
				}
				$newpage=$resource->makeNewVersion();
				$newpage->setReferencedBlock($request->query['reference'],$block);
				$newpage->savePreferences();
				if ($resource->isCurrentVersion())
					$newpage->makeCurrentVersion();
				redirect($request->nested);
			}
			else
			{
				displayGeneralError($request,"Invalid arguments");
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
