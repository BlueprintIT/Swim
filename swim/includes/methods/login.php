<?

/*
 * Swim
 *
 * Attempts to log in
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_login($request)
{
	global $_PREFS;
	
  checkSecurity($request, true, true);
  
	$user=UserManager::login($request->query['swim_username'],$request->query['swim_password']);
	if ($user!==null)
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