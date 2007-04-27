<?

/*
 * Swim
 *
 * Creates a new mail from a given mailing.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/createitem.php $
 * $LastChangedBy: dave $
 * $Date: 2007-03-26 15:49:34 +0100 (Mon, 26 Mar 2007) $
 * $Revision: 1385 $
 */


function method_createmail($request)
{
  $log = Loggermanager::getLogger('swim.createmail');
  $user = Session::getUser();
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('contacts',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('section') && $request->hasQueryVar('mailing'))
    {
      $section = FieldSetManager::getSection($request->getQueryVar('section'));
      if ($section !== null)
      {
        $mailing = $section->getMailing($query->getQueryVar('mailing'));
        if ($mailing !== null)
        {
        }
        else
          displayNotFound($request);
      }
      else
        displayNotFound($request);
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