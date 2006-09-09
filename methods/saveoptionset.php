<?

/*
 * Swim
 *
 * Saves the optionset details
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_saveoptionset($request)
{
  global $_USER, $_STORAGE;
  
  checkSecurity($request, true, true);
  
  if ($_USER->isLoggedIn())
  {
    if ($request->hasQueryVar('optionset'))
    {
      $id = $request->getQueryVar('optionset');
      $optionset = FieldSetManager::getOptionSet($id);
      if ($optionset !== null)
      {
        $seen = "";
        if ($request->hasQueryVar('option'))
        {
          $options = $request->getQueryVar('option');
          foreach ($options as $details)
          {
            if (isset($details['id']))
            {
              $seen.=$details['id'].',';
              $option = $optionset->getOption($details['id']);
              if (isset($details['name']))
                $option->setName($details['name']);
              $option->setValue($details['value']);
            }
            else
            {
              $option = $optionset->createOption($details['name'], $details['value']);
              $seen.=$option->getId().',';
            }
          }
        }
        if (strlen($seen)>0)
          $seen = ' AND id NOT IN ('.substr($seen,0,-1).')';
        $_STORAGE->queryExec('DELETE FROM OptionSet WHERE optionset="'.$_STORAGE->escape($optionset->getId()).'"'.$seen.';');
        $req = new Request();
        $req->setMethod('admin');
        $req->setPath('options/optionsetdetails.tpl');
        $req->setQueryVar('optionset', $id);
        redirect($req);
      }
      else
      {
        displayNotFoundError();
      }
    }
    else
    {
      displayServerError($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>