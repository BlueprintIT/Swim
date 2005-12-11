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
	
	list($container,$type)=explode('/',$request->resource);
	
	$container=getContainer($container);
	if ($container!==false)
	{
		if ($container->isWritable())
		{
			// TODO possibly a better security check here
			if ($type=='page')
			{
				$container=getContainer('internal');
				$page=$container->getPage('pageedit');
				$page->display($request);
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