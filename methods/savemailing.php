<?

/*
 * Swim
 *
 * Saves the mailing details
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/saveoptionset.php $
 * $LastChangedBy: dave $
 * $Date: 2007-02-05 17:07:22 +0000 (Mon, 05 Feb 2007) $
 * $Revision: 1318 $
 */


function method_savemailing($request)
{
  global $_STORAGE;
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (Session::getUser()->isLoggedIn())
  {
    if (($request->hasQueryVar('mailing')) && ($request->hasQueryVar('section')))
    {
      $section = FieldSetManager::getSection($request->getQueryVar('section'));
      if ($section !== null)
      {
        $mailing = $section->getMailing($request->getQueryVar('mailing'));
        if ($mailing !== null)
        {
          if ($request->hasQueryVar('intro'))
          {
            $mailing->setIntro($request->getQueryVar('intro'));
          }
          $req = new Request();
          $req->setMethod('admin');
          $req->setPath('mailing/maildetails.tpl');
          $req->setQueryVar('section', $section->getId());
          $req->setQueryVar('mailing', $mailing->getId());
          redirect($req);
        }
        else
          diaplayNotFound($request);
      }
      else
        displayNotFound($request);
    }
    else
      displayServerError($request);
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>