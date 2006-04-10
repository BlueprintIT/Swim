<?

/*
 * Swim
 *
 * Displays the admin site
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_admin($request)
{
	global $_USER;
	
  checkSecurity($request, true, true);
  $resource=null;
  
  if ($request->resource!='')
  	$resource=Resource::decodeResource($request);

	if ($_USER->isLoggedIn())
  {
		foreach (AdminManager::$sections as $section)
		{
		  if ($section->isAvailable())
		  {
		  	redirect($section->getURL());
		  	return;
		  }
		}
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>