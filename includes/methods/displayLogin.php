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


function method_displayLogin($request)
{
	global $_PREFS;
	
  checkSecurity($request, true, true);
  
	$version=false;
	$container = getContainer('internal');
	$page = $container->getPage($_PREFS->getPref('method.login.page'),$version);
	if ($page!==false)
	{
		header($_SERVER["SERVER_PROTOCOL"]." 401 Not Authorized");
		$page->display($request);
	}
	else
	{
		displayNotFound($request);
	}
}


?>