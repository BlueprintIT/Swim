<?

/*
 * Swim
 *
 * Saves the user details
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_saveuser($request)
{
  global $_USER;
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('users',PERMISSION_WRITE)))
  {
    if (!isset($request->query['cancel']))
    {
      if ((isset($request->query['username']))&&(strlen($request->query['username'])>0))
      {
        $id = $request->query['username'];
        $user = new User($id);
        if (!$user->userExists())
        {
          $user = UserManager::createUser($id);
        }
        if (isset($request->query['name']))
        {
          $user->setName($request->query['name']);
        }
        if ((isset($request->query['password']))&&(strlen($request->query['password'])>0))
        {
          $user->setPassword($request->query['password']);
        }
        if (isset($request->query['group']))
        {
          $user->clearGroups();
          $user->addGroup($request->query['group']);
        }
        $req = new Request();
        $req->method='users';
        $req->resource='view/'.$id;
        redirect($req);
      }
      else
      {
        displayServerError($request);
      }
    }
    redirect($request->nested);
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>