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

function method_setblock(&$request)
{
	global $_USER,$_PREFS;
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			$block=&Resource::decodeResource($request->query['block']);
			if (($resource->isPage())&&($block->isBlock()))
			{
				$newpage=&$resource->makeNewVersion();
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
