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
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if (($resource->isBlock())||($resource->isPage()))
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
          if ($resource->isBlock())
          {
  					$page=&$working->getBlockEditor($request);
  					$page->display($request);
          }
          else
          {
            $container=&getContainer('internal');
            $page=&$container->getPage('pageedit');
            $page->display($request);
          }
				}
				else
				{
					displayLocked($request,$details,$resource);
				}
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