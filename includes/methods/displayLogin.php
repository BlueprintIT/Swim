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
	if ($container->isPage($_PREFS->getPref('method.login.page'),$version))
	{
		$page = &$container->getPage($_PREFS->getPref('method.login.page'),$version);
		$page->display($request);
	}
	else
	{
		displayError($request);
	}
}


?>