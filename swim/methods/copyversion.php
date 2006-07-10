<?

/*
 * Swim
 *
 * Creates a new version of a given itemversion, optionally in a different item/variant.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_copyversion($request)
{
  global $_USER;
  
  $log = Loggermanager::getLogger('swim.copyversion');
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('itemversion') && $request->hasQueryVar('targetvariant') 
      && ($request->hasQueryVar('targetitem') || $request->hasQueryVar('targetsection')))
    {
      $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      if ($itemversion != null)
      {
        $item = null;
        if ($request->hasQueryVar('targetitem'))
          $item = Item::getItem($request->getQueryVar('targetitem'));
        else
        {
          $section = Section::getSection($request->getQueryVar('targetsection'));
          if ($section != null)
            $item = Item::createItem($section);
        }
        if ($item != null)
        {
          $variant = $item->createVariant($request->getQueryVar('targetvariant'));
          if ($variant != null)
          {
            $newversion = $variant->createNewVersion(null, $itemversion);
            if ($newversion != null)
            {
              $req = new Request();
              $req->setMethod('admin');
              $req->setPath('items/edit.tpl');
              $req->setQueryVar('item', $item->getId());
              $req->setQueryVar('version', $newversion->getVersion());
              $req->setQueryVar('reloadtree', 'true');
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