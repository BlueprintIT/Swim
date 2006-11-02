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
        $req->setQueryVar('reloadtree', 'true');
        $query = $request->getQuery();
        unset($query['itemversion']);
        if (isset($query['view']))
        {
          $view = FieldSetManager::getView($query['view']);
          if ($view !== null)
            $itemversion->setView($view);
          unset($query['view']);
        }
        foreach ($query as $name => $value)
        {
          if (($name != 'complete') && ($name != 'current') && ($name != 'compounds'))
          {
            $field = $itemversion->getField($name);
            if ($field !== null)
              $field->setValue($value);
          }
        }
        if ((isset($query['compounds'])) && (is_array($query['compounds'])))
        {
        	foreach ($query['compounds'] as $compound => $count)
        	{
        		if (!isset($query[$compound]))
        		{
	            $field = $itemversion->getField($compound);
	            if ($field !== null)
	              $field->setValue(array());
        		}
        	}
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