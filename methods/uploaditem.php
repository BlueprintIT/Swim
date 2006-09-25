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
  $log = Loggermanager::getLogger('swim.uploaditem');
  
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
        $path = $version->getStoragePath();
        if (is_dir($path) || mkdir($path, 0777, true))
        {
	        if (move_uploaded_file($file, $path.'/'.$filename))
	        {
		        $field = $version->getField('file');
		        if ($field != null)
		          $field->setValue($version->getStorageUrl().'/'.$filename);
	        }
	        else
	        	$log->error('Unable to move uploaded file');
        }
        else
        	$log->error('Unable to find or create target directory '.$path);
        $sequence->appendItem($item);
        return $version;
      }
    }
  }
}

function import_items($request, $file, $section, $variant, $sequence, $dir = null)
{
	$doc = new DOMDocument();
	$doc->load($file);
  $itemloop = $doc->documentElement->firstChild;
  $items = array();
  $pos = 0;
  while ($itemloop!==null)
  {
    if ($itemloop->nodeType==XML_ELEMENT_NODE)
    {
      if ($itemloop->tagName=='item')
      {
      	$thisparent = $sequence;
      	$pos++;
      	
      	if ($itemloop->hasAttribute('class'))
      	{
      		$class = FieldSetManager::getClass($itemloop->getAttribute('class'));
      		if ($class === null)
      		{
     				displayGeneralError($request, 'Unable to import. Item '.$pos.' has an invalid class specified.');
     				return;
        	}
        }
      	else
      	{
    			displayGeneralError($request, 'Unable to import. Item '.$pos.' does not have a class specified.');
    			return;
      	}

      	if ($itemloop->hasAttribute('view'))
      	{
      		$view = FieldSetManager::getClass($itemloop->getAttribute('view'));
      		if ($view === null)
      		{
      			displayGeneralError($request, 'Unable to import. Item '.$pos.' has an invalid view specified.');
      			return;
      		}
      	}
      	else
      		$view = $class->getDefaultView();

      	if ($itemloop->hasAttribute('parent'))
      	{
      		if (!isset($items[$itemloop->getAttribute('parent')]))
      		{
      			displayGeneralError($request, 'Unable to import. Item '.$pos.' refers to a nonexistant parent.');
      			return;
      		}
      		if ($itemloop->hasAttribute('parentsequence'))
      		{
    				$thisparent = $items[$itemloop->getAttribute('parent')]->getField(null, $itemloop->getAttribute('parentsequence'));
      		}
      		else
      		{
      			$thisparent = $items[$itemloop->getAttribute('parent')]->getMainSequenceName();
      			if ($thisparent !== null)
      				$thisparent = $items[$itemloop->getAttribute('parent')]->getField(null, $thisparent);
      		}
      		if (($thisparent===null) || ($thisparent->getType()!=='sequence'))
      		{
      			displayGeneralError($request, 'Unable to import. Item '.$pos.' refers to a nonexistant parent sequence.');
      			return;
      		}
      	}
        	
      	/*$classes = $thisparent->getVisibleClasses();
      	if (!isset($classes[$class->getId()]))
      	{
    			displayGeneralError($request, 'Unable to import. Class for item '.$pos.' is not valid for the parent sequence.');
    			return;
      	}*/
      	
      	$fieldloop = $itemloop->firstChild;
      	while ($fieldloop !== null)
      	{
		      if ($fieldloop->nodeType==XML_ELEMENT_NODE)
		      {
		      	if ($fieldloop->tagName == 'field')
		      	{
		      		if ($fieldloop->hasAttribute('name'))
		      		{
		      			$name = $fieldloop->getAttribute('name');
			      		if ($class->hasField($name))
			      		{
			      			$field = $class->getField(null, $name);
			      		}
			      		else if ($class->hasField($name))
			      		{
			      			$field = $view->getField(null, $name);
			      		}
			      		else
			      		{
			      			displayGeneralError($request, 'Unable to import. Item '.$pos.' includes non-existant field '.$name.'.');
			      			return;
			      		}
		      		}
		      		else
		      		{
		      			displayGeneralError($request, 'Unable to import. Item '.$pos.' contains a field with no name.');
		      			return;
		      		}
		      		if ($field->getType() == 'sequence')
		      		{
		      			displayGeneralError($request, 'Unable to import. Item '.$pos.' contains a sequence.');
		      			return;
		      		}
		      		else if ($field->getType() == 'compound')
		      		{
		      			$rowloop = $fieldloop->firstChild;
		      			while ($rowloop !== null)
		      			{
						      if ($rowloop->nodeType==XML_ELEMENT_NODE)
						      {
						      	if ($rowloop->tagName == 'row')
						      	{
						      		$subfieldloop = $rowloop->firstChild;
						      		while ($subfieldloop !== null)
						      		{
									      if ($subfieldloop->nodeType==XML_ELEMENT_NODE)
									      {
									      	if ($subfieldloop->tagName == 'field')
									      	{
									      		if ($subfieldloop->hasAttribute('name'))
									      		{
									      			$name = $subfieldloop->getAttribute('name');
									      			if (!$field->hasField($name))
									      			{
										      			displayGeneralError($request, 'Unable to import. Item '.$pos.' includes non-existant field '.$name.'.');
										      			return;
									      			}
									      			$type = $field->getFieldType($name);
									      			if ($type == 'file')
									      			{
										      			if (!is_file($dir.'/'.getDOMText($subfieldloop)))
										      			{
											      			displayGeneralError($request, 'Unable to import. Item '.$pos.' contains a file field ('.$name.') with no matching file.');
											      			return;
										      			}
									      			}
									      		}
									      		else
									      		{
									      			displayGeneralError($request, 'Unable to import. Item '.$pos.' contains a field with no name.');
									      			return;
									      		}
									      	}
									      }
									      $subfieldloop = $subfieldloop->nextSibling;
						      		}
						      	}
						      }
		      				$rowloop = $rowloop->nextSibling;
		      			}
		      		}
		      		else if ($field->getType() == 'file')
		      		{
		      			if (($dir !== null) && (!is_file($dir.'/'.getDOMText($fieldloop))))
		      			{
			      			displayGeneralError($request, 'Unable to import. Item '.$pos.' contains a file field ('.$name.') with no matching file.');
			      			return;
		      			}
		      		}
		      	}
		      }
      		$fieldloop = $fieldloop->nextSibling;
      	}
      	
      	if ($itemloop->hasAttribute('id'))
      	{
      		$items[$itemloop->getAttribute('id')] = $class;
      	}
      }
    }
    $itemloop = $itemloop->nextSibling;
	}
	
	// If we're here then the initial check has passed. Time to start ravaging the database
  $items = array();
  $pos = 0;
  $itemloop = $doc->documentElement->firstChild;
  while ($itemloop!==null)
  {
    if ($itemloop->nodeType==XML_ELEMENT_NODE)
    {
      if ($itemloop->tagName=='item')
      {
      	$thisparent = $sequence;
      	$pos++;
      	
    		$class = FieldSetManager::getClass($itemloop->getAttribute('class'));

      	if ($itemloop->hasAttribute('view'))
      		$view = FieldSetManager::getClass($itemloop->getAttribute('view'));
      	else
      		$view = $class->getDefaultView();

			  $item = Item::createItem($section, $class);
		    $ivariant = $item->createVariant($variant);
	      $version = $ivariant->createNewVersion();

      	if ($itemloop->hasAttribute('parent'))
      	{
      		$thisparent = $items[$itemloop->getAttribute('parent')];
      		if ($itemloop->hasAttribute('parentsequence'))
    				$thisparent = $thisparent->getField($itemloop->getAttribute('parentsequence'));
      		else
      			$thisparent = $thisparent->getMainSequence();
      	}
      	$thisparent->appendItem($item);
      	
      	$fieldloop = $itemloop->firstChild;
      	while ($fieldloop !== null)
      	{
		      if ($fieldloop->nodeType==XML_ELEMENT_NODE)
		      {
		      	if ($fieldloop->tagName == 'field')
		      	{
		      		$name = $fieldloop->getAttribute('name');
	      			$field = $version->getField($name);
		      		
		      		if ($field->getType() == 'compound')
		      		{
		      			$rowloop = $fieldloop->firstChild;
		      			while ($rowloop !== null)
		      			{
						      if ($rowloop->nodeType==XML_ELEMENT_NODE)
						      {
						      	if ($rowloop->tagName == 'row')
						      	{
						      		$row = $field->appendRow();
						      		$subfieldloop = $rowloop->firstChild;
						      		while ($subfieldloop !== null)
						      		{
									      if ($subfieldloop->nodeType==XML_ELEMENT_NODE)
									      {
									      	if ($subfieldloop->tagName == 'field')
									      	{
									      		$subfield = $row->getField($subfieldloop->getAttribute('name'));
								      			if ($type == 'file')
								      			{
									      			rename($dir.'/'.getDOMText($fieldloop), $version->getStoragePath().'/'.getDOMText($fieldloop));
									      			$subfield->setValue($version->getStorageUrl().'/'.getDOMText($fieldloop));
								      			}
									      		else
									      		{
									      			$subfield->setValue(getDOMText($subfieldloop));
									      		}
									      	}
									      }
									      $subfieldloop = $subfieldloop->nextSibling;
						      		}
						      	}
						      }
		      				$rowloop = $rowloop->nextSibling;
		      			}
		      		}
		      		else if ($field->getType() == 'file')
		      		{
		      			rename($dir.'/'.getDOMText($fieldloop), $version->getStoragePath().'/'.getDOMText($fieldloop));
		      			$field->setValue($version->getStorageUrl().'/'.getDOMText($fieldloop));
		      		}
		      		else
		      		{
		      			$field->setValue(getDOMText($fieldloop));
		      		}
		      	}
		      }
      		$fieldloop = $fieldloop->nextSibling;
      	}
      	
      	if ($itemloop->hasAttribute('id'))
      	{
      		$items[$itemloop->getAttribute('id')] = $version;
      	}
      	$version->setComplete(true);
//      	$version->makeCurrent();
      }
    }
    $itemloop = $itemloop->nextSibling;
	}
}

function create_items($request, $dir, $section, $variant, $sequence)
{
	if (is_file($dir.'/swimimport.xml'))
	{
		import_items($request, $dir.'/swimimport.xml', $section, $variant, $sequence, $dir);
	}
	else
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
					create_item($dir.'/'.$file, $file, $section, $variant, $sequence, $class);
				}
			}
		}
		closedir($res);
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
            $cache = $_PREFS->getPref('storage.sitecache').'/uploads';
            if ((is_dir($cache)) || (mkdir($cache, 0777, true)))
            {
            	$id = 1;
            	while (!mkdir($cache.'/'.$id, 0777, true))
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
            }
            else
            {
            	displayGeneralError($request, 'Unable to extract files from archive.');
            }
          }
          else if (($type == 'text/xml') && ($_FILES['file']['name'] == 'swimimport.xml'))
          {
          	import_items($request, $_FILES['file']['tmp_name'], $section, $request->getQueryVar('targetvariant'), $sequence);
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