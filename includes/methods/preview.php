<?

/*
 * Swim
 *
 * Page previewing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_preview(&$request)
{
	global $_USER,$_PREFS;
	
	$resource=&Resource::decodeResource($request);
	$log=&LoggerManager::getLogger("swim.method.view");

	if ($resource!==false)
	{
		if ($_USER->canRead($resource))
		{
 			if ($resource->isPage())
			{
				$resource=$resource->getReferencedBlock('content');
			}
			if ($resource->isBlock())
			{
				$page = &Resource::decodeResource($_PREFS->getPref('method.preview.page'));
				$page->setReferencedBlock('content',$resource);
				$page->display($request);
			}
			else
			{
				displayGeneralError($request,'You can only preview pages or blocks.');
			}
		}
		else
		{
			displayLogin($request,'You must log in to view this page.');
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>