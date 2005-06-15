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
	global $_PREFS,$_USER;
	
	if ($_USER->login($request->query['swim_username'],$request->query['swim_password']))
	{
		if ($_USER->canAccess($request->nested))
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