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

function method_docreate(&$request)
{
	global $_USER,$_PREFS;
	
	list($container,$type)=explode('/',$request->resource);
	
	$container=&getContainer($container);
	if ($container!==false)
	{
		if ($container->isWritable())
		{
			// TODO possibly a better security check here Bug 3
			if ($type=='page')
			{
				if (isset($request->query['layout']))
				{
					$layout=&getLayout($request->query['layout']);
				}
				else
				{
					$layout=false;
				}
	
				$newpage=&$container->createPage($layout);
					
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();
				
				$nrequest = new Request();
				$nrequest->method=$request->nested->method;
				$nrequest->resource=$container->id.'/page/'.$newpage->id;
				redirect($nrequest);
			}
			else
			{
				displayGeneralError($request,'Can only create a page.');
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