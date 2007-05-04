<?

/*
 * Swim
 *
 * Sends the email
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/saveitem.php $
 * $LastChangedBy: dave $
 * $Date: 2007-04-27 12:29:30 +0100 (Fri, 27 Apr 2007) $
 * $Revision: 1460 $
 */


function method_sendmail($request)
{
  $log = LoggerManager::getLogger('swim.sendmail');
  checkSecurity($request, true, true);
  $user = Session::getUser();
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('contacts',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('item'))
    {
      $itemversion = Item::getItem($request->getQueryVar('item'))->getVariant('default')->getDraftVersion();
      if ($itemversion !== null)
      {
        if ($request->hasQueryVar('redirect'))
          $req = $request->getQueryVar('redirect');
        else
          $req = $request->getNested();
        $mailing = $itemversion->getClass()->getMailing();
        $mailing->prepareSend($itemversion);
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