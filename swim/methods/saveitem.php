<?

/*
 * Swim
 *
 * Saves the item details
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_saveitem($request)
{
  global $_USER;
  
  $log = LoggerManager::getLogger('swim.saveitem');
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('itemversion'))
    {
      $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      if ($itemversion != null)
      {
        $req = new Request();
        $req->setMethod('admin');
        $req->setPath('items/details.tpl');
        $req->setQueryVar('item', $itemversion->getItem()->getId());
        $req->setQueryVar('version', $itemversion->getVersion());
        $query = $request->getQuery();
        unset($query['itemversion']);
        foreach ($query as $name => $value)
        {
          if ($name == 'complete')
          {
            $itemversion->setComplete($value=='true');
          }
          else if ($name == 'current')
          {
            $itemversion->makeCurrent();
            $req->setQueryVar('reloadtree', 'true');
          }
          else if ($name == 'view')
          {
            $view = ViewManager::getView($value);
            if ($view !== null)
              $itemversion->setView($view);
          }
          else
          {
            $field = $itemversion->getField($name);
            $field->setValue($value);
          }
        }
        redirect($req);
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