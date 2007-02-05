<?

/*
 * Swim
 *
 * Creates a new version of a given itemversion, optionally in a different item/variant.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_copyversion($request)
{
  $log = Loggermanager::getLogger('swim.copyversion');
  $user = Session::getUser();
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('itemversion') && $request->hasQueryVar('targetvariant') 
      && ($request->hasQueryVar('targetitem') || $request->hasQueryVar('targetsection')))
    {
      $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      if ($itemversion !== null)
      {
        $item = null;
        if ($request->hasQueryVar('targetitem'))
          $item = Item::getItem($request->getQueryVar('targetitem'));
        else
        {
          $section = Section::getSection($request->getQueryVar('targetsection'));
          if ($section !== null)
            $item = Item::createItem($section);
        }
        if ($item !== null)
        {
          $variant = $item->createVariant($request->getQueryVar('targetvariant'));
          if ($variant !== null)
          {
            $newversion = $variant->getDraftVersion();
            if ($newversion === null)
              $newversion = $variant->createNewVersion($itemversion);
            if ($newversion !== null)
            {
              $req = new Request();
              $req->setMethod('admin');
              if ($request->hasQueryVar('action'))
                $req->setPath('items/'.$request->getQueryVar('action').'.tpl');
              else
                $req->setPath('items/edit.tpl');
              $req->setQueryVar('item', $item->getId());
              $req->setQueryVar('version', $newversion->getVersion());
              redirect($req);
            }
            else
            {
              $log->error('Failed to create new version.');
              displayServerError($request);
            }
          }
          else
          {
            $log->error('Failed to create variant '.$request->getQueryVar('targetvariant'));
            displayServerError($request);
          }
        }
        else
        {
          $log->warn('Failed to find/create target item.');
          displayNotFound($request);
        }
      }
      else
      {
        $log->warn('Source version does not exist.');
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