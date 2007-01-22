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
  
  if ($request->hasQueryVar('itemversion'))
  {
    $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
    if (($itemversion !== null) && ($itemversion->hasField('email')))
    {
      $request->clearQueryVar('itemversion');
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
			
      if ($request->hasQueryVar('from_email'))
      {
      	if ($request->hasQueryVar('from_name'))
      		$from = $request->getQueryVar('from_name');
      	else
      		$from = 'Anonymous';
      	$from.=' <'.$request->getQueryVar('from_email').'>';
      	$request->clearQueryVar('from_email');
      	$request->clearQueryVar('from_name');
      	$from = str_replace("\n", " ", $from);
      	$from = str_replace("\r", " ", $from);
      }
      else
	      $from = 'Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';

			if ($_PREFS->getPref('method.postback.headernewline'))
				$from.="\r\n";
				
      foreach ($request->getQuery() as $name => $value)
        $message .= $name.': '.$value."\n\n";

      $field = $itemversion->getField('email');
      $email = $field->toString();
      mail($email, $subject, $message, 'From: '.$from);
      
      $request = new Request();
      $request->setMethod('view');
      $request->setPath($itemversion->getItem()->getId());
      $request->setQueryVar('posted', 'true');
      redirect($request);
    }
    else
      displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
  }
  else
    displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
}
