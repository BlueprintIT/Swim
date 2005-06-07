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
	
	$pagedir=$_PREFS->getPref('storage.pages').'/'.$request->resource;
	if (is_dir($pagedir))
	{
		$version=getCurrentVersion($pagedir);
		$page = new Page($request->resource,$version);
		$page->display($request);
	}
	else
	{
		// Error
	}
}


?>