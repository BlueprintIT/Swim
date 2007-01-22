<?

/*
 * Swim
 *
 * Attempts to log in
 *
 * Copyright Blueprint IT Ltd. 2007
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
  
  RequestCache::setNoCache();
  
	$user=UserManager::login($request->getQueryVar('username'),$request->getQueryVar('password'));
	if ($user!==null)
	{
    if ($request->getNested() !== null)
    {
  		redirect($request->getNested());
    }
    else if ($request->hasQueryVar('goto'))
    {
      redirect($request->getQueryVar('goto'));
    }
    else
    {
      displayServerError($request);
    }
	}
	else
	{
		displayLogin($request->nested,$request->getQueryVar('message'));
	}
}

?>