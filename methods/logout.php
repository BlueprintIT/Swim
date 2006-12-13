<?

/*
 * Swim
 *
 * Attempts to log out
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_logout($request)
{
  global $_PREFS;
  
  checkSecurity($request, true, true);
  
  setNoCache();
  
  UserManager::logout();
  redirect($request->getNested());
}

?>