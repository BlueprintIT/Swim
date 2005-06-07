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
	
	$pagedir=$_PREFS->getPref('storage.pages').'/'.$_PREFS->getPref('method.error.page');
	if (is_dir($pagedir))
	{
		$version=getCurrentVersion($pagedir);
		$page = new Page($_PREFS->getPref('method.error.page'),$version);
		$page->display($request);
	}
	else
	{
		// FOO
	}
}


?>