<?

/*
 * Swim
 *
 * Logs out of the system
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_logout($request)
{
  global $_PREFS;
  
  UserManager::logout();
  if (isset($request->nested))
  {
    redirect($request->nested);
  }
  else
  {
    $request = new Request();
    $request->method = 'admin';
    redirect($request);
  }
}

?>