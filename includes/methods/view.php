<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_view(&$request)
{
	global $_PREFS;
	
	$version=false;
	if (isValidPage('global',$request->resource,$version))
	{
		$page = &loadPage('global',$request->resource,$version);
		$page->display($request);
	}
	else
	{
		displayError($request);
	}
}


?>