<?

/*
 * Swim
 *
 * Imports new items
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function import_items($request, $file, $section, $variant, $sequence, $dir = null, $temp = true)
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
										      			displayGeneralError($request, 'Unable to import. Item '.$pos.' field '.$field->getId().' includes non-existant field '.$name.'.');
										      			return;
									      			}
									      			$type = $field->getFieldType($name);
									      			if ($type == 'file')
									      			{
									      				if ($dir === null)
									      				{
									      					displayGeneralError($request, 'Cannot specify file fields unless importing from archive or directory.');
									      					return;
									      				}
									      				
										      			if ($subfieldloop->hasAttribute('actual'))
										      				$filename = $subfieldloop->getAttribute('actual');
										      			else
										      				$filename = getDOMText($subfieldloop);
										      			if (!is_file($dir.'/'.$filename))
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
	      				if ($dir === null)
	      				{
	      					displayGeneralError($request, 'Cannot specify file fields unless importing from archive or directory.');
	      					return;
	      				}
	      				
		      			if ($fieldloop->hasAttribute('actual'))
		      				$filename = $fieldloop->getAttribute('actual');
		      			else
		      				$filename = getDOMText($fieldloop);
		      			if (!is_file($dir.'/'.$filename))
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
									      			$targetdir = $version->getStoragePath();
									      			if ((is_dir($targetdir)) || recursiveMkDir($targetdir))
									      			{
										      			if ($subfieldloop->hasAttribute('actual'))
										      				$filename = $subfieldloop->getAttribute('actual');
										      			else
										      				$filename = getDOMText($subfieldloop);
										      			if ($temp)
											      			rename($dir.'/'.$filename, $targetdir.'/'.getDOMText($subfieldloop));
											      		else if (!link($dir.'/'.$filename, $targetdir.'/'.getDOMText($subfieldloop)))
											      			copy($dir.'/'.$filename, $targetdir.'/'.getDOMText($subfieldloop));
										      			$subfield->setValue($version->getStorageUrl().'/'.getDOMText($subfieldloop));
									      			}
									      			else
									      			{
									      				displayGeneralError($request, 'Unable to create target directory '.$targetdir);
									      				return;
									      			}
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
		      			$targetdir = $version->getStoragePath();
		      			if ((is_dir($targetdir)) || recursiveMkDir($targetdir))
		      			{
			      			if ($fieldloop->hasAttribute('actual'))
			      				$filename = $fieldloop->getAttribute('actual');
			      			else
			      				$filename = getDOMText($fieldloop);
			      			if ($temp)
				      			rename($dir.'/'.$filename, $targetdir.'/'.getDOMText($fieldloop));
				      		else if (!link($dir.'/'.$filename, $targetdir.'/'.getDOMText($fieldloop)))
				      			copy($dir.'/'.$filename, $targetdir.'/'.getDOMText($fieldloop));
				      			
			      			$field->setValue($version->getStorageUrl().'/'.getDOMText($fieldloop));
		      			}		      			
		      			else
		      			{
		      				displayGeneralError($request, 'Unable to create target directory '.$targetdir);
		      				return;
		      			}
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
      	$version->setCurrent(true);
      }
    }
    $itemloop = $itemloop->nextSibling;
	}
}

function method_import($request)
{
  global $_USER;
  
  $log = Loggermanager::getLogger('swim.importitem');
  
  checkSecurity($request, true, true);
  
  setNoCache();
  
  if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
  {
    if ($request->hasQueryVar('targetvariant') && $request->hasQueryVar('parentitem'))
    {
      $parent = Item::getItem($request->getQueryVar('parentitem'));
      $section = $parent->getSection();
      $variant = $request->getQueryVar('targetvariant');
      if ($request->hasQueryVar('parentsequence'))
      	$sequence = $parent->getSequence($request->getQueryVar('parentsequence'));
      else
      	$sequence = $parent->getMainSequence();

    	if (isset($_FILES['file']) && $_FILES['file']['error']==UPLOAD_ERR_OK && is_uploaded_file($_FILES['file']['tmp_name']))
    	{
    		$source = $_FILES['file']['tmp_name'];
    		$sourcename = $_FILES['file']['name'];
    	}
    	else if ($request->hasQueryVar('local') && strlen($request->getQueryVar('local'))>0)
    	{
    		$source = $request->getQueryVar('local');
    		$sourcename = basename($source);
    	}
    	else
    	{
    		displayGeneralError($request, 'No file to import from specified.');
    		return;
    	}
    	
    	if (is_dir($source))
    	{
      	if (is_file($source.'/swimimport.xml'))
      		import_items($request, $source.'/swimimport.xml', $section, $variant, $sequence, $source, false);
      	else
      		displayGeneralError($request, 'No import file included in directory.');
    	}
    	else
    	{
	      $type = determineContentType($source, $sourcename);
	      if ($type == 'application/zip')
	      {
	        $cache = $_PREFS->getPref('storage.sitecache').'/uploads';
	        if ((is_dir($cache)) || (recursiveMkDir($cache)))
	        {
	        	$id = 1;
	        	while (!recursiveMkDir($cache.'/'.$id))
	        		$id++;
	        	$command = $_PREFS->getPref('tools.unzip').' '.$source.' -d '.$cache.'/'.$id;
	        	$output = array();
	        	$return = 0;
	        	exec($command, $output, $return);
	        	if ($return == 0)
	        	{
		        	if (is_file($cache.'/'.$id.'/swimimport.xml'))
		        		import_items($request, $cache.'/'.$id.'/swimimport.xml', $section, $variant, $sequence, $cache.'/'.$id);
		        	else
		        		displayGeneralError($request, 'No import file included in zip file.');
	        	}
	        	else
	        		displayGeneralError($request, 'Unable to extract files from archive - error '.$return);
	
	        	recursiveDelete($cache.'/'.$id);
	        }
	        else
	        	displayGeneralError($request, 'Unable to extract files from archive.');
	      }
	      else if ($type == 'text/xml')
	      {
	      	import_items($request, $source, $section, $variant, $sequence);
	      }
	      else
	        displayGeneralError($request, 'Unknown mimetype '.$type);
    	}
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