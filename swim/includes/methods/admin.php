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
  
  if ($request->resource!='')
  	$resource=Resource::decodeResource($request);

	if (($_USER->isLoggedIn())&&($_USER->hasPermission('documents',PERMISSION_READ)))
  {
    if (($request->resource=='')||($resource!==null))
    {
      if (($resource===null)||($resource->isPage())||($resource->isContainer()))
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