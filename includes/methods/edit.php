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

function method_edit(&$request)
{
	global $_USER;
	
	$log=&LoggerManager::getLogger('swim.method.edit');
	$log->info('New edit method');
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$details=&$resource->getWorkingDetails();
				
				if ((!$details->isMine())&&(isset($request->query['forcelock'])))
				{
					if ($request->query['forcelock']=='continue')
					{
						$details->takeOver();
					}
					else if ($request->query['forcelock']=='discard')
					{
						$details->takeOverClean();
					}
				}
				
				if ($details->isMine())
				{
					$working=&$resource->makeWorkingVersion();
					$page=&$working->getBlockEditor();
					$page->display($request);
				}
				else
				{
					displayLocked($request,$details,$resource);
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
			displayAdminLogin($request);
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>