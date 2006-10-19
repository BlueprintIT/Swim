<?

/*
 * Swim
 *
 * Uploads a file into the file area
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_uploadfile($request)
{
  global $_USER,$_PREFS,$_STORAGE;
  
  $log = Loggermanager::getLogger('swim.uploadfile');
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if (isset($_FILES['file']))
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
      $file=$_FILES['file'];
      if (($file['error']==UPLOAD_ERR_OK)&&(is_uploaded_file($file['tmp_name'])))
      {
      	recursiveMkDir($path);
      	if (is_dir($path))
      	{
	        if (!is_readable($path.'/'.$file['name']))
	        {
	          if (move_uploaded_file($file['tmp_name'],$path.'/'.$file['name']))
	          {
		          if ($request->hasQueryVar('description'))
		            $description = $request->getQueryVar('description');
		          else
		            $description = '';
		          $_STORAGE->queryExec('INSERT INTO File (itemversion,file,description) VALUES ('.$iv.',"'.$_STORAGE->escape($file['name']).'","'.$_STORAGE->escape($description).'");');
		          $message = 'Upload was successful.';
	          }
	          else
	          	$message = 'Error uploading, failed to save file.';
	        }
	        else
	        {
	          $message = 'Upload failed because a file of that name already exists - '.$path.'/'.$file['name'].'.';
	        }
      	}
      	else
      		$message = 'Upload failed, please notify Blueprint IT of this issue as soon as possible.';
      }
      else
      {
        if ($file['error']==UPLOAD_ERR_INI_SIZE)
        {
          $message = 'File was too large to be uploaded.';
        }
        else
        {
          $message = 'File upload failed due to a server misconfiguration (error '.$file['error'].').';
        }
      }
      $req = $request->getNested();
      if (isset($message))
        $req->setQueryVar('message', $message);
      else
        $req->clearQueryVar('message');
      redirect($req);
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