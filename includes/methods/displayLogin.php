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
	
	$pagedir=$_PREFS->getPref('storage.pages').'/'.$_PREFS->getPref('method.login.page');
	if (is_dir($pagedir))
	{
		$version=getCurrentVersion($pagedir);
		$page = new Page($_PREFS->getPref('method.login.page'),$version);
		$page->display($request);
	}
	else
	{
		displayError($request);
	}
}


?>