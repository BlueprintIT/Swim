<?

/*
 * Swim
 *
 * Deletes the user
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_deleteuser($request)
{
  global $_USER;
  
  if ($_USER->isLoggedIn())
  {
    $user = new User($request->resource);
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