<?

/*
 * Swim
 *
 * Uploads new contacts from a csv file.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/uploaditem.php $
 * $LastChangedBy: dave $
 * $Date: 2007-03-26 15:49:34 +0100 (Mon, 26 Mar 2007) $
 * $Revision: 1385 $
 */

function method_uploadcontacts($request)
{
  global $_PREFS;
  
  $log = Loggermanager::getLogger('swim.uploadcontacts');
  $user = Session::getUser();
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($user->isLoggedIn()) && ($user->hasPermission('contacts',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('parentitem') && isset($_FILES['file'])
        && $_FILES['file']['error']==UPLOAD_ERR_OK && is_uploaded_file($_FILES['file']['tmp_name']))
    {
      $parent = Item::getItem($request->getQueryVar('parentitem'));
      $class = FieldSetManager::getClass('_contact');
      if (($parent !== null) && ($class !== null))
      {
        if ($request->hasQueryVar('parentsequence'))
          $sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
        else
          $sequence = $parent->getMainSequence();
        if ($sequence !== null)
        {
          $section = $parent->getSection();
          $handle = fopen($_FILES['file']['tmp_name'], "r");
          $headers = fgetcsv($handle);
          if ($headers === FALSE)
          {
            fclose($handle);
            $log->error('Unable to read header line.');
            displayServerError($request);
            return;
          }
          $emailfield = -1;
          $headers = str_replace(array(' ', '-'), '', $headers);
          for ($i = 0; $i < count($headers); $i++)
          {
            $name = strtolower($headers[$i]);
            if ($class->hasField($name))
            {
              $headers[$i] = $name;
              if ($name == 'emailaddress')
                $emailfield = $i;
            }
            else
              $headers[$i] = null;
          }
          if ($emailfield < 0)
          {
            fclose($handle);
            $log->error('No email field in csv file.');
            displayServerError($request);
            return;
          }
          $line = 1;
          while (($record = fgetcsv($handle)) !== FALSE)
          {
            $line++;
            if (count($record) > count($headers))
            {
              fclose($handle);
              $log->error('Line '.$line.' did not contain the correct number of fields ('.count($record).' '.count($headers).').');
              displayServerError($request);
              return;
            }
            if (($emailfield < count($record)) && (preg_match('/\S+\@\S+\.\S+/', $record[$emailfield]) > 0))
            {
              $items = Item::findItems($section, $class, null, 'emailaddress', $record[$emailfield]);
              if (count($items) > 0)
              {
                $version = $items[0];
                $variant = $version->getVariant();
                $item = $version->getItem();
                $version = $variant->createNewVersion($version);
              }
              else
              {
                $item = Item::createItem($section, $class);
                $variant = $item->createVariant('default');
                $version = $variant->createNewVersion();
                $version->setFieldValue('optedin', true);
                $sequence->appendItem($item);
              }
              for ($i = 0; $i<count($record); $i++)
              {
                if ($headers[$i] === null)
                  continue;
                  
                $field = $version->setFieldValue($headers[$i], $record[$i]);
              }
              $version->setComplete(true);
              $version->setCurrent(true);
            }
          }
          fclose($handle);
          redirect($request->getNested());
        }
        else
        {
          $log->warn('Sequence does not exist.');
          displayNotFound($request);
        }
      }
      else
      {
        $log->warn('Item does not exist.');
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