<?

/*
 * Swim
 *
 * Saves the user details
 *
 * Copyright Blueprint IT Ltd. 2007
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
  
  setNoCache();
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('users',PERMISSION_WRITE)))
  {
    if (!$request->hasQueryVar('cancel'))
    {
      if (($request->hasQueryVar('username'))&&(strlen($request->getQueryVar('username'))>0))
      {
        $id = $request->getQueryVar('username');
        $user = UserManager::getUser($id);
        if (!$user->userExists())
        {
          $user = UserManager::createUser($id);
        }
        if ($request->hasQueryVar('name'))
        {
          $user->setName($request->getQueryVar('name'));
        }
        if (($request->hasQueryVar('password'))&&(strlen($request->getQueryVar('password'))>0))
        {
          $user->setPassword($request->getQueryVar('password'));
        }
        if ($request->hasQueryVar('group'))
        {
          $user->clearGroups();
          $group = UserManager::getGroup($request->getQueryVar('group'));
          if ($group !== null)
            $user->addGroup($group);
        }
        $req = new Request();
        $req->setMethod('admin');
        $req->setPath('users/details.tpl');
        $req->setQueryVar('user', $id);
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