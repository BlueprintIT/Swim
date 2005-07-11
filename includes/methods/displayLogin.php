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
	$container = &getContainer('internal');
	$page = &$container->getPage($_PREFS->getPref('method.login.page'),$version);
	if ($page!==false)
	{
		$page->display($request);
	}
	else
	{
		displayNotFound($request);
	}
}


?>