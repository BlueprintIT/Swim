<?

/*
 * Swim
 *
 * Item viewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_view($request)
{
  $log = LoggerManager::getLogger('swim.method.view');
  checkSecurity($request, false, false);
  
  $item = Item::getItem($request->getPath());
  if ($item != null)
    $item = $item->getCurrentVersion(Session::getDefaultVariant());
  if ($item != null)
  {
    $smarty = createSmarty($request, 'text/html');
    if ($request->hasQueryVar('template'))
      $template = $request->getQueryVar('template');
    else
      $template = $item->getClass()->getTemplate();
    $smarty->assign_by_ref('item', new ItemWrapper($item));
    $smarty->display($template, $item->getId());
  }
  else
    displayNotFound($request);
}

?>