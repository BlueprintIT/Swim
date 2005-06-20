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
	
	$resource=&Resource::decodeResource($request->resource);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isPage())
			{
				$page = &$resource->getPage();
				if ($page->prefs->getPref("page.editable")===false)
				{
					$page->display($request);
				}
				else
				{
					$page->displayAdmin($request);
				}
			}
			else
			{
				displayError($request);
			}
		}
		else
		{
			displayLogin($request);
		}
	}
	else
	{
		displayError($request);
	}
}


?>