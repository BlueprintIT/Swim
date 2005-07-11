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
				$page = &$resource;
				
				$template=$_PREFS->getPref('method.preview.template');
				list($container,$template)=explode('/',$template,2);
				$container=&getContainer($container);
				$template=&$container->getTemplate($template);
				$template->display($request,$page);
			}
			else
			{
				displayGeneralError($request,'You can only preview pages.');
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