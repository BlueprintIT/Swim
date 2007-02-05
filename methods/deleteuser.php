<?

/*
 * Swim
 *
 * Deletes the user
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_deleteuser($request)
{
  checkSecurity($request, true, true);
  $user = Session::getUser();
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('users',PERMISSION_DELETE)))
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