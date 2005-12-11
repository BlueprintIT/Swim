<?

/*
 * Swim
 *
 * Displays the admin site
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_admin($request)
{
	global $_USER;
	
	$resource=Resource::decodeResource($request);

	if ($_USER->isLoggedIn())
  {
    if ($resource!==false)
    {
      if ($resource->isPage())
      {
        $page = Resource::decodeResource('internal/page/pagedetails');
        $page->display($request);
    	}
    	else
    	{
        displayGeneralError($request,'You can only administrate pages.');
    	}
    }
    else
    {
    	displayNotFound($request);
    }
  }
  else
  {
    displayAdminLogin($request);
  }
}


?>