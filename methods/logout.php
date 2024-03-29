<?

/*
 * Swim
 *
 * Attempts to log out
 *
 * Copyright Blueprint IT Ltd. 2007
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
  
  RequestCache::setNoCache();
  
  UserManager::logout();
  redirect($request->getNested());
}

?>