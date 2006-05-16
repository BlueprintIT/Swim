<?

/*
 * Swim
 *
 * Resource creation method
 *
 * Copyright Blueprint IT Ltd. 2006
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
  
	list($container,$type)=explode('/',$request->resourcePath);
	
	$container=getContainer($container);
	if ($container!==null)
	{
		if (($container->isWritable())&&($_USER->hasPermission('documents',PERMISSION_WRITE)))
		{
			if ($type=='page')
			{
        list($id,$pdir)=$container->createNewResource('page');
        $new=$container->getPage($id);
        $new->savePreferences();
        
        if (isset($request->query['category']))
        {
        	$category = $container->getCategory($request->query['category']);
        	$category->add($new);
        }
        else
        {
        	$category = $container->getRootCategory();
        }
        
        $source = new Request();
        $source->method='view';
        $source->resource='internal/page/pagedetails';
        $source->query['category']=$category->id;
        $source->query['page']=$new->getPath();
        $chained = new Request();
        $chained->method='view';
        $chained->resource='internal/page/pageedit';
        $chained->query['page']=$new->getPath();
        $chained->query['reloadtree']='true';
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