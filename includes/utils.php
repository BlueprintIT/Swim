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

function httpdate($date)
{
	return gmdate("D, j M Y G:i:s",$date).' GMT';
}

function displayLogin(&$request)
{
	$newrequest = new Request();
	$newrequest->method='displayLogin';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

function displayError(&$request)
{
	$newrequest = new Request();
	$newrequest->method='error';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

function setModifiedDate($date)
{
	//header('Cache-Control: public');
	//header('Pragma: ');
	header('Last-Modified: '.httpdate($date));
	//header('Expires: '.httpdate(time()+3600));
}

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