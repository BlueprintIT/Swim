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
	
	$pagedir=$_PREFS->getPref('storage.pages').'/'.$request->resource;
	if (is_dir($pagedir))
	{
		$version=getCurrentVersion($pagedir);
		$page = new Page($request->resource,$version);
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