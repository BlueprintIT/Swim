<?

/*
 * Swim
 *
 * Allows users to opt out of mailings.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/methods/postitem.php $
 * $LastChangedBy: dave $
 * $Date: 2007-04-27 14:11:51 +0100 (Fri, 27 Apr 2007) $
 * $Revision: 1462 $
 */

function method_optout($request)
{
  $log = Loggermanager::getLogger('swim.optout');
  
  checkSecurity($request, false, true);
  
  RequestCache::setNoCache();
  
  if (!$request->hasQueryVar('section'))
  {
    $log->warn('No section specified.');
    displayNotFound($request);
    return;
  }
    
  $section = FieldSetManager::getSection($request->getQueryVar('section'));
  if (($section === null) || ($section->getType() !== 'mailing'))
  {
    $log->warn('No valid section specified.');
    displayNotFound($request);
    return;
  }
  
  if (!$request->hasQueryVar('email'))
  {
    $log->warn('No email specified.');
    displayNotFound($request);
    return;
  }
  
  $email = $request->getQueryVar('email');
  $items = Item::findItems($section, null, null, 'emailaddress', $email);
  if (count($items) == 0)
  {
    $log->warn('Email '.$email.' not found.');
    displayNotFound($request);
    return;
  }
  
  if (count($items) > 1)
    $log->warn('More than one contact with the same email address.');
  
  foreach ($items as $itemversion)
  {
    $newversion = $itemversion->getVariant()->createNewVersion($itemversion);
    $newversion->setFieldValue('optedin', false);
    $newversion->setComplete(true);
    $newversion->setCurrent(true);
  }

  if ($request->hasQueryVar('redirect'))
    redirect($request->getQueryVar('redirect'));
  else
    redirect($request->getNested());
}

?>
