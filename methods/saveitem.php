<?

/*
 * Swim
 *
 * Saves the item details
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_saveitem($request)
{
  $log = LoggerManager::getLogger('swim.saveitem');
  checkSecurity($request, true, true);
  $user = Session::getUser();
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('documents',PERMISSION_WRITE)))
  {
    if (($request->hasQueryVar('itemversion')) || (($request->hasQueryVar('variant')) &&
         (($request->hasQueryVar('item')) ||
        (($request->hasQueryVar('section')) && ($request->hasQueryVar('class'))))))
    {
      if ($request->hasQueryVar('itemversion'))
        $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      else if ($request->hasQueryVar('item'))
      {
        $item = Item::getItem($request->getQueryVar('item'));
        if ($item !== null)
        {
          $variant = $item->getVariant($request->getQueryVar('variant'));
          $itemversion = $variant->getDraftVersion();
          if ($itemversion === null)
          {
            $itemversion = $variant->getCurrentVersion();
            if ($itemversion === null)
              $itemversion = $variant->createNewVersion();
            else
              $itemversion = $variant->createNewVersion($itemversion);
          }
        }
      }
      else
      {
        $class = FieldSetManager::getClass($request->getQueryVar('class'));
        $section = FieldSetManager::getSection($request->getQueryVar('section'));
        if (($class !== null) && ($section !== null))
        {
          $item = Item::createItem($section, $class);
          $variant = $item->createVariant($request->getQueryVar('variant'));
          $itemversion = $variant->createNewVersion();

          if ($request->hasQueryVar('parentitem') && $request->hasQueryVar('parentsequence'))
          {
            $parent = Item::getItem($request->getQueryVar('parentitem'));
            $sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
            if ($sequence !== null)
              $sequence->appendItem($item);
            $request->clearQueryVar('parentitem');
            $request->clearQueryVar('parentsequence');
          }
        }
      }

      $request->clearQueryVar('itemversion');
      $request->clearQueryVar('item');
      $request->clearQueryVar('variant');
      $request->clearQueryVar('section');
      $request->clearQueryVar('class');

      if ($itemversion !== null)
      {
        $query = $request->getQuery();
        if ($request->hasQueryVar('redirect'))
        {
          $req = $request->getQueryVar('redirect');
          unset($query['redirect']);
        }
        else
        {
          $req = $request->getNested();
        }
        if (isset($query['view']))
        {
          $view = FieldSetManager::getView($query['view']);
          if ($view !== null)
            $itemversion->setView($view);
          unset($query['view']);
        }
        if ((isset($query['compounds'])) && (is_array($query['compounds'])))
        {
          foreach ($query['compounds'] as $name => $count)
          {
            if (!isset($query[$name]))
              $field = $itemversion->setFieldValue($name, array());
          }
          unset($query['compounds']);
        }
        if ((isset($query['defaults'])) && (is_array($query['defaults'])))
        {
          foreach ($query['defaults'] as $name => $value)
          {
            if (!is_array($value) && !isset($query[$name]))
              $field = $itemversion->setFieldValue($name, $value);
          }
          unset($query['defaults']);
        }
        foreach ($query as $name => $value)
        {
          if (($name != 'complete') && ($name != 'current'))
            $field = $itemversion->setFieldValue($name, $value);
        }
        if (isset($query['complete']))
          $itemversion->setComplete($query['complete']=='true');
        if (isset($query['current']))
        {
        	if ($query['current'] == 'true')
        	{
        		if (!$itemversion->isComplete())
        			$itemversion->setComplete(true);
	          $itemversion->setCurrent(true);
        	}
        	else
	          $itemversion->setCurrent(false);
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