<?

/*
 * Swim
 *
 * Displays the user details
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_users($request)
{
  global $_USER;
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('users',PERMISSION_READ)))
  {
    $page = Resource::decodeResource('internal/page/users');
    $page->display($request);
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>