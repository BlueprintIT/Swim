<?

/*
 * Swim
 *
 * Mutates a sequence
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_mutatesequence($request)
{
  global $_USER;
  
  $log = Loggermanager::getLogger('swim.mutatesequenc');
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('item') && $request->hasQueryVar('field') 
      && $request->hasQueryVar('action'))
    {
      $field = null;
      $item = Item::getItem($request->getQueryVar('item'));
      if ($item != null)
        $item = $item->getCurrentVersion('default');
      if ($item != null)
        $field = $item->getField($request->getQueryVar('field'));
      if ($field != null)
      {
        if (($request->getQueryVar('action')=='moveup') && ($request->hasQueryVar('index')))
        {
          $item = $field->getItem($request->getQueryVar('index'));
          if ($item != null)
          {
            $field->removeItem($request->getQueryVar('index'));
            $field->insertItem($request->getQueryVar('index')-1, $item);
          }
          else
          {
            $log->warn('Invalid index.');
            displayServerError($request);
          }
        }
        else if (($request->getQueryVar('action')=='movedown') && ($request->hasQueryVar('index')))
        {
          $item = $field->getItem($request->getQueryVar('index'));
          if ($item != null)
          {
            $field->removeItem($request->getQueryVar('index'));
            $field->insertItem($request->getQueryVar('index')+1, $item);
          }
          else
          {
            $log->warn('Invalid index.');
            displayServerError($request);
          }
        }
        else
        {
          $log->warn('Unknown action specified');
          displayServerError($request);
        }
      }
      else
      {
        $log->warn('Could not find sequence.');
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