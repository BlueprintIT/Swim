<?

/*
 * Swim
 *
 * Deletes a file from the file area
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_deletefile($request)
{
  global $_PREFS,$_STORAGE;
  
  $log = Loggermanager::getLogger('swim.deletefile');
  $user = Session::getUser();
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn()) && ($user->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('itemversion'))
    {
      $iv = $request->getQueryVar('itemversion');
      $itemversion = Item::getItemVersion($iv);
      $path = $itemversion->getStoragePath();
    }
    else
    {
      $iv = -1;
      $path = $_PREFS->getPref('storage.site.attachments');
    }
    $filename = $request->getPath();
    if (is_file($path.'/'.$filename))
    {
    	unlink($path.'/'.$filename);
		  $_STORAGE->queryExec('DELETE FROM File WHERE itemversion='.$iv.' AND file="'.$_STORAGE->escape($filename).'";');
      $req = $request->getNested();
      $req->clearQueryVar('message');
      redirect($req);
    }
    else
    {
    	displayNotFound($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>