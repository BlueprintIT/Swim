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
  global $_USER;
  
  $log = LoggerManager::getLogger('swim.archive');
  checkSecurity($request, true, true);
  
  setNoCache();
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
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
        $req->setQueryVar('reloadtree', 'true');
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