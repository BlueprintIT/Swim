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
	global $_PREFS;
	
	$version=false;
	if (isValidPage('global',$request->resource,$version))
	{
		$page = &loadPage('global',$request->resource,$version);
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


?>