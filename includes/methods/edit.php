<?

/*
 * Swim
 *
 * Resource editing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// TODO fix this - Bug 8
function displayLocked(&$request,$resource)
{
}

function method_edit(&$request)
{
	global $_USER;
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$details=$resource->getWorkingDetails();
				if ($details->isMine())
				{
					$working=$resource->makeWorkingVersion();
					$page=&$working->getBlockEditor();
					$page->display($request);
				}
				else
				{
					displayLocked($request,$resource);
				}
			}
			else if ($resource->isPage())
			{
				$container=&getContainer('internal');
				$page=&$container->getPage('pageedit');
				$page->display($request);
			}
			else
			{
				displayGeneralError($request,'You can only edit pages and blocks.');
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