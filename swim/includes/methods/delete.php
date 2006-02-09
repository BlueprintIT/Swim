<?

/*
 * Swim
 *
 * Cancels block editing
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_delete($request)
{
  global $_USER;
  
  checkSecurity($request, true, true);
  
  $resource = Resource::decodeResource($request);

  if ($resource!==false)
  {
    if ($_USER->canWrite($resource))
    {
      $resource->delete();
      redirect($request->nested);
    }
    else
    {
      displayAdminLogin($request);
    }
  }
  else
  {
    displayNotFound($request);
  }
}

?>