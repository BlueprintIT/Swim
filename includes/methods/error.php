<?

/*
 * Swim
 *
 * Displays the error page
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_error(&$request)
{
	global $_PREFS;
	
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	print 'Error page';
	/*
	$version=false;
	if (isValidPage('global',$_PREFS->getPref('method.error.page'),$version))
	{
		$page = &loadPage('global',$_PREFS->getPref('method.error.page'),$version);
		$page->display($request);
	}
	else
	{
		// TODO figure out what to do here - Bug 7
	}*/
}


?>