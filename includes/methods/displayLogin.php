<?

/*
 * Swim
 *
 * Displays the login page
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_displayLogin(&$request)
{
	global $_PREFS;
	
	$version=false;
	if (isValidPage('internal',$_PREFS->getPref('method.login.page'),$version))
	{
		$page = &loadPage('internal',$_PREFS->getPref('method.login.page'),$version);
		$page->display($request);
	}
	else
	{
		displayError($request);
	}
}


?>