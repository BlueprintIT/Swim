<?

/*
 * Swim
 *
 * Posts form details to an email address
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_postback($request)
{
	global $_PREFS;
	
  $log = LoggerManager::getLogger('swim.postback');
  checkSecurity($request, true, true);
  
  RequestCache::setNoCache();
  
  if (($request->hasQueryVar('itemversion')) || ($request->hasQueryVar('item')))
  {
    if ($request->hasQueryVar('itemversion'))
    {
      $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
      $item = $itemversion->getItem();
      $request->clearQueryVar('itemversion');
    }
    else
    {
      $item = Item::getItem($request->getQuery('item'));
      $request->clearQueryVar('item');
    }
    $itemversion = $item->getCurrentVersion(Session::getCurrentVariant());

    if (($itemversion !== null) && ($itemversion->hasField('email')))
    {
      $subject = 'Email from '.$_SERVER['HTTP_HOST'];
      if ($itemversion->hasField('subject'))
      {
        $field = $itemversion->getField('subject');
        $subject = $field->toString();
      }
      $message = '';

			if ($request->hasQueryVar('mail_subject'))
			{
				$subject = $request->getQueryVar('mail_subject');
				$request->clearQueryVar('mail_subject');
			}
			
      $responseto = null;
      if ($request->hasQueryVar('from_email'))
      {
      	if ($request->hasQueryVar('from_name'))
      		$from = $request->getQueryVar('from_name').' <'.$request->getQueryVar('from_email').'>';
      	else
      		$from = $request->getQueryVar('from_email');
      	$request->clearQueryVar('from_email');
      	$request->clearQueryVar('from_name');
      	$from = str_replace("\n", " ", $from);
      	$from = str_replace("\r", " ", $from);
        $responseto = $from;
      }
      else
	      $from = 'Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';

			if ($_PREFS->getPref('mail.headernewline'))
				$from.="\r\n";
				
      if ($request->hasQueryVar('post_success'))
      {
        $redirect = Request::decodeEncodedPath($request->getQueryVar('post_success'));
        $request->clearQueryVar('post_success');
      }
      else
      {
        $redirect = new Request();
        $redirect->setMethod('view');
        $redirect->setPath($itemversion->getItem()->getId());
        $redirect->setQueryVar('posted', 'true');
      }
      
      foreach ($request->getQuery() as $name => $value)
        $message .= $name.': '.$value."\n\n";

      $field = $itemversion->getField('email');
      $email = $field->toString();
      mail($email, $subject, $message, 'From: '.$from);
      
      if (($itemversion->hasField('response')) && ($responseto !== null))
      {
        $body = $itemversion->getField('response')->toString();
        if ($itemversion->hasField('responseFile'))
        {
          $url = 'http://'.$_SERVER['HTTP_HOST'].$itemversion->getField('responseFile')->toString();
          $body = str_replace('{$responseFile}', $url, $body);
        }
        $subject = 'Re: '.$subject;
        $from = 'Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';
        if ($_PREFS->getPref('mail.headernewline'))
          $from.="\r\n";
          
        mail($responseto, $subject, $body, 'From: '.$from);
      }
      
      redirect($redirect);
    }
    else
      displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
  }
  else
    displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
}
