<?

/*
 * Swim
 *
 * Archives/restores the item
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_archive($request)
{
  $log = LoggerManager::getLogger('swim.archive');
  checkSecurity($request, true, true);
  $user = Session::getUser();
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('item') && $request->hasQueryVar('archive'))
    {
      $item = Item::getItem($request->getQueryVar('item'));
      if ($item !== null)
      {
        if ($request->getQueryVar('archive')=='true')
          $item->setArchived(true);
        else
          $item->setArchived(false);
        
        $req = $request->getNested();
        redirect($req);
      }
      else
      {
        $log->warn('Source item does not exist.');
        displayNotFound($request);
      }
    }
    else
    {
      $log->error('Invalid paramaters specified.');
      displayServerError($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>