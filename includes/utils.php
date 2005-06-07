<?

/*
 * Swim
 *
 * Utility functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function callMethod(&$request)
{
	global $_PREFS;
	
	$methodfile=$request->method.".php";
	$methodfunc='method_'.$request->method;
	if (is_readable($_PREFS->getPref('storage.methods')))
	{
		require_once($_PREFS->getPref('storage.methods').'/'.$methodfile);
		$methodfunc($request);
	}
}

?>