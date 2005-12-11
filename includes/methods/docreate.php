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

function method_docreate($request)
{
	global $_USER,$_PREFS;
	
	$log=LoggerManager::getLogger('swim.method.docreate');
	
	list($container,$type)=explode('/',$request->resource);
	
	$container=getContainer($container);
	if ($container!==false)
	{
		if ($container->isWritable())
		{
			// TODO possibly a better security check here Bug 3
			if ($type=='page')
			{
				if (isset($request->query['layout']))
				{
					$layout=getLayout($request->query['layout']);
				}
				else
				{
					$layout=false;
				}
	
				$newpage=$container->createPage($layout);
					
				if (isset($request->query['makedefault']))
				{
					if ($request->query['makedefault']=='true')
					{
						$_PREFS->setPref('method.view.defaultresource',$newpage->getPath());
						$_PREFS->setPref('method.admin.defaultresource',$newpage->getPath());
						saveSitePreferences();
					}
					unset($request->query['makedefault']);
				}
				
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$log->debug('Setting pref '.$name.' -> '.$value);
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();
				

				$nrequest = new Request();
				$nrequest->method=$request->nested->method;
				$nrequest->resource=$container->id.'/page/'.$newpage->id;
				redirect($nrequest);
			}
			else if ($type=='block')
			{
				$newblock=$container->createBlock($request->query['layout']);
					
				foreach ($request->query as $name => $value)
				{
					$log->debug('Checking pref '.$name);
					if (substr($name,0,6)=='block.')
					{
						$log->debug('Setting pref '.$name.' -> '.$value);
						$newblock->prefs->setPref($name,$value);
					}
				}
				$newblock->savePreferences();
				
				redirect($request->nested);
			}
			else
			{
				displayGeneralError($request,'Can only create pages or blocks.');
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