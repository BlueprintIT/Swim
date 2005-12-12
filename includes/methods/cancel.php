<?

/*
 * Swim
 *
 * Cancels block editing
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_cancel($request)
{
	global $_USER;
	
	$resource = Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			$details=$resource->getWorkingDetails();
			if ($details->isMine())
			{
				$details->free();
			}
      if (($resource instanceof Page)&&(!isset($this->parent)))
      {
        $vers = array_keys($resource->getVersions());
        if ((count($vers==1))&&($vers[0]=='base'))
        {
          $resource->delete();
        }
      }
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