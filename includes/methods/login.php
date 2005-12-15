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

function method_login($request)
{
	global $_PREFS;
	
	$user=UserManager::login($request->query['swim_username'],$request->query['swim_password']);
	if ($user!==false)
	{
    if (isset($request->nested))
    {
  		redirect($request->nested);
    }
    else if (isset($request->query['goto']))
    {
      redirect($request->query['goto']);
    }
    else
    {
      displayServerError($request);
    }
	}
	else
	{
		displayLogin($request->nested,$request->query['message']);
	}
}

?>