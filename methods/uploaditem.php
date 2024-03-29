<?

/*
 * Swim
 *
 * Uploads a new item(s), optionally inserting into a sequence.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function create_item($file, $filename, $section, $variant, $sequence, $class)
{
  $log = Loggermanager::getLogger('swim.uploaditem');
  
  $item = Item::createItem($section, $class);
  if ($item !== null)
  {
    $variant = $item->createVariant($variant);
    if ($variant !== null)
    {
      $version = $variant->createNewVersion();
      if ($version !== null)
      {
        $pos = strrpos($filename, '.');
        if ($pos === FALSE)
          $name = $filename;
        else
          $name = substr($filename, 0, $pos);
        $field = $version->getField('name');
        if ($field !== null)
          $field->setValue($name);
        $path = $version->getStoragePath();
        if (is_dir($path) || recursiveMkDir($path))
        {
        	if (is_uploaded_file($file))
        		$result = move_uploaded_file($file, $path.'/'.$filename);
        	else
        		$result = rename($file, $path.'/'.$filename);

	        if ($result)
	        {
		        $field = $version->getField('file');
		        if ($field !== null)
		          $field->setValue($version->getStorageUrl().'/'.$filename);
	        }
	        else
	        	$log->error('Unable to move file '.$file.' '.$path.'/'.$filename);
        }
        else
        	$log->error('Unable to find or create target directory '.$path);
        $sequence->appendItem($item);
        return $version;
      }
    }
  }
}

function create_items($request, $dir, $section, $variant, $sequence)
{
	$res = opendir($dir);
	while (($file = readdir($res)) !== false)
	{
		if (is_file($dir.'/'.$file))
		{
			$type = determineContentType($dir.'/'.$file);
			$class = $sequence->getClassForMimetype($type);
			if ($class !== null)
			{
				$version = create_item($dir.'/'.$file, $file, $section, $variant, $sequence, $class);
				$version->setComplete(true);
				$version->setCurrent(true);
			}
		}
	}
	closedir($res);
}

function method_uploaditem($request)
{
  global $_PREFS;
  
  $log = Loggermanager::getLogger('swim.uploaditem');
  $user = Session::getUser();
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn())&&($user->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('targetvariant') && $request->hasQueryVar('targetsection')
        && $request->hasQueryVar('parentitem') && $request->hasQueryVar('parentsequence')
        && isset($_FILES['file']) && $_FILES['file']['error']==UPLOAD_ERR_OK && is_uploaded_file($_FILES['file']['tmp_name']))
    {
      $parent = Item::getItem($request->getQueryVar('parentitem'));
      $sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
      $section = FieldSetManager::getSection($request->getQueryVar('targetsection'));
      if ($sequence !== null && $section !== null)
      {
        $type = determineContentType($_FILES['file']['tmp_name'], $_FILES['file']['name']);
        $class = $sequence->getClassForMimetype($type);
        if ($class == null)
        {
          $req = new Request();
          $req->setMethod('admin');
          $req->setPath('items/details.tpl');
          $req->setQueryVar('item', $parent->getId());
          if ($type == 'application/zip')
          {
            $cache = $_PREFS->getPref('storage.sitecache').'/uploads';
            if ((is_dir($cache)) || (recursiveMkDir($cache)))
            {
            	$id = 1;
            	while (!recursiveMkDir($cache.'/'.$id))
            		$id++;
            	$command = $_PREFS->getPref('tools.unzip').' '.$_FILES['file']['tmp_name'].' -d '.$cache.'/'.$id;
            	$output = array();
            	$return = 0;
            	exec($command, $output, $return);
            	if ($return == 0)
            		create_items($request, $cache.'/'.$id, $section, $request->getQueryVar('targetvariant'), $sequence);

            	recursiveDelete($cache.'/'.$id);
            	if ($return != 0)
            	{
            		displayGeneralError($request, 'Unable to extract files from archive - error '.$return);
            	}
            	else
			          redirect($req);
            }
            else
            {
            	displayGeneralError($request, 'Unable to create extraction folder.');
            }
          }
          else if (($type == 'text/xml') && ($_FILES['file']['name'] == 'swimimport.xml'))
          {
          	import_items($request, $_FILES['file']['tmp_name'], $section, $request->getQueryVar('targetvariant'), $sequence);
	          redirect($req);
          }
          else
          {
            $log->warn('Unknown mimetype '.$type);
            displayGeneralError($request, 'Unknown mimetype '.$type);
          }
        }
        else
        {
          $version = create_item($_FILES['file']['tmp_name'], $_FILES['file']['name'], $section, $request->getQueryVar('targetvariant'), $sequence, $class);
          if ($version instanceof ItemVersion)
          {
            $req = new Request();
            $req->setMethod('admin');
            $req->setPath('items/edit.tpl');
            $req->setQueryVar('item', $version->getItem()->getId());
            $req->setQueryVar('version', $version->getVersion());
            redirect($req);
          }
          else
          {
            $log->warn($version);
            displayGeneralError($request, $version);
          }
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
      $log->error('Invalid parameters specified.');
      displayServerError($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}

?>