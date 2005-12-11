<?

/*
 * Swim
 *
 * Displays a file selector dialog
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_pageselect(&$request)
{
  $page = &Resource::decodeResource('internal/page/pageselect');
  $page->display($request);
}

?>