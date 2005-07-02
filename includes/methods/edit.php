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
				$temp=$resource->getWorkingDir();
				if ($temp===false)
				{
					displayLocked($request,$resource->getResource());
					return;
				}

				if (!is_readable($temp.'/block.conf'))
				{
					$working=$resource->makeWorkingVersion();
				}
				
				$page=&$resource->getBlockEditor();
				$page->display($request);
			}
			else if ($resource->isPage())
			{
				$container=&getContainer('internal');
				$page=&$container->getPage('pageedit');
				$page->display($request);
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