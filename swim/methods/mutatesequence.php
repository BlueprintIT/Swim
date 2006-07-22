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
    if ($request->hasQueryVar('item') && $request->hasQueryVar('action'))
    {
      if ($request->getQueryVar('action') == 'move')
      {
        $item = Item::getItem($request->getQueryVar('item'));
        if ($item !== null)
        {
          if ($request->hasQueryVar('removeitem') && $request->hasQueryVar('removepos'))
          {
            $parent = Item::getItem($request->getQueryVar('removeitem'));
            if ($parent !== null)
            {
              $sequence = $parent->getMainSequence();
              $test = $sequence->getItem($request->getQueryVar('removepos'));
              if ($test === $item)
                $sequence->removeItem($request->getQueryVar('removepos'));
              else
              {
                displayServerError($request);
                return;
              }
            }
            else
            {
              displayServerError($request);
              return;
            }
          }
          if ($request->hasQueryVar('insertitem') && $request->hasQueryVar('insertpos'))
          {
            $parent = Item::getItem($request->getQueryVar('insertitem'));
            if ($parent !== null)
            {
              $sequence = $parent->getMainSequence();
              $sequence->insertItem($request->getQueryVar('insertpos'), $item);
            }
            else
            {
              displayServerError($request);
              return;
            }
          }
        }
        else
        {
          displayServerError($request);
          return;
        }
      }
      else if ($request->hasQueryVar('field'))
      {
        $field = null;
        $item = Item::getItem($request->getQueryVar('item'));
        if ($item !== null)
          $item = $item->getCurrentVersion(Session::getCurrentVariant());
        if ($item !== null)
          $field = $item->getField($request->getQueryVar('field'));
        if ($field !== null)
        {
          if (($request->getQueryVar('action')=='moveup') && ($request->hasQueryVar('index')))
          {
            $item = $field->getItem($request->getQueryVar('index'));
            if ($item !== null)
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
            if ($item !== null)
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