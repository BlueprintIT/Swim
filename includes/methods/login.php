<?

/*
 * Swim
 *
 * Attempts to log in
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_login(&$request)
{
	global $_PREFS;
	
	$user=login($request->query['swim_username'],$request->query['swim_password']);
	if ($user!==false)
	{
		if ($user->canAccess($request->nested))
		{
			redirect($request->nested);
		}
		else
		{
			displayLogin($request->nested);
		}
	}
	else
	{
		displayLogin($request->nested);
	}
}

?>