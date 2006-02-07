<?

/*
 * Swim
 *
 * Resource creation method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_create($request)
{
	global $_USER;
	
  checkSecurity($request, true, true);
  
	list($container,$type)=explode('/',$request->resource);
	
	$container=getContainer($container);
	if ($container!==false)
	{
		if (($container->isWritable())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
		{
			if ($type=='page')
			{
        list($id,$pdir)=$container->createNewResource('page');
        $new=$container->getPage($id);
        $new->savePreferences();
        $source = new Request();
        $source->method='admin';
        $source->resource=$new->getPath();
        $chained = new Request();
        $chained->method='edit';
        $chained->resource=$new->getPath();
        $chained->nested=$source;
        redirect($chained);
			}
			else
			{
				displayGeneralError($request,'You can only create pages.');
			}
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