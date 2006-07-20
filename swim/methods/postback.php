<?

/*
 * Swim
 *
 * Posts form details to an email address
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_postback($request)
{
  $log = LoggerManager::getLogger('swim.postback');
  checkSecurity($request, true, true);
  
  if ($request->hasQueryVar('itemversion'))
  {
    $itemversion = Item::getItemVersion($request->getQueryVar('itemversion'));
    if (($itemversion != null) && ($itemversion->hasField('email')))
    {
      $request->clearQueryVar('itemversion');
      $subject = 'Email from '.$_SERVER['HTTP_HOST'];
      if ($itemversion->hasField('subject'))
      {
        $field = $itemversion->getField('subject');
        $subject = $field->toString();
      }
      $message = '';
      foreach ($request->getQuery() as $name => $value)
      {
        $message .= $name.': '.$value."\n\n";
      }
      $from = 'Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';
      $field = $itemversion->getField('email');
      $email = $field->toString();
      mail($email, $subject, $message, 'From: '.$from."\r\n");
    }
    else
      displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
  }
  else
    displayGeneralError($request, "No post form specified. This may indicate an attempt to hack this site, the developers have been notified.");
}
