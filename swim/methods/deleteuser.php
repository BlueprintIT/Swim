<?

/*
 * Swim
 *
 * Deletes the user
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_deleteuser($request)
{
  global $_USER;
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('users',PERMISSION_DELETE)))
  {
    $user = UserManager::getUser($request->getQueryVar('user'));
    if (UserManager::deleteUser($user))
    {
      $request = new Request();
      $request->setMethod('admin');
      $request->setPath('users/');
      redirect($request);
    }
    else
    {
      redirect($request->nested);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>