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
    $user = new User($request->requestPath);
    if (!UserManager::deleteUser($user))
    {
      redirect($request->nested);
    }
    else
    {
      $req = new Request();
      $req->method='users';
      redirect($req);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>