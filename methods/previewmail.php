<?

/*
 * Swim
 *
 * Sends the email to an address for previewing
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/saveitem.php $
 * $LastChangedBy: dave $
 * $Date: 2007-04-27 12:29:30 +0100 (Fri, 27 Apr 2007) $
 * $Revision: 1460 $
 */


function method_previewmail($request)
{
  $log = LoggerManager::getLogger('swim.previewmail');
  checkSecurity($request, true, true);
  $user = Session::getUser();
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('contacts',PERMISSION_WRITE)))
  {
    if (($request->hasQueryVar('itemversion')) && ($request->hasQueryVar('email')))
    {
      $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      if ($itemversion !== null)
      {
        if ($request->hasQueryVar('redirect'))
          $req = $request->getQueryVar('redirect');
        else
          $req = $request->getNested();
        $mailing = $itemversion->getClass()->getMailing();
        $mailing->sendMailTo($itemversion, $request->getQueryVar('email'));
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