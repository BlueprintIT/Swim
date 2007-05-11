<?

/*
 * Swim
 *
 * Allows non-admin users to post items (such as comments).
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function formatPlainToHtml($text)
{
	return nl2br(htmlentities($text, ENT_COMPAT, 'UTF-8'));
}

function method_postitem($request)
{
  global $_PREFS;

  $log = Loggermanager::getLogger('swim.postitem');
  
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (!$request->hasQueryVar('item'))
  {
  	displayNotFound($request);
  	return;
  }
    
	$item = Item::getItem($request->getQueryVar('item'));
	if ($item === null)
	{
		displayNotFound($request);
		return;
	}
	
	if ($request->hasQueryVar('parentsequence'))
		$sequence = $item->getSequence($request->getQueryVar('parentsequence'));
	else
		$sequence = $item->getMainSequence();
	if (($sequence === null) || (!$sequence->allowPosts()))
	{
		displayNotFound($request);
		return;
	}
    	
	if ($request->hasQueryVar('class'))
    $class = FieldSetManager::getClass($request->getQueryVar('class'));
  else
  {
  	$classes = $sequence->getVisibleClasses();
  	if (count($classes)>0)
  	{
  		$class = each($classes);
  		$class = $class[1];
  	}
  }
  if ($class === null)
  {
  	displayGeneralError($request, "Invalid class.");
  	return;
  }
  
  $request->clearQueryVar('item');
  $request->clearQueryVar('parentsequence');
  $request->clearQueryVar('class');
  
  $section = $item->getSection();
  $variant = Session::getCurrentVariant();
  
  $newitem = Item::createItem($section, $class);
  if ($newitem !== null)
  {
	  $variant = $newitem->createVariant($variant);
	  if ($variant !== null)
	  {
		  $itemversion = $variant->createNewVersion();
		  if ($itemversion !== null)
		  {
		  	$query = $request->getQuery();
        foreach ($query as $name => $value)
        {
          $field = $itemversion->getField($name);
          if ($field !== null)
          {
            if ($field->getType() == 'html')
            {
            	if (!is_array($value))
            		$value = array('content' => $value, 'format' => 'plain');
            	
            	switch ($value['format'])
            	{
            		case 'plain':
            		default:
            			$final = formatPlainToHtml($value['content']);
            	}
            	$field->setValue($final);
            }
            else
            	$field->setValue($value);
          }
        }
        
        if ($itemversion->hasField('ipaddress'))
          $itemversion->setFieldValue('ipaddress', $_SERVER['REMOTE_ADDR']);
		  	
		  	$itemversion->setComplete(true);
		  	if ($sequence->postPublished())
		  		$itemversion->setCurrent(true);
		  	$sequence->appendItem($newitem);
        
        if ($item->getClass()->hasField('postemail'))
        {
          $iv = $item->getCurrentVersion(Session::getCurrentVariant());
          $field = $iv->getField('postemail');
          $email = $field->toString();
          if (strlen($email) > 0)
          {
            $url = new Request();
            $url->setMethod('admin');
            $url->setPath('items/index.tpl');
            $url->setQueryVar('section', $section->getId());
            $url->setQueryVar('item', $newitem->getId());

            $field = $iv->getField('name');
            $subject = 'Comment posted to '.$field->toString();
            $from = 'Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';
            $message = 'New item posted. Visit '.$url->encode()." to edit it.\n\n";
            if ($_PREFS->getPref('mail.headernewline'))
              $from.="\r\n";

            foreach ($query as $name => $value)
              $message .= $name.': '.$value."\n\n";
            
            mail($email, $subject, $message, 'From: '.$from);
          }
        }
		  	
		  	redirect($request->getNested());
		  }
      else
      {
        $log->warn('Unable to create version');
        displayServerError($request);
      }
    }
    else
    {
      $log->warn('Unable to create variant');
      displayServerError($request);
    }
  }
  else
  {
    $log->warn('Unable to create item');
    displayServerError($request);
  }
}

?>
