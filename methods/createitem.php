<?

/*
 * Swim
 *
 * Creates a new item of a given class, optionally inserting into a sequence.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_createitem($request)
{
  global $_USER;
  
  $log = Loggermanager::getLogger('swim.createitem');
  
  checkSecurity($request, true, true);
  
  setNoCache();
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('targetvariant') && $request->hasQueryVar('targetsection')
       && $request->hasQueryVar('class'))
    {
      $class = FieldSetManager::getClass($request->getQueryVar('class'));
      $section = SectionManager::getSection($request->getQueryVar('targetsection'));
      if (($section !== null) && ($class !== null))
      {
        $item = Item::createItem($section, $class);
        if ($item !== null)
        {
          $variant = $item->createVariant($request->getQueryVar('targetvariant'));
          if ($variant !== null)
          {
            $version = $variant->createNewVersion();
            if ($version !== null)
            {
              if ($request->hasQueryVar('parentitem') && $request->hasQueryVar('parentsequence'))
              {
                $parent = Item::getItem($request->getQueryVar('parentitem'));
                $sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
                if ($sequence !== null)
                  $sequence->appendItem($item);
              }
              $req = new Request();
              $req->setMethod('admin');
              $req->setPath('items/edit.tpl');
              $req->setQueryVar('item', $item->getId());
              $req->setQueryVar('version', $version->getVersion());
              redirect($req);
            }
            else
            {
              $log->warn('Unable to create version');
              displayServerError($request);
            }
          }
          else
          {
            $log->warn('Unable to create variant');
            displayServerError($request);
          }
        }
        else
        {
          $log->warn('Unable to create item');
          displayServerError($request);
        }
      }
      else
      {
        $log->warn('Section does not exist.');
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