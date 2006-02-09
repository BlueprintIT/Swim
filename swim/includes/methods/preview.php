<?

/*
 * Swim
 *
 * Page previewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_preview($request)
{
	global $_USER,$_PREFS;
	
	$resource=Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canRead($resource))
		{
			$page = Resource::decodeResource($_PREFS->getPref('method.preview.page'));
			$page->display($request);
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