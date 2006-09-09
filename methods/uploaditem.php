<?

/*
 * Swim
 *
 * Uploads a new item(s), optionally inserting into a sequence.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function create_item($file, $filename, $section, $variant, $sequence, $class)
{
  $item = Item::createItem($section, $class);
  if ($item != null)
  {
    $variant = $item->createVariant($variant);
    if ($variant != null)
    {
      $version = $variant->createNewVersion();
      if ($version != null)
      {
        $pos = strrpos($filename, '.');
        if ($pos === FALSE)
          $name = $filename;
        else
          $name = substr($filename, 0, $pos);
        $field = $version->getField('name');
        if ($field != null)
          $field->setValue($name);
        $field = $version->getField('file');
        if ($field != null)
          $field->setValue($version->getStorageUrl().'/'.$filename);
        $path = $version->getStoragePath();
        mkdir($path, 0777, true);
        move_uploaded_file($file, $path.'/'.$filename);
        $sequence->appendItem($item);
        return $version;
      }
    }
  }
}

function method_uploaditem($request)
{
  global $_USER;
  
  $log = Loggermanager::getLogger('swim.uploaditem');
  
  checkSecurity($request, true, true);
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('targetvariant') && $request->hasQueryVar('targetsection')
        && $request->hasQueryVar('parentitem') && $request->hasQueryVar('parentsequence')
        && isset($_FILES['file']) && $_FILES['file']['error']==UPLOAD_ERR_OK && is_uploaded_file($_FILES['file']['tmp_name']))
    {
      $parent = Item::getItem($request->getQueryVar('parentitem'));
      $sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
      $section = SectionManager::getSection($request->getQueryVar('targetsection'));
      if ($sequence != null && $section != null)
      {
        $type = determineContentType($_FILES['file']['tmp_name'], $_FILES['file']['name']);
        $class = $sequence->getClassForMimetype($type);
        if ($class == null)
        {
          if ($type == 'application/zip')
          {
            // Do some zip magic
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
      $log->error('Invalid paramaters specified - '.join(array_keys($_FILES['file']),' ').'.');
      displayServerError($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}

?>